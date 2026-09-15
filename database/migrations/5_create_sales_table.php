<?php

declare(strict_types=1);

use App\Models\Business;
use App\Models\Client;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

if (!Manager::schema()->hasTable('sales')) {
  Manager::schema()->create('sales', static function (Blueprint $blueprint) {
    $blueprint->id();
    $blueprint->foreignIdFor(Business::class)->constrained();
    $blueprint->foreignIdFor(Client::class)->constrained();
    $blueprint->timestamps();
  });
}
