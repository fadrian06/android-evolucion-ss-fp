<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final readonly class BcvExchangeRate
{
  public function fetch(): object
  {
    $key = $_ENV['DOLARVZLA_KEY'] ?? '';

    if (!is_string($key) || trim($key) === '') {
      throw new \RuntimeException('La clave DOLARVZLA_KEY no está configurada');
    }

    try {
      $response = (new Client(['timeout' => 10, 'verify' => false]))->get(
        'https://rates.dolarvzla.com/bcv/current.json',
        ['headers' => ['x-dolarvzla-key' => $key]],
      );
      $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
    } catch (GuzzleException | \JsonException $exception) {
      throw new \RuntimeException('No se pudo obtener la cotización BCV', previous: $exception);
    }

    if (
      !is_object($data)
      || !isset($data->current)
      || !is_object($data->current)
      || !isset($data->current->usd)
      || !is_numeric($data->current->usd)
    ) {
      throw new \RuntimeException('La respuesta de la cotización BCV no tiene una tasa USD válida');
    }

    return $data;
  }

  public function rate(): float
  {
    return (float) $this->fetch()->current->usd;
  }
}
