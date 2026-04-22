<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ExamPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $actions = ['viewAny', 'view', 'create', 'update', 'delete'];

        foreach ($actions as $action) {
            Permission::firstOrCreate([
                'name'       => "exams.{$action}",
                'guard_name' => 'web',
            ]);
            $this->command->info("OK: exams.{$action}");
        }

        // Limpiar permiso vacío si se creó accidentalmente
        Permission::where('name', 'exams.')->delete();

        $this->command->info('exams.* permissions ready.');
    }
}
