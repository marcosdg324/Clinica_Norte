<?php

namespace App\Providers;

use App\Domains\Auth\Models\Permission;
use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Policies\PermissionPolicy;
use App\Domains\Auth\Policies\RolePolicy;
use App\Domains\Auth\Policies\UserPolicy;
use App\Domains\Catalog\Models\Exam;
use App\Domains\Catalog\Models\ExamCategory;
use App\Domains\Catalog\Models\ExamParameter;
use App\Domains\Catalog\Models\ExamRequirement as CatalogExamRequirement;
use App\Domains\Catalog\Observers\ExamCategoryObserver;
use App\Domains\Catalog\Policies\ExamCategoryPolicy;
use App\Domains\Catalog\Policies\ExamParameterPolicy;
use App\Domains\Catalog\Policies\ExamPolicy;
use App\Domains\Catalog\Policies\ExamRequirementPolicy;
use App\Domains\Imaging\Models\ImagingEquipment;
use App\Domains\Imaging\Models\ImagingStudy;
use App\Domains\Imaging\Observers\ImagingStudyObserver;
use App\Domains\Imaging\Policies\ImagingEquipmentPolicy;
use App\Domains\Imaging\Policies\ImagingStudyPolicy;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Policies\OrderPolicy;
use App\Domains\Patients\Models\Patient;
use App\Domains\Patients\Observers\PatientObserver;
use App\Domains\Patients\Policies\PatientPolicy;
use App\Domains\Samples\Models\Sample;
use App\Domains\Samples\Observers\SampleObserver;
use App\Domains\Samples\Policies\SamplePolicy;
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
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Sample::class, SamplePolicy::class);
        Gate::policy(ImagingEquipment::class, ImagingEquipmentPolicy::class);
        Gate::policy(ImagingStudy::class, ImagingStudyPolicy::class);
        Gate::policy(ExamCategory::class, ExamCategoryPolicy::class);
        Gate::policy(ExamParameter::class, ExamParameterPolicy::class);
        Gate::policy(CatalogExamRequirement::class, ExamRequirementPolicy::class);

        Sample::observe(SampleObserver::class);
        ImagingStudy::observe(ImagingStudyObserver::class);
        Patient::observe(PatientObserver::class);
        ExamCategory::observe(ExamCategoryObserver::class);
    }
}
