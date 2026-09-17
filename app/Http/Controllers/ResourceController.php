<?php

declare(strict_types=1);

namespace App\Http\Controllers;

interface ResourceController
{
  public function index(): void;
  public function create(): void;
  public function store(): void;
  public function show(string $id): void;
  public function edit(string $id): void;
  public function update(string $id): void;
  public function destroy(string $id): void;
}
