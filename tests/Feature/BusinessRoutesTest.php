<?php

declare(strict_types=1);

namespace Tests\Feature;

use Override;
use PDO;
use PHPUnit\Framework\Attributes\Test;

final class BusinessRoutesTest extends FeatureTestCase
{
  private string $email;
  private PDO $database;

  #[Override]
  protected function setUp(): void
  {
    parent::setUp();

    $path = parse_ini_file(dirname(__DIR__, 2) . '/.env')['DB_DATABASE'];
    $this->database = new PDO('sqlite:' . dirname(__DIR__, 2) . '/' . $path);
    $this->email = 'business-' . bin2hex(random_bytes(8)) . '@example.test';
    $this->post('/registrarse', [
      'email' => $this->email,
      'password' => 'feature-password',
    ]);
  }

  #[Test]
  public function a_business_can_be_registered(): void
  {
    $business = $this->business('Orion Centro');

    $response = $this->post('/negocios', $business);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/negocios', $response->getHeaderLine('Location'));
    self::assertSame($business, $this->businessByName($business['name']));
  }

  #[Test]
  public function duplicate_business_names_are_rejected_for_the_same_user(): void
  {
    $business = $this->business('Orion Duplicado');
    $this->post('/negocios', $business);
    $duplicate = $this->business('Orion Duplicado');

    $response = $this->post('/negocios', $duplicate);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/negocios', $response->getHeaderLine('Location'));
    self::assertSame(1, $this->businessCount($business['name']));
  }

  #[Test]
  public function duplicate_rifs_are_rejected(): void
  {
    $business = $this->business('Orion RIF Base');
    $this->post('/negocios', $business);
    $duplicate = $this->business('Orion RIF Duplicado');
    $duplicate['rif'] = $business['rif'];

    $response = $this->post('/negocios', $duplicate);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/negocios', $response->getHeaderLine('Location'));
    self::assertSame(1, $this->businessCountByRif($business['rif']));
  }

  #[Test]
  public function duplicate_addresses_are_rejected_for_the_same_user(): void
  {
    $business = $this->business('Orion Dirección Base');
    $this->post('/negocios', $business);
    $duplicate = $this->business('Orion Dirección Duplicada');
    $duplicate['address'] = $business['address'];

    $response = $this->post('/negocios', $duplicate);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/negocios', $response->getHeaderLine('Location'));
    self::assertSame(1, $this->businessCountByAddress($business['address']));
  }

  #[Test]
  public function authenticated_users_can_list_their_businesses(): void
  {
    $business = $this->business('Orion Norte');
    $this->post('/negocios', $business);

    $response = $this->get('/negocios');

    self::assertSame(200, $response->getStatusCode());
    self::assertStringContainsString($business['name'], (string) $response->getBody());
    self::assertStringContainsString($business['rif'], (string) $response->getBody());
  }

  #[Test]
  public function a_business_can_be_updated(): void
  {
    $original = $this->business('Orion Este');
    $this->post('/negocios', $original);
    $id = $this->businessId($original['name']);
    $updated = [
      'name' => $original['name'],
      'rif' => 'J-' . bin2hex(random_bytes(5)),
      'address' => $original['address'],
      'phone' => $original['phone'],
    ];

    $response = $this->post("/negocios/$id", $updated);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/negocios', $response->getHeaderLine('Location'));
    self::assertSame($updated, $this->businessByName($updated['name']));
  }

  #[Test]
  public function a_business_can_be_deleted(): void
  {
    $business = $this->business('Orion Sur');
    $this->post('/negocios', $business);
    $id = $this->businessId($business['name']);

    $response = $this->get("/negocios/$id/eliminar");

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/negocios', $response->getHeaderLine('Location'));
    self::assertSame(0, $this->businessCount($business['name']));
  }

  #[Test]
  public function a_business_with_associated_records_cannot_be_deleted(): void
  {
    $business = $this->business('Orion Inventario');
    $this->post('/negocios', $business);
    $businessId = $this->businessId($business['name']);
    $product = $this->database->prepare(
      'INSERT INTO products (user_id, name, category, price) VALUES (:user_id, :name, :category, :price)',
    );
    $product->execute([
      'user_id' => $this->userId(),
      'name' => 'Producto asociado ' . bin2hex(random_bytes(4)),
      'category' => 'accessory',
      'price' => 10,
    ]);
    $productId = (int) $this->database->lastInsertId();
    $batch = $this->database->prepare(
      'INSERT INTO batches (business_id, product_id, stock) VALUES (:business_id, :product_id, :stock)',
    );
    $batch->execute(['business_id' => $businessId, 'product_id' => $productId, 'stock' => 1]);

    $response = $this->get("/negocios/$businessId/eliminar");

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/negocios', $response->getHeaderLine('Location'));
    self::assertSame(1, $this->businessCount($business['name']));
    self::assertSame(
      1,
      (int) $this->database->query("SELECT COUNT(*) FROM batches WHERE business_id = $businessId")->fetchColumn(),
    );
  }

  #[Test]
  public function a_business_can_be_selected_as_active(): void
  {
    $business = $this->business('Orion Oeste');
    $this->post('/negocios', $business);
    $id = $this->businessId($business['name']);

    $response = $this->get("/negocios/$id/seleccionar");
    $nextPage = $this->get('/');

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/negocios', $response->getHeaderLine('Location'));
    self::assertSame(303, $nextPage->getStatusCode());
    self::assertSame('/calculadora', $nextPage->getHeaderLine('Location'));
  }

  #[Override]
  protected function tearDown(): void
  {
    $userId = $this->database
      ->prepare('SELECT id FROM users WHERE email = :email');
    $userId->execute(['email' => $this->email]);
    $id = $userId->fetchColumn();

    if ($id !== false) {
      $this->database->prepare(
        'DELETE FROM batches WHERE product_id IN (SELECT id FROM products WHERE user_id = :user_id)',
      )->execute(['user_id' => $id]);
      $this->database->prepare(
        'DELETE FROM products WHERE user_id = :user_id',
      )->execute(['user_id' => $id]);

      $deleteBusinesses = $this->database->prepare(
        'DELETE FROM businesses WHERE user_id = :user_id',
      );
      $deleteBusinesses->execute(['user_id' => $id]);

      $deleteUser = $this->database->prepare(
        'DELETE FROM users WHERE id = :id',
      );
      $deleteUser->execute(['id' => $id]);
    }

    parent::tearDown();
  }

  /** @return array{name: string, rif: string, address: string, phone: string} */
  private function business(string $name): array
  {
    return [
      'name' => $name,
      'rif' => 'J-' . bin2hex(random_bytes(5)),
      'address' => 'Avenida Principal, local 1',
      'phone' => '0412-555-0100',
    ];
  }

  /** @return array{name: string, rif: string, address: string, phone: string} */
  private function businessByName(string $name): array
  {
    $statement = $this->database->prepare(
      'SELECT name, rif, address, phone FROM businesses WHERE name = :name AND user_id = :user_id',
    );
    $statement->execute(['name' => $name, 'user_id' => $this->userId()]);

    return $statement->fetch(PDO::FETCH_ASSOC);
  }

  private function businessId(string $name): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM businesses WHERE name = :name AND user_id = :user_id',
    );
    $statement->execute(['name' => $name, 'user_id' => $this->userId()]);

    return (int) $statement->fetchColumn();
  }

  private function businessCount(string $name): int
  {
    $statement = $this->database->prepare(
      'SELECT COUNT(*) FROM businesses WHERE name = :name AND user_id = :user_id',
    );
    $statement->execute(['name' => $name, 'user_id' => $this->userId()]);

    return (int) $statement->fetchColumn();
  }

  private function businessCountByRif(string $rif): int
  {
    $statement = $this->database->prepare(
      'SELECT COUNT(*) FROM businesses WHERE rif = :rif',
    );
    $statement->execute(['rif' => $rif]);

    return (int) $statement->fetchColumn();
  }

  private function businessCountByAddress(string $address): int
  {
    $statement = $this->database->prepare(
      'SELECT COUNT(*) FROM businesses WHERE address = :address AND user_id = :user_id',
    );
    $statement->execute(['address' => $address, 'user_id' => $this->userId()]);

    return (int) $statement->fetchColumn();
  }

  private function userId(): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM users WHERE email = :email',
    );
    $statement->execute(['email' => $this->email]);

    return (int) $statement->fetchColumn();
  }
}
