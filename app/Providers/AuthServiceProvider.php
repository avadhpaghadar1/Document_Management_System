<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
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
        Permission::all()->each(function ($permission) {
            Gate::define($permission->name, function (User $user) use ($permission) {
                return $user->permissions()->where('id', $permission->id)->exists();
            });
        });
    }
}
