<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('classifications')->insert([
            [
                'category_id' => '1', 
                'name' => 'flood',
                'color' =>  '#ff0000',
                'created_at' => now(), 
                'updated_at' => now()
                ],
            [
                'category_id' => '2', 
                'name' => 'hospital',
                'color' => '#0000ff',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'category_id' => '3', 
                'color' => '#00ff00',
                'name' => 'City/Municipality',
                'created_at' => now(), 
                'updated_at' => now()],
        ]);
        
    }
}
