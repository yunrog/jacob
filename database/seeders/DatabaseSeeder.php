<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@jacob.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        $brands = ['Samsung', 'Apple', 'Sony', 'Canon'];
        foreach ($brands as $brand) {
            Brand::firstOrCreate([
                'name' => $brand,
                'slug' => strtolower(str_replace(' ', '-', $brand)),
            ]);
        }

        $categories = ['Elektronik', 'Komputer', 'Gadget', 'Aksesoris'];
        foreach ($categories as $category) {
            Category::firstOrCreate([
                'name' => $category,
                'slug' => strtolower(str_replace(' ', '-', $category)),
            ]);
        }
    }
}
