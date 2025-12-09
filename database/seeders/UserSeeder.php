<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@invoice.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('123456789'),
                'email_verified_at' => now(),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $admin->assignRole('admin');
        }

        $user = User::firstOrCreate(
            ['email' => 'user@invoice.com'],
            [
                'name' => 'John Doe',
                'password' => bcrypt('123456789'),
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $user->assignRole('user');
        }

        User::factory(3)->create()->each(function ($user) {
            $user->assignRole('user');
        });

        $this->command->info(' Users created successfully!');
        $this->command->info(' Admin: admin@invoice.com ');
        $this->command->info(' User: user@invoice.com ');
    }
}