<?php

declare(strict_types=1);

namespace Tests\Feature;

use Override;
use PDO;
use PHPUnit\Framework\Attributes\Test;

final class ProductRoutesTest extends FeatureTestCase
{
  private string $email;
  private int $businessId;
  private PDO $database;

  #[Override]
  protected function setUp(): void
  {
    parent::setUp();

    $path = parse_ini_file(dirname(__DIR__, 2) . '/.env')['DB_DATABASE'];
    $this->database = new PDO('sqlite:' . dirname(__DIR__, 2) . '/' . $path);
    $this->email = 'product-' . bin2hex(random_bytes(8)) . '@example.test';
    $this->post('/registrarse', [
      'email' => $this->email,
      'password' => 'feature-password',
    ]);

    $business = [
      'name' => 'Inventario ' . bin2hex(random_bytes(4)),
      'rif' => 'J-' . bin2hex(random_bytes(5)),
      'address' => 'Avenida Principal, local 1',
      'phone' => '0412-555-0100',
    ];
    $this->post('/negocios', $business);
    $this->businessId = $this->businessId($business['name']);
    $this->get("/negocios/$this->businessId/seleccionar");
    $this->post('/calculadora/cotizacion', ['rate' => '100']);
  }

  #[Test]
  public function a_product_can_be_registered(): void
  {
    $product = $this->product('Teléfono Aurora', 'phone', 500, 3);

    $response = $this->post('/productos', $product);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/productos', $response->getHeaderLine('Location'));
    self::assertSame(
      ['name' => $product['name'], 'category' => 'phone', 'price' => 500],
      $this->productByName($product['name']),
    );
    self::assertSame(3, $this->stockFor($product['name']));
  }

  #[Test]
  public function a_spare_part_can_be_registered_with_a_decimal_price(): void
  {
    $product = $this->product('Pantalla OLED de repuesto', 'spare_part', 49.95, 2);

    $response = $this->post('/productos', $product);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame(
      ['name' => $product['name'], 'category' => 'spare_part', 'price' => 49.95],
      $this->productByName($product['name']),
    );
    self::assertSame(2, $this->stockFor($product['name']));
  }

  #[Test]
  public function duplicate_product_names_are_rejected_for_the_same_user(): void
  {
    $product = $this->product('Cable USB-C', 'accessory', 8, 3);
    $this->post('/productos', $product);
    $duplicate = $this->product('Cable USB-C', 'spare_part', 4.5, 5);

    $response = $this->post('/productos', $duplicate);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame(1, $this->productCount($product['name']));
  }

  #[Test]
  public function invalid_product_categories_are_rejected(): void
  {
    $product = $this->product('Categoría inválida', 'invalid', 10, 1);

    $response = $this->post('/productos', $product);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame(0, $this->productCount($product['name']));
  }

  #[Test]
  public function authenticated_users_can_list_their_products(): void
  {
    $product = $this->product('Cargador Solar', 'accessory', 25, 8);
    $this->post('/productos', $product);

    $response = $this->get('/productos');

    self::assertSame(200, $response->getStatusCode());
    self::assertStringContainsString($product['name'], (string) $response->getBody());
    self::assertStringContainsString('Accesorio', (string) $response->getBody());
  }

  #[Test]
  public function a_product_can_be_updated(): void
  {
    $original = $this->product('Audífonos Base', 'accessory', 30, 4);
    $this->post('/productos', $original);
    $id = $this->productId($original['name']);
    $updated = $this->product('Audífonos Pro', 'accessory', 45, 12);

    $response = $this->post("/productos/$id", $updated);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/productos', $response->getHeaderLine('Location'));
    self::assertSame(
      ['name' => $updated['name'], 'category' => 'accessory', 'price' => 45],
      $this->productByName($updated['name']),
    );
    self::assertSame(12, $this->stockFor($updated['name']));
  }

  #[Test]
  public function a_product_can_be_deleted(): void
  {
    $product = $this->product('Protector de pantalla', 'accessory', 10, 0);
    $this->post('/productos', $product);
    $id = $this->productId($product['name']);

    $response = $this->get("/productos/$id/eliminar");

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/productos', $response->getHeaderLine('Location'));
    self::assertSame(0, $this->productCount($product['name']));
  }

  #[Test]
  public function a_product_with_associated_records_cannot_be_deleted(): void
  {
    $product = $this->product('Producto con inventario', 'accessory', 10, 1);
    $this->post('/productos', $product);
    $id = $this->productId($product['name']);

    $response = $this->get("/productos/$id/eliminar");

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/productos', $response->getHeaderLine('Location'));
    self::assertSame(1, $this->productCount($product['name']));
    self::assertSame(1, $this->stockFor($product['name']));
  }

  #[Override]
  protected function tearDown(): void
  {
    $statement = $this->database->prepare(
      'SELECT id FROM users WHERE email = :email',
    );
    $statement->execute(['email' => $this->email]);
    $userId = $statement->fetchColumn();

    if ($userId !== false) {
      $this->database->prepare(
        'DELETE FROM batches WHERE product_id IN (SELECT id FROM products WHERE user_id = :user_id)',
      )->execute(['user_id' => $userId]);
      $this->database->prepare(
        'DELETE FROM products WHERE user_id = :user_id',
      )->execute(['user_id' => $userId]);
      $this->database->prepare(
        'DELETE FROM exchange_rates WHERE user_id = :user_id',
      )->execute(['user_id' => $userId]);
      $this->database->prepare(
        'DELETE FROM businesses WHERE user_id = :user_id',
      )->execute(['user_id' => $userId]);
      $this->database->prepare(
        'DELETE FROM users WHERE id = :id',
      )->execute(['id' => $userId]);
    }

    parent::tearDown();
  }

  /** @return array{name: string, category: string, price: float|int, stocks: array<int, int>} */
  private function product(
    string $name,
    string $category,
    float|int $price,
    int $stock,
  ): array {
    return [
      'name' => $name,
      'category' => $category,
      'price' => $price,
      'stocks' => [$this->businessId => $stock],
    ];
  }

  /** @return array{name: string, category: string, price: float|int} */
  private function productByName(string $name): array
  {
    $statement = $this->database->prepare(
      'SELECT name, category, price FROM products WHERE name = :name AND user_id = :user_id',
    );
    $statement->execute(['name' => $name, 'user_id' => $this->userId()]);

    return $statement->fetch(PDO::FETCH_ASSOC);
  }

  private function businessId(string $name): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM businesses WHERE name = :name',
    );
    $statement->execute(['name' => $name]);

    return (int) $statement->fetchColumn();
  }

  private function productId(string $name): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM products WHERE name = :name AND user_id = :user_id',
    );
    $statement->execute(['name' => $name, 'user_id' => $this->userId()]);

    return (int) $statement->fetchColumn();
  }

  private function stockFor(string $name): int
  {
    $statement = $this->database->prepare(
      'SELECT batches.stock FROM batches JOIN products ON products.id = batches.product_id WHERE products.name = :name',
    );
    $statement->execute(['name' => $name]);

    return (int) $statement->fetchColumn();
  }

  private function productCount(string $name): int
  {
    $statement = $this->database->prepare(
      'SELECT COUNT(*) FROM products WHERE name = :name AND user_id = :user_id',
    );
    $statement->execute(['name' => $name, 'user_id' => $this->userId()]);

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
