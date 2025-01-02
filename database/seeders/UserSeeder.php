<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Crear dos usuarios predefinidos
        User::create([
            'name' => 'Usuario 1',
            'email' => 'usuario1@example.com',
            'password' => Hash::make('password1234'),
        ]);

        User::create([
            'name' => 'Usuario 2',
            'email' => 'usuario2@example.com',
            'password' => Hash::make('password12345'),
        ]);
    }
}
