<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear the categories table before each test
        DB::delete('delete from categories');
    }

    public function testInsert()
    {
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

        $results = DB::table('categories')->get();
        $this->assertCount(4, $results, 'Expected 4 categories after insert');
    }

    public function testSelect()
    {
        $this->testInsert(); // Ensure data is inserted first

        $collection = DB::table('categories')->select('id', 'name')->get();
        $this->assertCount(4, $collection, 'Expected 4 categories after insert');

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhere()
    {
        $this->testInsert(); // Ensure data is inserted first

        // Using where(column, operator, value)
        $category = DB::table('categories')->where('id', 'GADGET')->first();
        $this->assertEquals('Gadget', $category->name, 'Expected category name to be updated');

        // Using where([condition1, condition2])
        $categories = DB::table('categories')->where([
            ['id', '!=', 'GADGET']
        ])->get();
        $this->assertGreaterThan(0, $categories->count(), 'Expected categories matching conditions');

        // Using where(callback(Builder))
        $filteredCategories = DB::table('categories')->where(function ($query) {
            $query->where('id', 'FOOD')->orWhere('id', 'PHONE');
        })->get();
        $this->assertCount(2, $filteredCategories, 'Expected 2 categories matching callback conditions');
    }

    public function testOrWhere()
    {
        $this->testInsert(); // Ensure data is inserted first

        // Using orWhere(column, operator, value)
        $orWhereCategories = DB::table('categories')
            ->where('id', 'LAPTOP')
            ->orWhere('id', 'PHONE')
            ->get();
        $this->assertCount(2, $orWhereCategories, 'Expected 2 categories matching orWhere conditions');

        // Using orWhere(callback(Builder))
        $complexCategories = DB::table('categories')->where('id', 'GADGET')->orWhere(function ($query) {
            $query->where('id', 'FOOD')->orWhere('id', 'PHONE');
        })->get();
        $this->assertCount(3, $complexCategories, 'Expected 3 categories matching complex orWhere conditions');
    }

    public function testWhereBetween()
    {
        $this->testInsert(); // Ensure data is inserted first

        // Using whereBetween(column, [min, max])
        $betweenCategories = DB::table('categories')->whereBetween('created_at', ['2025-07-18 17:16:51', '2025-07-20 17:17:17'])->get();
        $this->assertCount(4, $betweenCategories, 'Expected 4 categories between 2025-07-18 17:16:51 and 2025-07-20 17:17:17');

        // Using whereNotBetween(column, [min, max])
        $notBetweenCategories = DB::table('categories')->whereNotBetween('created_at', ['2025-07-18 17:16:51', '2025-07-20 17:17:17'])->get();
        $this->assertCount(0, $notBetweenCategories, 'Expected no categories not between 2025-07-18 17:16:51 and 2025-07-20 17:17:17');
    }

    public function testWhereIn()
    {
        $this->testInsert(); // Ensure data is inserted first

        // Using whereIn(column, [values])
        $inCategories = DB::table('categories')->whereIn('id', ['GADGET', 'FOOD'])->get();
        $this->assertCount(2, $inCategories, 'Expected 2 categories in GADGET and FOOD');

        // Using whereNotIn(column, [values])
        $notInCategories = DB::table('categories')->whereNotIn('id', ['GADGET', 'FOOD'])->get();
        $this->assertCount(2, $notInCategories, 'Expected 2 categories not in GADGET and FOOD');
    }

    public function testWhereNull()
    {
        $this->testInsert(); // Ensure data is inserted first

        // Using whereNull(column)
        $nullCategories = DB::table('categories')->whereNull('updated_at')->get();
        $this->assertCount(0, $nullCategories, 'Expected 0 categories with null updated_at');

        // Using whereNotNull(column)
        $notNullCategories = DB::table('categories')->whereNotNull('updated_at')->get();
        $this->assertCount(4, $notNullCategories, 'Expected 4 categories with non-null updated_at');
    }

    public function testWhereDate()
    {
        $this->testInsert(); // Ensure data is inserted first

        // Using whereDate(column, date)
        $dateCategories = DB::table('categories')->whereDate('created_at', now()->toDateString())->get();
        $this->assertCount(4, $dateCategories, 'Expected 4 categories created today');

        // Using whereMonth(column, month)
        $monthCategories = DB::table('categories')->whereMonth('created_at', now()->month)->get();
        $this->assertCount(4, $monthCategories, 'Expected 4 categories created in the current month');

        // Using whereYear(column, year)
        $yearCategories = DB::table('categories')->whereYear('created_at', now()->year)->get();
        $this->assertCount(4, $yearCategories, 'Expected 4 categories created in the current year');
    }

    public function testUpdate()
    {
        $this->testInsert(); // Ensure data is inserted first

        DB::table('categories')->where('id', 'GADGET')->update(['name' => 'Updated Gadget']);
        $updatedCategory = DB::table('categories')->where('name', 'Updated Gadget')->first();
        $this->assertEquals('Updated Gadget', $updatedCategory->name, 'Expected category name to be updated');
        Log::info('Updated category: ' . json_encode($updatedCategory));
    }

    public function testUpdateOrInsert()
    {
        $this->testInsert(); // Ensure data is inserted first

        // Update existing category
        DB::table('categories')->updateOrInsert(
            ['id' => 'VOUCHER'],
            ['name' => 'Voucher', 'description' => 'Voucher Category', 'created_at' => now(), 'updated_at' => now()]
        );

        $updatedCategory = DB::table('categories')->where('id', 'VOUCHER')->first();
        $this->assertEquals('Voucher', $updatedCategory->name, 'Expected category name to be updated');
        Log::info('Updated or inserted category: ' . json_encode($updatedCategory));
    }
}
