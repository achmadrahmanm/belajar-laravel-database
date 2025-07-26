<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CounterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // You can seed the counters table here
        // Example:
        // DB::table('counters')->insert([
        //     ['name' => 'Counter 1', 'description' => 'Description for counter 1'],
        //     ['name' => 'Counter 2', 'description' => 'Description for counter 2'],
        // ]);

        DB::table('counters')->insert([
            'id' => 'sample',
            'counter' => 0,
            'description' => 'This is the main counter'
        ]);
    }
}
