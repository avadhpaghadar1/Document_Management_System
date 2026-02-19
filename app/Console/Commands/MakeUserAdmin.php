<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    protected $signature = 'dms:make-admin {--id= : User ID} {--email= : User email}';

    protected $description = 'Grant a user all permissions (admin)';

    public function handle(): int
    {
        $id = $this->option('id');
        $email = $this->option('email');

        $user = null;
        if ($id !== null) {
            $user = User::query()->find($id);
        } elseif ($email !== null) {
            $user = User::query()->where('email', $email)->first();
        } else {
            $userCount = User::query()->count();

            if ($userCount === 1) {
                $user = User::query()->first();
            } else {
                $this->error('Multiple users exist. Specify --id or --email.');
                return self::INVALID;
            }
        }

        if (!$user) {
            $this->error('User not found.');
            return self::FAILURE;
        }

        $permissionIds = Permission::query()->pluck('id')->all();
        if (count($permissionIds) === 0) {
            $this->warn('No permissions found in the permissions table. Nothing to grant.');
            return self::SUCCESS;
        }

        $user->role = 'admin';
        $user->save();
        $user->permissions()->sync($permissionIds);

        $this->info("User #{$user->id} ({$user->email}) now has " . count($permissionIds) . ' permissions.');

        return self::SUCCESS;
    }
}
