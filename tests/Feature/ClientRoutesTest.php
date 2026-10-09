<?php

declare(strict_types=1);

namespace Tests\Feature;

use Override;
use PDO;
use PHPUnit\Framework\Attributes\Test;

final class ClientRoutesTest extends FeatureTestCase
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
    $this->email = 'client-' . bin2hex(random_bytes(8)) . '@example.test';
    $this->post('/registrarse', [
      'email' => $this->email,
      'password' => 'feature-password',
    ]);

    $business = [
      'name' => 'Clientes ' . bin2hex(random_bytes(4)),
      'rif' => 'J-' . bin2hex(random_bytes(5)),
      'address' => 'Avenida Principal, local 1',
      'phone' => '0412-555-0100',
    ];
    $this->post('/negocios', $business);
    $this->businessId = $this->businessId($business['name']);
    $this->get('/negocios/' . $this->businessId . '/seleccionar');
    $this->post('/calculadora/cotizacion', ['rate' => '100']);
  }

  #[Test]
  public function a_client_can_be_registered(): void
  {
    $client = $this->client('María Pérez');

    $response = $this->post('/clientes', $client);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/clientes', $response->getHeaderLine('Location'));
    self::assertSame($client, $this->clientByName($client['name']));
  }

  #[Test]
  public function authenticated_users_can_list_their_clients(): void
  {
    $client = $this->client('José Rojas');
    $this->post('/clientes', $client);

    $response = $this->get('/clientes');

    self::assertSame(200, $response->getStatusCode());
    self::assertStringContainsString($client['name'], (string) $response->getBody());
    self::assertStringContainsString($client['id_card'], (string) $response->getBody());
  }

  #[Test]
  public function a_client_can_be_updated(): void
  {
    $original = $this->client('Carla Díaz');
    $this->post('/clientes', $original);
    $id = $this->clientId($original['name']);
    $updated = [
      'name' => $original['name'],
      'id_card' => $original['id_card'],
      'phone' => '0424-555-0101',
      'address' => $original['address'],
    ];

    $response = $this->post("/clientes/$id", $updated);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/clientes', $response->getHeaderLine('Location'));
    self::assertSame($updated, $this->clientByName($updated['name']));
  }

  #[Test]
  public function a_client_can_be_deleted(): void
  {
    $client = $this->client('Luis Mendoza');
    $this->post('/clientes', $client);
    $id = $this->clientId($client['name']);

    $response = $this->get("/clientes/$id/eliminar");

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/clientes', $response->getHeaderLine('Location'));
    self::assertSame(0, $this->clientCount($client['name']));
  }

  #[Test]
  public function duplicate_client_names_are_rejected_for_the_same_user(): void
  {
    $client = $this->client('Cliente Duplicado');
    $this->post('/clientes', $client);
    $duplicate = $this->client('Cliente Duplicado');

    $response = $this->post('/clientes', $duplicate);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame(1, $this->clientCount($client['name']));
  }

  #[Test]
  public function duplicate_id_cards_are_rejected_for_the_same_user(): void
  {
    $client = $this->client('Cliente Cédula Base');
    $this->post('/clientes', $client);
    $duplicate = $this->client('Cliente Cédula Duplicada');
    $duplicate['id_card'] = $client['id_card'];

    $response = $this->post('/clientes', $duplicate);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame(1, $this->clientCountByIdCard($client['id_card']));
  }

  #[Test]
  public function a_client_with_associated_records_cannot_be_deleted(): void
  {
    $client = $this->client('Cliente con reparación');
    $this->post('/clientes', $client);
    $clientId = $this->clientId($client['name']);
    $repair = $this->database->prepare(
      'INSERT INTO repairs (business_id, client_id, description, price, price_ves, due_date) VALUES (:business_id, :client_id, :description, :price, :price_ves, :due_date)',
    );
    $repair->execute([
      'business_id' => $this->businessId,
      'client_id' => $clientId,
      'description' => 'Registro asociado',
      'price' => 10,
      'price_ves' => 1000,
      'due_date' => '2026-12-31',
    ]);

    $response = $this->get("/clientes/$clientId/eliminar");

    self::assertSame(303, $response->getStatusCode());
    self::assertSame(1, $this->clientCount($client['name']));
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
        'DELETE FROM repairs WHERE client_id IN (SELECT id FROM clients WHERE user_id = :user_id)',
      )->execute(['user_id' => $userId]);
      $this->database->prepare(
        'DELETE FROM clients WHERE user_id = :user_id',
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

  /** @return array{name: string, id_card: string, phone: string, address: string} */
  private function client(string $name): array
  {
    return [
      'name' => $name,
      'id_card' => 'V-' . random_int(10_000_000, 99_999_999),
      'phone' => '0412-555-0100',
      'address' => 'Avenida Principal, edificio 1',
    ];
  }

  /** @return array{name: string, id_card: string, phone: string, address: string} */
  private function clientByName(string $name): array
  {
    $statement = $this->database->prepare(
      'SELECT name, id_card, phone, address FROM clients WHERE name = :name AND user_id = :user_id',
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

  private function clientId(string $name): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM clients WHERE name = :name AND user_id = :user_id',
    );
    $statement->execute(['name' => $name, 'user_id' => $this->userId()]);

    return (int) $statement->fetchColumn();
  }

  private function clientCount(string $name): int
  {
    $statement = $this->database->prepare(
      'SELECT COUNT(*) FROM clients WHERE name = :name AND user_id = :user_id',
    );
    $statement->execute(['name' => $name, 'user_id' => $this->userId()]);

    return (int) $statement->fetchColumn();
  }

  private function clientCountByIdCard(string $idCard): int
  {
    $statement = $this->database->prepare(
      'SELECT COUNT(*) FROM clients WHERE id_card = :id_card AND user_id = :user_id',
    );
    $statement->execute(['id_card' => $idCard, 'user_id' => $this->userId()]);

    return (int) $statement->fetchColumn();
  }

  private function userId(): int
  {
    $statement = $this->database->prepare('SELECT id FROM users WHERE email = :email');
    $statement->execute(['email' => $this->email]);

    return (int) $statement->fetchColumn();
  }
}
