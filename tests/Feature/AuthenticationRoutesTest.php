<?php

declare(strict_types=1);

namespace Tests\Feature;

use Override;
use PDO;
use PHPUnit\Framework\Attributes\Test;

final class AuthenticationRoutesTest extends FeatureTestCase
{
  /** @var list<string> */
  private array $registeredEmails = [];

  #[Test]
  public function authentication_pages_are_available_to_guests(): void
  {
    $login = $this->get('/ingresar');
    $registration = $this->get('/registrarse');

    self::assertSame(200, $login->getStatusCode());
    self::assertStringContainsString('Iniciar sesión', (string) $login->getBody());
    self::assertSame(200, $registration->getStatusCode());
    self::assertStringContainsString('Crear cuenta', (string) $registration->getBody());
  }

  #[Test]
  public function registration_authenticates_the_new_user(): void
  {
    $credentials = $this->credentials();

    $response = $this->post('/registrarse', $credentials);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/', $response->getHeaderLine('Location'));

    $home = $this->get('/');

    self::assertSame(303, $home->getStatusCode());
    self::assertSame('/negocios', $home->getHeaderLine('Location'));
  }

  #[Test]
  public function valid_credentials_start_a_session(): void
  {
    $credentials = $this->credentials();
    $this->post('/registrarse', $credentials);
    $this->get('/cerrar-sesion');

    $response = $this->post('/ingresar', $credentials);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/', $response->getHeaderLine('Location'));
  }

  #[Test]
  public function invalid_credentials_are_rejected(): void
  {
    $response = $this->post('/ingresar', [
      'email' => 'unknown-' . bin2hex(random_bytes(8)) . '@example.test',
      'password' => 'invalid-password',
    ]);

    self::assertSame(303, $response->getStatusCode());
    self::assertSame('/ingresar', $response->getHeaderLine('Location'));
  }

  #[Test]
  public function logout_revokes_access_to_protected_routes(): void
  {
    $this->post('/registrarse', $this->credentials());

    $logout = $this->get('/cerrar-sesion');
    $home = $this->get('/');

    self::assertSame(303, $logout->getStatusCode());
    self::assertSame('/ingresar', $logout->getHeaderLine('Location'));
    self::assertSame(303, $home->getStatusCode());
    self::assertSame('/ingresar', $home->getHeaderLine('Location'));
  }

  #[Override]
  protected function tearDown(): void
  {
    $database = parse_ini_file(dirname(__DIR__, 2) . '/.env')['DB_DATABASE'];
    $connection = new PDO('sqlite:' . dirname(__DIR__, 2) . '/' . $database);
    $statement = $connection->prepare('DELETE FROM users WHERE email = :email');

    foreach ($this->registeredEmails as $email) {
      $statement->execute(['email' => $email]);
    }

    parent::tearDown();
  }

  /** @return array{email: string, password: string} */
  private function credentials(): array
  {
    $email = 'feature-' . bin2hex(random_bytes(8)) . '@example.test';
    $this->registeredEmails[] = $email;

    return ['email' => $email, 'password' => 'feature-password'];
  }
}
