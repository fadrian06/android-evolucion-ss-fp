<?php

declare(strict_types=1);

namespace Tests\Unit;

use Override;
use PHPUnit\Framework\Attributes\Test;

use function App\foreach_file;

final class ForeachFileTest extends UnitTestCase
{
  private string $tempDir;

  #[Override]
  protected function setUp(): void
  {
    parent::setUp();

    $this->tempDir =
      sys_get_temp_dir()
      . DIRECTORY_SEPARATOR
      . 'foreach_file_test_'
      . bin2hex(random_bytes(8));
    mkdir(directory: $this->tempDir);
  }

  #[Override]
  protected function tearDown(): void
  {
    if (is_dir($this->tempDir)) {
      $files = glob($this->tempDir . DIRECTORY_SEPARATOR . '*');
      if ($files) {
        foreach ($files as $file) {
          if (!is_file($file)) {
            continue;
          }

          unlink(filename: $file);
        }
      }

      rmdir(directory: $this->tempDir);
    }

    parent::tearDown();
  }

  #[Test]
  public function it_iterates_over_matching_files(): void
  {
    $file1 = $this->tempDir . DIRECTORY_SEPARATOR . 'a.txt';
    $file2 = $this->tempDir . DIRECTORY_SEPARATOR . 'b.txt';
    $other = $this->tempDir . DIRECTORY_SEPARATOR . 'c.log';

    file_put_contents(filename: $file1, data: '1');
    file_put_contents(filename: $file2, data: '2');
    file_put_contents(filename: $other, data: '3');

    $visited = [];

    foreach_file(
      pattern: $this->tempDir . DIRECTORY_SEPARATOR . '*.txt',
      callable: static function (string $file) use (&$visited): void {
        $visited[] = $file;
      },
    );

    self::assertCount(2, $visited);
    self::assertContains($file1, $visited);
    self::assertContains($file2, $visited);
    self::assertNotContains($other, $visited);
  }

  #[Test]
  public function it_does_not_invoke_callback_when_no_files_match(): void
  {
    $called = false;

    foreach_file(
      pattern: $this->tempDir . DIRECTORY_SEPARATOR . '*.nonexistent',
      callable: static function (string $file) use (&$called): void {
        $called = true;
      },
    );

    self::assertFalse($called);
  }
}
