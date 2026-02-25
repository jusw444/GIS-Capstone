<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $disaster = Category::where('name', 'Disaster')->first();
    User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
        'category_id' => null,
        'password' => Hash::make('password123'),
        'role' => 'super_admin',
    ]);

    User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'category_id' => $disaster->id,
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);
}

}
