<?php

// Create sample permissions
$actions = ['viewAny', 'view', 'create', 'update', 'delete'];
foreach ($actions as $a) {
    \App\Domains\Auth\Models\Permission::firstOrCreate([
        'name' => 'samples.' . $a,
        'guard_name' => 'web',
    ]);
}
echo 'Permissions created: ' . implode(', ', array_map(fn($a) => 'samples.'.$a, $actions)) . PHP_EOL;

// Admin (id=1) - all samples
$admin = \App\Models\User::find(1);
$admin->givePermissionTo(['samples.viewAny','samples.view','samples.create','samples.update','samples.delete']);
echo 'Admin samples: ' . $admin->getDirectPermissions()->pluck('name')->filter(fn($n) => str_starts_with($n,'samples'))->implode(', ') . PHP_EOL;

// Bioquimico (id=6) - all samples
$bio = \App\Models\User::find(6);
$bio->givePermissionTo(['samples.viewAny','samples.view','samples.create','samples.update','samples.delete']);
echo 'Bioquimico samples: ' . $bio->getDirectPermissions()->pluck('name')->filter(fn($n) => str_starts_with($n,'samples'))->implode(', ') . PHP_EOL;

// Recepcionista (id=5) - limited samples
$recep = \App\Models\User::find(5);
$recep->givePermissionTo(['samples.viewAny','samples.view','samples.create']);
echo 'Recepcionista samples: ' . $recep->getDirectPermissions()->pluck('name')->filter(fn($n) => str_starts_with($n,'samples'))->implode(', ') . PHP_EOL;

app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
echo 'Cache cleared' . PHP_EOL;
