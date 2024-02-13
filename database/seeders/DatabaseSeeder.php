<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $category = new Category();
        $category->name = "English";
        $category->save();
        $category = new Category();
        $category->name = "History";
        $category->save();
        $category = new Category();
        $category->name = "Math";
        $category->save();
        $category = new Category();
        $category->name = "Science";
        $category->save();
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
