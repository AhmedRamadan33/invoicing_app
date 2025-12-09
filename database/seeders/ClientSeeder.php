<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->hasRole('admin')) {
                $this->createClientsForUser($user, 3);
            } else {
                $this->createClientsForUser($user, 5);
            }
        }

        $this->command->info('Clients created successfully!');
    }

    private function createClientsForUser(User $user, int $count): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 1; $i <= $count; $i++) {
            Client::create([
                'user_id' => $user->id,
                'name' => $faker->company() . ' - ' . $user->name,
                'email' => strtolower(str_replace(' ', '.', $user->name)) . '.client' . $i . '@example.com',
                'phone' => $faker->phoneNumber(),
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'state' => $faker->state(),
                'country' => $faker->country(),
                'postal_code' => $faker->postcode(),
            ]);
        }
    }
}
