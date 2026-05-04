<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$admin = User::whereHas('roles', fn ($q) => $q->where('name', 'Administrador'))->first();
echo 'Email: '.$admin->email.PHP_EOL;
