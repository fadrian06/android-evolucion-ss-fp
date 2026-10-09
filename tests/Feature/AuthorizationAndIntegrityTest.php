<?php

declare(strict_types=1);

namespace Tests\Feature;

use Override;
use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\Test;

final class AuthorizationAndIntegrityTest extends FeatureTestCase
{
  private PDO $database;
  private string $ownerEmail;
  private string $otherUserEmail;
  private int $ownerBusinessId;
  private int $otherUserBusinessId;
  private int $ownerClientId;
  private int $ownerProductId;

  #[Override]
  protected function setUp(): void
  {
    parent::setUp();

    $path = parse_ini_file(dirname(__DIR__, 2) . '/.env')['DB_DATABASE'];
    $this->database = new PDO('sqlite:' . dirname(__DIR__, 2) . '/' . $path);
    $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $suffix = bin2hex(random_bytes(6));
    $this->ownerEmail = "owner-$suffix@example.test";
    $this->otherUserEmail = "other-$suffix@example.test";

    $this->post('/registrarse', ['email' => $this->ownerEmail, 'password' => 'feature-password']);
    $ownerBusiness = $this->business("Owner $suffix", "J-$suffix", "Owner address $suffix");
    $this->post('/negocios', $ownerBusiness);
    $this->ownerBusinessId = $this->businessId($ownerBusiness['name']);
    $this->get("/negocios/$this->ownerBusinessId/seleccionar");
    $this->post('/calculadora/cotizacion', ['rate' => '100']);

    $ownerClient = [
      'name' => "Owner client $suffix",
      'id_card' => "V-$suffix",
      'phone' => '0412-555-0100',
      'address' => 'Owner client address',
    ];
    $this->post('/clientes', $ownerClient);
    $this->ownerClientId = $this->clientId($ownerClient['name'], $this->ownerId());
    $ownerProduct = [
      'name' => "Owner product $suffix",
      'category' => 'accessory',
      'price' => '10',
      'stocks' => [$this->ownerBusinessId => 1],
    ];
    $this->post('/productos', $ownerProduct);
    $this->ownerProductId = $this->productId($ownerProduct['name'], $this->ownerId());

    $this->get('/cerrar-sesion');
    $this->post('/registrarse', ['email' => $this->otherUserEmail, 'password' => 'feature-password']);
    $otherBusiness = $this->business("Other $suffix", "J-other-$suffix", "Other address $suffix");
    $this->post('/negocios', $otherBusiness);
    $this->otherUserBusinessId = $this->businessId($otherBusiness['name']);
    $this->get("/negocios/$this->otherUserBusinessId/seleccionar");
    $this->post('/calculadora/cotizacion', ['rate' => '100']);
  }

  #[Test]
  public function users_cannot_select_or_modify_another_users_records(): void
  {
    $selection = $this->get("/negocios/$this->ownerBusinessId/seleccionar");
    $clientUpdate = $this->post("/clientes/$this->ownerClientId", [
      'name' => 'Attempted update',
      'id_card' => 'V-00000000',
      'phone' => '0412-555-0199',
      'address' => 'Attempted address',
    ]);
    $productUpdate = $this->post("/productos/$this->ownerProductId", [
      'name' => 'Attempted product update',
      'category' => 'spare_part',
      'price' => '20',
      'stocks' => [$this->otherUserBusinessId => 1],
    ]);
    $clientDeletion = $this->get("/clientes/$this->ownerClientId/eliminar");
    $productDeletion = $this->get("/productos/$this->ownerProductId/eliminar");

    self::assertSame(303, $selection->getStatusCode());
    self::assertSame('/negocios', $selection->getHeaderLine('Location'));
    self::assertSame(303, $clientUpdate->getStatusCode());
    self::assertSame(303, $productUpdate->getStatusCode());
    self::assertSame(303, $clientDeletion->getStatusCode());
    self::assertSame(303, $productDeletion->getStatusCode());
    self::assertSame(
      "Owner client " . substr($this->ownerEmail, 6, 12),
      $this->database->query("SELECT name FROM clients WHERE id = $this->ownerClientId")->fetchColumn(),
    );
    self::assertSame(
      "Owner product " . substr($this->ownerEmail, 6, 12),
      $this->database->query("SELECT name FROM products WHERE id = $this->ownerProductId")->fetchColumn(),
    );
  }

  #[Test]
  public function database_constraints_reject_duplicate_primary_and_unique_keys(): void
  {
    $ownerId = $this->ownerId();
    $ownerClient = $this->database->query(
      "SELECT name, id_card FROM clients WHERE id = $this->ownerClientId",
    )->fetch(PDO::FETCH_ASSOC);
    $ownerProduct = $this->database->query(
      "SELECT name FROM products WHERE id = $this->ownerProductId",
    )->fetch(PDO::FETCH_ASSOC);

    $this->assertConstraintViolation(
      'INSERT INTO clients (id, user_id, name, id_card, phone, address) VALUES (:id, :user_id, :name, :id_card, :phone, :address)',
      [
        'id' => $this->ownerClientId,
        'user_id' => $ownerId,
        'name' => 'Duplicate primary key',
        'id_card' => 'V-duplicate-primary',
        'phone' => '0412-555-0100',
        'address' => 'Duplicate primary key address',
      ],
    );
    $this->assertConstraintViolation(
      'INSERT INTO clients (user_id, name, id_card, phone, address) VALUES (:user_id, :name, :id_card, :phone, :address)',
      [
        'user_id' => $ownerId,
        'name' => $ownerClient['name'],
        'id_card' => 'V-duplicate-name',
        'phone' => '0412-555-0100',
        'address' => 'Duplicate name address',
      ],
    );
    $this->assertConstraintViolation(
      'INSERT INTO clients (user_id, name, id_card, phone, address) VALUES (:user_id, :name, :id_card, :phone, :address)',
      [
        'user_id' => $ownerId,
        'name' => 'Duplicate ID card',
        'id_card' => $ownerClient['id_card'],
        'phone' => '0412-555-0100',
        'address' => 'Duplicate ID card address',
      ],
    );
    $this->assertConstraintViolation(
      'INSERT INTO products (user_id, name, category, price) VALUES (:user_id, :name, :category, :price)',
      [
        'user_id' => $ownerId,
        'name' => $ownerProduct['name'],
        'category' => 'accessory',
        'price' => 10,
      ],
    );
  }

  /** @param array<string, int|string> $parameters */
  private function assertConstraintViolation(string $query, array $parameters): void
  {
    try {
      $this->database->prepare($query)->execute($parameters);
      self::fail('Expected a database constraint violation.');
    } catch (PDOException) {
      self::addToAssertionCount(1);
    }
  }

  /** @return array{name: string, rif: string, address: string, phone: string} */
  private function business(string $name, string $rif, string $address): array
  {
    return [
      'name' => $name,
      'rif' => $rif,
      'address' => $address,
      'phone' => '0412-555-0100',
    ];
  }

  private function businessId(string $name): int
  {
    $statement = $this->database->prepare('SELECT id FROM businesses WHERE name = :name');
    $statement->execute(['name' => $name]);

    return (int) $statement->fetchColumn();
  }

  private function clientId(string $name, int $userId): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM clients WHERE name = :name AND user_id = :user_id',
    );
    $statement->execute(['name' => $name, 'user_id' => $userId]);

    return (int) $statement->fetchColumn();
  }

  private function productId(string $name, int $userId): int
  {
    $statement = $this->database->prepare(
      'SELECT id FROM products WHERE name = :name AND user_id = :user_id',
    );
    $statement->execute(['name' => $name, 'user_id' => $userId]);

    return (int) $statement->fetchColumn();
  }

  private function ownerId(): int
  {
    $statement = $this->database->prepare('SELECT id FROM users WHERE email = :email');
    $statement->execute(['email' => $this->ownerEmail]);

    return (int) $statement->fetchColumn();
  }

  #[Override]
  protected function tearDown(): void
  {
    foreach ([$this->ownerEmail, $this->otherUserEmail] as $email) {
      $user = $this->database->prepare('SELECT id FROM users WHERE email = :email');
      $user->execute(['email' => $email]);
      $userId = $user->fetchColumn();

      if ($userId === false) {
        continue;
      }

      $this->database->prepare('DELETE FROM batches WHERE product_id IN (SELECT id FROM products WHERE user_id = :user_id)')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM products WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM clients WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM exchange_rates WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM businesses WHERE user_id = :user_id')->execute(['user_id' => $userId]);
      $this->database->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $userId]);
    }

    parent::tearDown();
  }
}
