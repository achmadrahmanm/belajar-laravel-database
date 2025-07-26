<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // You can seed the categories table here
        // Example:
        // DB::table('categories')->insert([
        //     ['name' => 'Category 1', 'description' => 'Description for category 1'],
        //     ['name' => 'Category 2', 'description' => 'Description for category 2'],
        // ]);

        DB::table('categories')->insert([
            'id' => 'GADGET',
            'name' => 'Gadget',
            'description' => 'Gadget Category',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('categories')->insert([
            'id' => 'FOOD',
            'name' => 'Food',
            'description' => 'Food Category',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('categories')->insert([
            'id' => 'PHONE',
            'name' => 'Phone',
            'description' => 'Phone Category',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('categories')->insert([
            'id' => 'LAPTOP',
            'name' => 'Laptop',
            'description' => 'Laptop Category',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
