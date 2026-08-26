<?php

namespace Database\Factories;

use App\Models\TalentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TalentProfile>
 */
class TalentProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'category' => $this->faker->randomElement(['Band', 'Solo', 'MC']),
            'bio' => $this->faker->paragraph,
            'portfolio_url' => $this->faker->url,
            'contact_info' => $this->faker->phoneNumber,
        ];
    }
}
