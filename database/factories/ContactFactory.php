<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company' => $this->faker->company(),
            'position' => $this->faker->jobTitle(),
            'status' => $this->faker->randomElement(['lead', 'prospect', 'customer', 'partner']),
            'source' => $this->faker->randomElement(['website', 'referral', 'social_media', 'event', 'other']),
            'assigned_to' => User::factory(),
            'notes' => $this->faker->paragraph(),
        ];
    }
}