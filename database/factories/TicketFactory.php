<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ticketTypes = [
            [
                'title' => 'Unable to login to account',
                'description' => 'I keep getting an error message when trying to log in. It says my username or password is incorrect, but I know they are right. Can you help me reset my account?',
                'category' => 'Account'
            ],
            [
                'title' => 'Website loading very slowly',
                'description' => 'The website takes forever to load pages. Sometimes it times out completely. This is affecting my work productivity. Please investigate server performance.',
                'category' => 'Technical'
            ],
            [
                'title' => 'Billing question about subscription',
                'description' => 'I was charged twice for my monthly subscription. Can you please refund the duplicate charge and explain why this happened?',
                'category' => 'Billing'
            ],
            [
                'title' => 'Feature request: Dark mode',
                'description' => 'Would love to see a dark mode option for the application. Many users prefer dark themes, especially for extended use.',
                'category' => 'Feature Request'
            ],
            [
                'title' => 'Bug: Button not working',
                'description' => 'The save button on the profile page is not responding when clicked. I have tried different browsers but the issue persists.',
                'category' => 'Bug Report'
            ],
            [
                'title' => 'How to export data',
                'description' => 'I need to export my data for backup purposes. Could you please guide me through the process?',
                'category' => 'General'
            ],
            [
                'title' => 'Account suspended unexpectedly',
                'description' => 'My account was suspended without any warning. I have not violated any terms of service. Please restore my access.',
                'category' => 'Account'
            ],
            [
                'title' => 'Payment method update needed',
                'description' => 'My credit card expired and I need to update the payment method for my subscription. How can I do this?',
                'category' => 'Billing'
            ]
        ];

        $template = $this->faker->randomElement($ticketTypes);
        
        return [
            'user_id' => User::factory(),
            'title' => $template['title'],
            'description' => $template['description'],
            'status' => fake()->randomElement(['A', 'C', 'H', 'X']),
            'notes' => $this->faker->optional(0.6)->sentence(),
            'category' => $this->faker->optional(0.3)->randomElement(['Technical', 'Billing', 'Account', 'Feature Request', 'Bug Report', 'General']),
            'explanation' => $this->faker->optional(0.3)->sentence(),
            'confidence' => $this->faker->optional(0.3)->randomFloat(2, 0.6, 0.95),
        ];
    }
}