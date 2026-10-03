<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = ['coffee', 'non coffee', 'pastry', 'dessert'];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}