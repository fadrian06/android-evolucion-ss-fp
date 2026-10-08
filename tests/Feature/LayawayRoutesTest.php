<?php

declare(strict_types=1);

namespace Tests\Feature;

use Override;
use PDO;
use PHPUnit\Framework\Attributes\Test;

final class LayawayRoutesTest extends FeatureTestCase
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
    $this->email = 'layaway-' . bin2hex(random_bytes(8)) . '@example.test';
    $this->post('/registrarse', [
      'email' => $this->email,
      'password' => 'feature-password',
    ]);

    $business = [
      'name' => 'Reservas ' . bin2hex(random_bytes(4)),
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
      'cedula' => 'V-' . random_int(10_000_000, 99_999_999),
      'phone' => '0412-555-0101',
      'address' => 'Dirección de prueba',
    ]);
  }

  #[Test]
  public function an_accessory_can_be_reserved_without_a_code(): void
  {
    $productId = $this->createProduct('Cargador rápido', 'accessory', 25, 2);

    $response = $this->post('/apartados', [
      'client_id' => $this->clientId(),
      'product_id' => $productId,
      'amount' => [''],
      'method' => ['Efectivo USD'],
    ]);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/apartados', $response->getHeaderLine('Location'));
    self::assertSame(
      ['imei1' => null, 'imei2' => null, 'code' => null],
      $this->layaway($productId),
    );
    self::assertSame(1, $this->stockFor($productId));
  }

  #[Test]
  public function a_phone_can_be_reserved_with_its_imeis(): void
  {
    $productId = $this->createProduct('Orion X1', 'phone', 500, 1);

    $response = $this->post('/apartados', [
      'client_id' => $this->clientId(),
      'product_id' => $productId,
      'imei1' => '356789012345678',
      'imei2' => '356789012345679',
      'amount' => [''],
      'method' => ['Efectivo USD'],
    ]);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame(
      [
        'imei1' => '356789012345678',
        'imei2' => '356789012345679',
        'code' => null,
      ],
      $this->layaway($productId),
    );
    self::assertSame(0, $this->stockFor($productId));
  }

  #[Test]
  public function cancelling_a_layaway_restores_its_product_stock(): void
  {
    $productId = $this->createProduct('Protector de pantalla', 'accessory', 10, 1);
    $this->createLayaway($productId);
    $layawayId = $this->layawayId($productId);

    $response = $this->post("/apartados/$layawayId/cancelar", []);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/apartados', $response->getHeaderLine('Location'));
    self::assertSame(1, $this->stockFor($productId));
    self::assertNotNull($this->database->query("SELECT cancelled_at FROM layaways WHERE id = $layawayId")->fetchColumn());
  }

  #[Test]
  public function an_outstanding_layaway_can_be_paid_in_full(): void
  {
    $productId = $this->createProduct('Audífonos inalámbricos', 'accessory', 30, 1);
    $this->createLayaway($productId, ['10'], ['Efectivo USD']);
    $layawayId = $this->layawayId($productId);

    $response = $this->post("/apartados/$layawayId/pagar", [
      'amount' => '20',
      'method' => 'Efectivo USD',
    ]);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/apartados', $response->getHeaderLine('Location'));
    self::assertSame(2, (int) $this->database->query("SELECT COUNT(*) FROM layaway_payments WHERE layaway_id = $layawayId")->fetchColumn());
    self::assertStringContainsString('Pagado', (string) $this->get('/apartados')->getBody());

    $cancelled = $this->post("/apartados/$layawayId/cancelar", []);

    self::assertSame(303, $cancelled->getStatusCode());
    self::assertSame(0, $this->stockFor($productId));
    self::assertFalse((bool) $this->database->query("SELECT cancelled_at FROM layaways WHERE id = $layawayId")->fetchColumn());
  }

  private function createLayaway(
    int $productId,
    array $amounts = [''],
    array $methods = ['Efectivo USD'],
  ): void {
    $response = $this->post('/apartados', [
      'client_id' => $this->clientId(),
      'product_id' => $productId,
      'code' => 'TEST-CODE',
      'amount' => $amounts,
      'method' => $methods,
    ]);

    self::assertSame(303, $response->getStatusCode());
  }

  private function createProduct(string $name, string $category, int $price, int $stock): int
  {
    $response = $this->post('/productos', [
      'name' => $name,
      'category' => $category,
      'price' => $price,
      'stocks' => [$this->businessId => $stock],
    ]);
    self::assertSame(303, $response->getStatusCode());

    $statement = $this->database->prepare(
      'SELECT id FROM products WHERE user_id = :user_id AND name = :name',
    );
    $statement->execute(['user_id' => $this->userId(), 'name' => $name]);

    return (int) $statement->fetchColumn();
  }

  private function businessId(string $name): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM businesses WHERE user_id = :user_id AND name = :name',
    );
    $statement->execute(['user_id' => $this->userId(), 'name' => $name]);

    return (int) $statement->fetchColumn();
  }

  private function clientId(): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM clients WHERE user_id = :user_id',
    );
    $statement->execute(['user_id' => $this->userId()]);

    return (int) $statement->fetchColumn();
  }

  /** @return array{imei1: null|string, imei2: null|string, code: null|string} */
  private function layaway(int $productId): array
  {
    $statement = $this->database->prepare(
      'SELECT imei1, imei2, code FROM layaways WHERE product_id = :product_id',
    );
    $statement->execute(['product_id' => $productId]);

    return $statement->fetch(PDO::FETCH_ASSOC);
  }

  private function layawayId(int $productId): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM layaways WHERE product_id = :product_id',
    );
    $statement->execute(['product_id' => $productId]);

    return (int) $statement->fetchColumn();
  }

  private function stockFor(int $productId): int
  {
    $statement = $this->database->prepare(
      'SELECT stock FROM batches WHERE product_id = :product_id AND business_id = :business_id',
    );
    $statement->execute(['product_id' => $productId, 'business_id' => $this->businessId]);

    return (int) $statement->fetchColumn();
  }

  private function userId(): int
  {
    $statement = $this->database->prepare('SELECT id FROM users WHERE email = :email');
    $statement->execute(['email' => $this->email]);

    return (int) $statement->fetchColumn();
  }

  #[Override]
  protected function tearDown(): void
  {
    $userId = $this->userId();

    if ($userId !== 0) {
      $this->database->prepare(
        'DELETE FROM layaway_payments WHERE layaway_id IN (SELECT id FROM layaways WHERE business_id IN (SELECT id FROM businesses WHERE user_id = :user_id))',
      )->execute(['user_id' => $userId]);
      $this->database->prepare(
        'DELETE FROM layaways WHERE business_id IN (SELECT id FROM businesses WHERE user_id = :user_id)',
      )->execute(['user_id' => $userId]);
      $this->database->prepare(
        'DELETE FROM batches WHERE product_id IN (SELECT id FROM products WHERE user_id = :user_id)',
      )->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM products WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM clients WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM exchange_rates WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM businesses WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $userId]);
    }

    parent::tearDown();
  }
}
