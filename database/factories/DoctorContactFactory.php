<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorContactFactory extends Factory
{
    public function definition()
    {
        return [
            'doctor_id' => Doctor::inRandomOrder()->first()->id ?? Doctor::factory(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'telegram' => '@'.$this->faker->userName(),
            'whatsapp' => $this->faker->phoneNumber(),
            'max' => $this->faker->url(),
        ];
    }
}
