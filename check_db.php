<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\Permission;

echo "=== COMMAND 1: Verify database state ===" . PHP_EOL;
echo 'Users: ' . User::count() . PHP_EOL;
echo 'Roles: ' . Role::count() . PHP_EOL;
echo 'Permissions: ' . Permission::count() . PHP_EOL;
$admin = User::where('email','admin@clinicanorte.com')->first();
echo 'Admin exists: ' . ($admin ? 'YES' : 'NO') . PHP_EOL;
if ($admin) {
    echo 'Admin roles: ' . $admin->getRoleNames()->implode(', ') . PHP_EOL;
    echo 'Admin can users.viewAny: ' . ($admin->can('users.viewAny') ? 'YES' : 'NO') . PHP_EOL;
    echo 'Admin can roles.viewAny: ' . ($admin->can('roles.viewAny') ? 'YES' : 'NO') . PHP_EOL;
    echo 'Admin can permissions.viewAny: ' . ($admin->can('permissions.viewAny') ? 'YES' : 'NO') . PHP_EOL;
    try {
        $panel = app(\Filament\Panel::class);
        echo 'Admin canAccessPanel: ' . ($admin->canAccessPanel($panel) ? 'YES' : 'NO') . PHP_EOL;
    } catch (Exception $e) {
        echo 'Admin canAccessPanel: ERROR - ' . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "=== COMMAND 2: Verify all roles with permissions ===" . PHP_EOL;
foreach(Role::with('permissions')->get() as $r) {
    echo $r->name . ': ' . $r->permissions->count() . ' permisos' . PHP_EOL;
}

echo PHP_EOL . "=== COMMAND 3: Verify password ===" . PHP_EOL;
$u = User::where('email','admin@clinicanorte.com')->first();
if ($u) {
    echo 'Password valid: ' . (Illuminate\Support\Facades\Hash::check('Admin@2026!', $u->password) ? 'YES' : 'NO') . PHP_EOL;
} else {
    echo 'User not found' . PHP_EOL;
}
