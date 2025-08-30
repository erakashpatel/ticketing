<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create users for testing
        //$users = \App\Models\User::factory(10)->create();
        
        // Create a test admin user
        $user = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        \App\Models\Ticket::factory(100)
            ->recycle($user)
            ->create();

        $tickets = \App\Models\Ticket::inRandomOrder()->limit(30)->get();
        foreach ($tickets as $ticket) {
            $ticket->update([
                'notes' => fake()->sentence(10) . ' - Internal note for tracking purposes.'
            ]);
        }
    }
}
