<?php

namespace App\Console\Commands;

use App\Domains\Auth\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class SetupSamplesPermissions extends Command
{
    protected $signature = 'samples:setup-permissions';
    protected $description = 'Create and assign samples.* permissions to users';

    public function handle(): int
    {
        $actions = ['viewAny', 'view', 'create', 'update', 'delete'];

        foreach ($actions as $action) {
            Permission::firstOrCreate([
                'name'       => "samples.{$action}",
                'guard_name' => 'web',
            ]);
        }
        $this->info('Permissions created: ' . implode(', ', array_map(fn($a) => "samples.{$a}", $actions)));

        // Admin (id=1) — all samples
        $admin = User::find(1);
        if ($admin) {
            $admin->givePermissionTo(array_map(fn($a) => "samples.{$a}", $actions));
            $this->info('Admin: ' . $admin->getDirectPermissions()->pluck('name')->filter(fn($n) => str_starts_with($n, 'samples'))->implode(', '));
        }

        // Bioquímico (id=6) — all samples
        $bio = User::find(6);
        if ($bio) {
            $bio->givePermissionTo(array_map(fn($a) => "samples.{$a}", $actions));
            $this->info('Bioquímico: ' . $bio->getDirectPermissions()->pluck('name')->filter(fn($n) => str_starts_with($n, 'samples'))->implode(', '));
        }

        // Recepcionista (id=4) — limited: viewAny, view, create
        $recep = User::find(4);
        if ($recep) {
            $recep->givePermissionTo(['samples.viewAny', 'samples.view', 'samples.create']);
            $this->info('Recepcionista: ' . $recep->getDirectPermissions()->pluck('name')->filter(fn($n) => str_starts_with($n, 'samples'))->implode(', '));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->info('Cache cleared.');

        return self::SUCCESS;
    }
}
