<?php

declare(strict_types=1);

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\RequestOptions;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

abstract class FeatureTestCase extends TestCase
{
  protected static ClientInterface $client;
  protected static RequestFactoryInterface $requestFactory;
  protected static StreamFactoryInterface $streamFactory;
  protected CookieJar $cookies;

  #[Override]
  protected function setUp(): void
  {
    parent::setUp();

    $this->cookies = new CookieJar;
    self::$client = new Client([
      'base_uri' => 'http://localhost',
      RequestOptions::ALLOW_REDIRECTS => false,
      RequestOptions::COOKIES => $this->cookies,
    ]);

    self::$requestFactory = new HttpFactory();
    self::$streamFactory = new HttpFactory();
  }

  protected function get(string $uri): ResponseInterface
  {
    return self::$client->sendRequest(
      self::$requestFactory->createRequest('GET', $uri),
    );
  }

  /** @param array<string, string> $data */
  protected function post(string $uri, array $data): ResponseInterface
  {
    $request = self::$requestFactory
      ->createRequest('POST', $uri)
      ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
      ->withBody(self::$streamFactory->createStream(http_build_query($data)));

    return self::$client->sendRequest($request);
  }
}
