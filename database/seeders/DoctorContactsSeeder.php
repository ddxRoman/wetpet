<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\DoctorContact;

class DoctorContactsSeeder extends Seeder
{
    public function run()
    {
        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {
            DoctorContact::create([
                'doctor_id' => $doctor->id,
                'phone' => fake()->phoneNumber(),
                'email' => fake()->safeEmail(),
                'telegram' => '@' . fake()->userName(),
                'whatsapp' => fake()->phoneNumber(),
                'max' => fake()->url(),
            ]);
        }
    }
}
