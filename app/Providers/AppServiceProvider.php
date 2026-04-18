<?php

namespace App\Providers;

use App\Domains\Auth\Models\Permission;
use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Policies\PermissionPolicy;
use App\Domains\Auth\Policies\RolePolicy;
use App\Domains\Auth\Policies\UserPolicy;
use App\Domains\Patients\Models\Patient;
use App\Domains\Patients\Policies\PatientPolicy;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);
    }
}
