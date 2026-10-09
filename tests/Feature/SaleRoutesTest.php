<?php

declare(strict_types=1);

namespace Tests\Feature;

use Override;
use PDO;
use PHPUnit\Framework\Attributes\Test;

final class SaleRoutesTest extends FeatureTestCase
{
  private PDO $database;
  private string $email;
  private int $businessId;
  private int $clientId;

  #[Override]
  protected function setUp(): void
  {
    parent::setUp();

    $path = parse_ini_file(dirname(__DIR__, 2) . '/.env')['DB_DATABASE'];
    $this->database = new PDO('sqlite:' . dirname(__DIR__, 2) . '/' . $path);
    $this->email = 'sale-' . bin2hex(random_bytes(8)) . '@example.test';
    $this->post('/registrarse', [
      'email' => $this->email,
      'password' => 'feature-password',
    ]);

    $business = [
      'name' => 'Ventas ' . bin2hex(random_bytes(4)),
      'rif' => 'J-' . bin2hex(random_bytes(5)),
      'address' => 'Avenida Principal, local 1',
      'phone' => '0412-555-0100',
    ];
    $this->post('/negocios', $business);
    $this->businessId = $this->businessId($business['name']);
    $this->get("/negocios/$this->businessId/seleccionar");
    $this->post('/calculadora/cotizacion', ['rate' => '100']);
    $this->post('/clientes', [
      'name' => 'Cliente ' . bin2hex(random_bytes(4)),
      'id_card' => 'V-' . random_int(10_000_000, 99_999_999),
      'phone' => '0412-555-0101',
      'address' => 'Dirección de prueba',
    ]);
    $this->clientId = (int) $this->database->query(
      "SELECT id FROM clients WHERE user_id = {$this->userId()}",
    )->fetchColumn();
  }

  #[Test]
  public function non_phone_sales_accept_an_optional_code(): void
  {
    $accessoryId = $this->createProduct('Cargador rápido', 'accessory', 25, 1);
    $sparePartId = $this->createProduct('Pantalla de repuesto', 'spare_part', 49.95, 1);

    $response = $this->post('/ventas', [
      'client_id' => $this->clientId,
      'product_id' => [$accessoryId, $sparePartId],
      'business_id' => [$this->businessId, $this->businessId],
      'quantity' => [1, 1],
      'code' => ['ACC-001', ''],
    ]);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/ventas', $response->getHeaderLine('Location'));
    self::assertSame(
      [
        ['product_id' => $accessoryId, 'code' => 'ACC-001'],
        ['product_id' => $sparePartId, 'code' => null],
      ],
      $this->saleItems(),
    );
  }

  #[Override]
  protected function tearDown(): void
  {
    $userId = $this->userId();

    if ($userId) {
      $this->database->exec(
        "DELETE FROM payments WHERE sale_id IN (SELECT id FROM sales WHERE business_id IN (SELECT id FROM businesses WHERE user_id = $userId))",
      );
      $this->database->exec(
        "DELETE FROM items WHERE sale_id IN (SELECT id FROM sales WHERE business_id IN (SELECT id FROM businesses WHERE user_id = $userId))",
      );
      $this->database->exec(
        "DELETE FROM sales WHERE business_id IN (SELECT id FROM businesses WHERE user_id = $userId)",
      );
      $this->database->exec("DELETE FROM clients WHERE user_id = $userId");
      $this->database->exec(
        "DELETE FROM batches WHERE product_id IN (SELECT id FROM products WHERE user_id = $userId)",
      );
      $this->database->exec("DELETE FROM products WHERE user_id = $userId");
      $this->database->exec("DELETE FROM exchange_rates WHERE user_id = $userId");
      $this->database->exec("DELETE FROM businesses WHERE user_id = $userId");
      $this->database->exec("DELETE FROM users WHERE id = $userId");
    }

    parent::tearDown();
  }

  private function createProduct(string $name, string $category, float $price, int $stock): int
  {
    $this->post('/productos', [
      'name' => $name,
      'category' => $category,
      'price' => $price,
      'stocks' => [$this->businessId => $stock],
    ]);

    return (int) $this->database->query(
      "SELECT id FROM products WHERE name = '$name' AND user_id = {$this->userId()}",
    )->fetchColumn();
  }

  /** @return list<array{product_id: int, code: null|string}> */
  private function saleItems(): array
  {
    $statement = $this->database->prepare(
      'SELECT product_id, code FROM items WHERE sale_id = (SELECT MAX(id) FROM sales WHERE business_id = :business_id) ORDER BY product_id',
    );
    $statement->execute(['business_id' => $this->businessId]);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  private function businessId(string $name): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM businesses WHERE name = :name',
    );
    $statement->execute(['name' => $name]);

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
