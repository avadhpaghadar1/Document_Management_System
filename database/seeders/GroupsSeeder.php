<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupsSeeder extends Seeder
{
    public function run(): void
    {
        $userId = (int) DB::table('users')->orderBy('id')->value('id');
        if ($userId <= 0) {
            return;
        }

        foreach (['Management', 'HR', 'Finance'] as $name) {
            Group::query()->firstOrCreate([
                'user_id' => $userId,
                'name' => $name,
            ]);
        }
    }
}
