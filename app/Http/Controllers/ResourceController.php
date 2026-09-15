<?php

declare(strict_types=1);

namespace App\Http\Controllers;

abstract readonly class ResourceController
{
  abstract public function index(): void;
  abstract public function create(): void;
  abstract public function store(): void;
  abstract public function show(string $id): void;
  abstract public function edit(string $id): void;
  abstract public function update(string $id): void;
  abstract public function destroy(string $id): void;
}
