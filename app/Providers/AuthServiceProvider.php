<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
         'App\Models\User' => 'App\Policies\UserPolicy',
         'App\Models\Group' => 'App\Policies\GroupPolicy',
         'App\Models\Role' => 'App\Policies\RolePolicy',
         'App\Models\Permission' => 'App\Policies\PermissionPolicy',
         'App\Models\Training' => 'App\Policies\TrainingPolicy',
         'App\Models\Activity' => 'App\Policies\ActivityPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
