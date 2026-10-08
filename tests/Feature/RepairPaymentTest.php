<?php

declare(strict_types=1);

namespace Tests\Feature;

use Override;
use PDO;
use PHPUnit\Framework\Attributes\Test;

final class RepairPaymentTest extends FeatureTestCase
{
  private string $email;
  private PDO $database;

  #[Override]
  protected function setUp(): void
  {
    parent::setUp();

    $path = parse_ini_file(dirname(__DIR__, 2) . '/.env')['DB_DATABASE'];
    $this->database = new PDO('sqlite:' . dirname(__DIR__, 2) . '/' . $path);
    $this->email = 'repair-payment-' . bin2hex(random_bytes(8)) . '@example.test';
    $this->post('/registrarse', [
      'email' => $this->email,
      'password' => 'feature-password',
    ]);

    $this->post('/negocios', [
      'name' => 'Taller ' . bin2hex(random_bytes(4)),
      'rif' => 'J-' . bin2hex(random_bytes(5)),
      'address' => 'Avenida Principal, local 1',
      'phone' => '0412-555-0100',
    ]);
    $businessId = (int) $this->database
      ->query("SELECT id FROM businesses WHERE user_id = (SELECT id FROM users WHERE email = '$this->email')")
      ->fetchColumn();
    $this->get("/negocios/$businessId/seleccionar");
    $this->post('/calculadora/cotizacion', ['rate' => '100']);
    $this->post('/clientes', [
      'name' => 'Cliente ' . bin2hex(random_bytes(4)),
      'cedula' => 'V-' . random_int(10_000_000, 99_999_999),
      'phone' => '0412-555-0101',
      'address' => 'Dirección de prueba',
    ]);
  }

  #[Test]
  public function a_repair_can_be_registered_with_multiple_initial_payments(): void
  {
    $created = $this->post('/reparaciones', [
      'client_id' => $this->clientId(),
      'description' => 'Cambio de batería',
      'price' => '7',
      'amount' => ['5', '200'],
      'method' => ['Efectivo USD', 'Punto'],
    ]);

    $repairId = $this->repairId('Cambio de batería');

    self::assertSame(303, $created->getStatusCode());
    self::assertSame('/reparaciones', $created->getHeaderLine('Location'));
    self::assertSame(
      [
        ['amount' => 5, 'method' => 'Efectivo USD', 'exchange_rate' => null],
        ['amount' => 200, 'method' => 'Punto', 'exchange_rate' => 100],
      ],
      $this->database
        ->query(
          "SELECT amount, method, exchange_rate FROM repair_payments WHERE repair_id = $repairId ORDER BY id",
        )
        ->fetchAll(PDO::FETCH_ASSOC),
    );
  }

  #[Test]
  public function authenticated_users_can_list_their_repairs(): void
  {
    $this->createRepair('Reemplazo de pantalla');

    $response = $this->get('/reparaciones');

    self::assertSame(200, $response->getStatusCode());
    self::assertStringContainsString('Reemplazo de pantalla', (string) $response->getBody());
    self::assertStringContainsString('Pago pendiente', (string) $response->getBody());
  }

  #[Test]
  public function a_repair_receipt_can_be_viewed(): void
  {
    $this->createRepair('Limpieza de puerto', ['3'], ['Efectivo USD']);
    $repairId = $this->repairId('Limpieza de puerto');

    $response = $this->get("/reparaciones/$repairId");

    self::assertSame(200, $response->getStatusCode());
    self::assertStringContainsString('Limpieza de puerto', (string) $response->getBody());
    self::assertStringContainsString('Pago pendiente', (string) $response->getBody());
    self::assertStringContainsString('$4,00', (string) $response->getBody());
  }

  #[Test]
  public function an_outstanding_repair_can_receive_a_payment(): void
  {
    $this->createRepair('Diagnóstico de placa');
    $repairId = $this->repairId('Diagnóstico de placa');

    $response = $this->post("/reparaciones/$repairId/pagar", [
      'amount' => '200',
      'method' => 'Transferencia',
    ]);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/reparaciones', $response->getHeaderLine('Location'));
    self::assertSame(
      ['amount' => 200, 'method' => 'Transferencia', 'exchange_rate' => 100],
      $this->database
        ->query(
          "SELECT amount, method, exchange_rate FROM repair_payments WHERE repair_id = $repairId",
        )
        ->fetch(PDO::FETCH_ASSOC),
    );
  }

  /**
   * @param list<string> $amounts
   * @param list<string> $methods
   */
  private function createRepair(
    string $description,
    array $amounts = [''],
    array $methods = ['Efectivo USD'],
  ): void {
    $response = $this->post('/reparaciones', [
      'client_id' => $this->clientId(),
      'description' => $description,
      'price' => '7',
      'amount' => $amounts,
      'method' => $methods,
    ]);

    self::assertSame(303, $response->getStatusCode());
  }

  private function clientId(): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM clients WHERE user_id = :user_id',
    );
    $statement->execute(['user_id' => $this->userId()]);

    return (int) $statement->fetchColumn();
  }

  private function repairId(string $description): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM repairs WHERE business_id IN (SELECT id FROM businesses WHERE user_id = :user_id) AND description = :description',
    );
    $statement->execute([
      'user_id' => $this->userId(),
      'description' => $description,
    ]);

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

  #[Override]
  protected function tearDown(): void
  {
    $userId = $this->userId();

    if ($userId !== false) {
      $this->database->prepare('DELETE FROM repair_payments WHERE repair_id IN (SELECT id FROM repairs WHERE business_id IN (SELECT id FROM businesses WHERE user_id = :user_id))')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM repairs WHERE business_id IN (SELECT id FROM businesses WHERE user_id = :user_id)')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM clients WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM exchange_rates WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM businesses WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $userId]);
    }

    parent::tearDown();
  }
}
