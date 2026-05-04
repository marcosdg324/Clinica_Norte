<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'bioquimico_id')) {
            return;
        }

        $this->clearInvalidImagingAssigneesOnOrders();

        if (Schema::hasTable('imaging_studies') && Schema::hasColumn('imaging_studies', 'bioquimico_id')) {
            $this->clearInvalidImagingAssigneesOnStudies();
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['bioquimico_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('bioquimico_id', 'responsible_user_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('responsible_user_id')->references('id')->on('users')->onDelete('restrict');
        });

        if (Schema::hasTable('imaging_studies') && Schema::hasColumn('imaging_studies', 'bioquimico_id')) {
            Schema::table('imaging_studies', function (Blueprint $table) {
                $table->dropForeign(['bioquimico_id']);
            });

            Schema::table('imaging_studies', function (Blueprint $table) {
                $table->renameColumn('bioquimico_id', 'responsible_user_id');
            });

            Schema::table('imaging_studies', function (Blueprint $table) {
                $table->foreign('responsible_user_id')->references('id')->on('users')->onDelete('restrict');
            });
        }
    }

    /**
     * Órdenes de imagen: el asignado debe ser Tecnólogo de Imagen (no Bioquímico u otro).
     */
    private function clearInvalidImagingAssigneesOnOrders(): void
    {
        foreach (DB::table('orders')->where('type', 'imagen')->whereNotNull('bioquimico_id')->get() as $row) {
            $user = User::with('roles')->find($row->bioquimico_id);
            if (! $user || ! $user->hasRole('Tecnólogo de Imagen')) {
                DB::table('orders')->where('id', $row->id)->update(['bioquimico_id' => null]);
            }
        }
    }

    /**
     * Estudios de imagen: mismo criterio para el profesional asignado.
     */
    private function clearInvalidImagingAssigneesOnStudies(): void
    {
        foreach (DB::table('imaging_studies')->whereNotNull('bioquimico_id')->get() as $row) {
            $user = User::with('roles')->find($row->bioquimico_id);
            if (! $user || ! $user->hasRole('Tecnólogo de Imagen')) {
                DB::table('imaging_studies')->where('id', $row->id)->update(['bioquimico_id' => null]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'responsible_user_id')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['responsible_user_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('responsible_user_id', 'bioquimico_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('bioquimico_id')->references('id')->on('users')->onDelete('restrict');
        });

        if (Schema::hasTable('imaging_studies') && Schema::hasColumn('imaging_studies', 'responsible_user_id')) {
            Schema::table('imaging_studies', function (Blueprint $table) {
                $table->dropForeign(['responsible_user_id']);
            });

            Schema::table('imaging_studies', function (Blueprint $table) {
                $table->renameColumn('responsible_user_id', 'bioquimico_id');
            });

            Schema::table('imaging_studies', function (Blueprint $table) {
                $table->foreign('bioquimico_id')->references('id')->on('users')->onDelete('restrict');
            });
        }
    }
};
