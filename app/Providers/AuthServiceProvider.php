<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        try {
            if (!Schema::hasTable('permissions') || !Schema::hasTable('user_permissions')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        Permission::query()->each(function ($permission) {
            Gate::define($permission->name, function (User $user) use ($permission) {
                return $user->permissions()->where('id', $permission->id)->exists();
            });
        });
    }
}
