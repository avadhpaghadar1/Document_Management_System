<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['day' => 30, 'name' => 'dayBefore'],
            ['day' => 7, 'name' => 'dayBefore'],
            ['day' => 1, 'name' => 'dayBefore'],
            ['day' => 1, 'name' => 'dayAfter'],
        ];

        User::query()->select('id')->orderBy('id')->each(function (User $user) use ($defaults) {
            $hasAny = Notification::query()->where('user_id', $user->id)->exists();
            if ($hasAny) {
                return;
            }

            foreach ($defaults as $row) {
                Notification::query()->create([
                    'user_id' => $user->id,
                    'day' => $row['day'],
                    'name' => $row['name'],
                ]);
            }
        });
    }
}
