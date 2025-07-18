<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Traversable;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear the categories table before each test
        DB::delete('delete from products');
        DB::delete('delete from categories');

        DB::delete('delete from counters');
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

    public function testIncrement()
    {
        // update counter tor 0 first 
        DB::table('counters')->updateOrInsert(
            ['id' => 'sample'],
            ['counter' => 0]
        );

        // Increment the counter
        DB::table('counters')->where('id', 'sample')->increment('counter', 1);

        $updatedCounter = DB::table('counters')->where('id', 'sample')->first();
        $this->assertEquals(1, $updatedCounter->counter, 'Expected counter to be incremented');
        Log::info('Incremented counter: ' . json_encode($updatedCounter));
    }

    public function testDelete()
    {
        $this->testInsert(); // Ensure data is inserted first

        // Delete a specific category
        DB::table('categories')->where('id', 'GADGET')->delete();
        $remainingCategories = DB::table('categories')->get();
        $this->assertCount(3, $remainingCategories, 'Expected 3 categories after deletion');
        Log::info('Remaining categories after deletion: ' . json_encode($remainingCategories));
    }

    public function insertProductTable()
    {

        $this->testInsert(); // Ensure categories are inserted first

        DB::table('products')->insert([
            'id' => 'P001',
            'name' => 'Product 1',
            'description' => 'Description for Product 1',
            'price' => 100.00,
            'category_id' => 'GADGET',
            'created_at' => now()
        ]);

        DB::table('products')->insert([
            'id' => 'P002',
            'name' => 'Product 2',
            'description' => 'Description for Product 2',
            'price' => 200.00,
            'category_id' => 'FOOD',
            'created_at' => now()
        ]);

        DB::table('products')->insert([
            'id' => 'P003',
            'name' => 'Product 3',
            'description' => 'Description for Product 3',
            'price' => 300.00,
            'category_id' => 'PHONE',
            'created_at' => now()
        ]);

        DB::table('products')->insert([
            'id' => 'P004',
            'name' => 'Product 4',
            'description' => 'Description for Product 4',
            'price' => 400.00,
            'category_id' => 'LAPTOP',
            'created_at' => now()
        ]);
    }

    public function testQueryBuilderJoin()
    {
        $this->insertProductTable(); // Ensure data is inserted first

        // Join categories and products
        $results = DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->select('products.id', 'products.name', 'categories.name as category_name', 'products.price')
            ->get();

        $this->assertCount(4, $results, 'Expected 4 joined results');
        Log::info('Joined results: ' . json_encode($results));
    }

    public function testOrderBy()
    {
        $this->insertProductTable(); // Ensure data is inserted first

        // Order products by price
        $orderedProducts = DB::table('products')->orderBy('price', 'asc')->get();
        $this->assertCount(4, $orderedProducts, 'Expected 4 ordered products');
        Log::info('Ordered products: ' . json_encode($orderedProducts));
    }

    public function testQueryBuilderTakeSkip()
    {
        $this->insertProductTable(); // Ensure data is inserted first

        // Take 2 products and skip the first one
        $pagedProducts = DB::table('products')->skip(1)->take(2)->get();
        $this->assertCount(2, $pagedProducts, 'Expected 2 products after skip and take');
        Log::info('Paged products: ' . json_encode($pagedProducts));
    }

    public function InsertManyCategorues()
    {
        for ($i = 1; $i <= 100; $i++) {
            DB::table('categories')->insert([
                'id' => 'CATEGORY_' . $i,
                'name' => 'Category ' . $i,
                'description' => 'Description for Category ' . $i,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        $this->assertCount(100, DB::table('categories')->get(), 'Expected 100 categories to be inserted');
    }

    public function testQueryBuilderChunk()
    {
        $this->InsertManyCategorues(); // Ensure data is inserted first

        // Chunk categories into groups of 10
        DB::table('categories')->orderBy('id')->chunk(10, function ($categories) {
            Log::info('Start Chunked categories: ' . json_encode($categories));
            $categories->each(function ($category) {
                Log::info('Category: ' . json_encode($category));
            });
            Log::info('End Chunked categories: ' . json_encode($categories));

            $this->assertCount(10, $categories, 'Expected 10 categories in each chunk');
        });
    }

    public function testQueryBuilderLazyCollection()
    {
        $this->InsertManyCategorues(); // Ensure data is inserted first

        // Use lazy collection to process categories
        // Lazy is different from chunk, it does not load all data into memory at once
        // It will load data in chunks of 100 by default
        // You can specify the chunk size by passing an argument to lazy()
        // For example, lazy(100) will load data in chunks of 100
        // Lazy collection is useful for processing large datasets without loading all data into memory at once
        // It will load data in chunks of 100 by default
        $lazyCategories = DB::table('categories')->orderBy('id')->lazy(10)->take(10);
        $this->assertInstanceOf(LazyCollection::class, $lazyCategories, 'Expected lazy collection instance');

        $lazyCategories->each(function ($category) {
            Log::info('Lazy Category: ' . json_encode($category));
        });
    }
    public function testQueryBuilderCursor()
    {
        $this->InsertManyCategorues(); // Ensure data is inserted first

        // Use cursor to process categories
        // Cursor is similar to lazy collection, but it will load data in chunks of 100 by default
        // It will load data in chunks of 100 by default
        // You can specify the chunk size by passing an argument to cursor()
        // For example, cursor(100) will load data in chunks of 100
        $cursorCategories = DB::table('categories')->orderBy('id')->cursor();
        $this->assertInstanceOf(Traversable::class, $cursorCategories, 'Expected cursor to be traversable');

        foreach ($cursorCategories as $category) {
            Log::info('Cursor Category: ' . json_encode($category));
        }
    }

    public function testQueryBuilderAggregate()
    {
        $this->insertProductTable(); // Ensure data is inserted first

        // Count total products
        $totalProducts = DB::table('products')->count();
        $this->assertEquals(4, $totalProducts, 'Expected 4 total products');

        // Sum product prices
        $totalPrice = DB::table('products')->sum('price');
        $this->assertEquals(1000.00, $totalPrice, 'Expected total price to be 1000.00');

        // Average product price
        $averagePrice = DB::table('products')->avg('price');
        $this->assertEquals(250.00, $averagePrice, 'Expected average price to be 250.00');

        // Maximum product price
        $maxPrice = DB::table('products')->max('price');
        $this->assertEquals(400.00, $maxPrice, 'Expected maximum price to be 400.00');

        // Minimum product price
        $minPrice = DB::table('products')->min('price');
        $this->assertEquals(100.00, $minPrice, 'Expected minimum price to be 100.00');

        // Aggregate multiple columns
        $aggregates = DB::table('products')->select(
            DB::raw('count(*) as total_products'),
            DB::raw('sum(price) as total_price'),
            DB::raw('avg(price) as average_price'),
            DB::raw('max(price) as max_price'),
            DB::raw('min(price) as min_price')
        )->first();

        $this->assertEquals(4, $aggregates->total_products, 'Expected 4 total products');
        $this->assertEquals(1000.00, $aggregates->total_price, 'Expected total price to be 1000.00');
        $this->assertEquals(250.00, $aggregates->average_price, 'Expected average price to be 250.00');
        $this->assertEquals(400.00, $aggregates->max_price, 'Expected maximum price to be 400.00');
        $this->assertEquals(100.00, $aggregates->min_price, 'Expected minimum price to be 100.00');
    }

    public function testQueryBuilderRawAggregate()
    {
        $this->insertProductTable(); // Ensure data is inserted first

        // Using raw expressions for aggregation
        $totalProducts = DB::table('products')
            ->select(
                DB::raw('count(id) as total_products'),
                DB::raw('min(price) as min_price'),
                DB::raw('max(price) as max_price')
            )->first();
        $this->assertEquals(4, $totalProducts->total_products, 'Expected 4 total products');

        // Using raw expressions for average
        $averagePrice = DB::table('products')->select(DB::raw('AVG(price) as average_price'))->first();
        $this->assertEquals(250.00, $averagePrice->average_price, 'Expected average price to be 250.00');
    }

    public function testQueryBuilderRawAggregateGroup()
    {
        $this->insertProductTable(); // Ensure data is inserted first

        // Group by category and aggregate
        $groupedProducts = DB::table('products')
            ->select('category_id', DB::raw('count(*) as total_products'), DB::raw('sum(price) as total_price'))
            ->groupBy('category_id')
            ->get();

        $this->assertCount(4, $groupedProducts, 'Expected 4 grouped categories');
        Log::info('Grouped products: ' . json_encode($groupedProducts));

        // Check specific category aggregates
        $gadgetCategory = $groupedProducts->firstWhere('category_id', 'GADGET');
        $this->assertEquals(1, $gadgetCategory->total_products, 'Expected 1 product in GADGET category');
        $this->assertEquals(100.00, $gadgetCategory->total_price, 'Expected total price for GADGET to be 100.00');
    }

    public function testQueryBuilderRawAggregateHaving()
    {
        $this->insertProductTable(); // Ensure data is inserted first

        // Group by category and filter using having
        $filteredCategories = DB::table('products')
            ->select('category_id', DB::raw('count(*) as total_products'), DB::raw('sum(price) as total_price'))
            ->groupBy('category_id')
            ->having('total_products', '>', 1)
            ->get();

        $this->assertCount(0, $filteredCategories, 'Expected no categories with more than 1 product');
        Log::info('Filtered categories: ' . json_encode($filteredCategories));
    }

    public function testQueryBuilderLocking()
    {
        $this->insertProductTable(); // Ensure data is inserted first

        // Locking rows for update is useful in scenarios where you want to prevent other transactions from modifying the data until the current transaction is complete
        // This is particularly useful in scenarios where you want to ensure data integrity during concurrent transactions
        DB::beginTransaction();
        $product = DB::table('products')->where('id', 'P001')->lockForUpdate()->first();
        $this->assertNotNull($product, 'Expected product P001 to be locked for update');
        Log::info('Locked product: ' . json_encode($product));

        // Simulate some processing
        sleep(1);

        // Commit the transaction
        DB::commit();
    }

    public function testQueryBuilderPagination()
    {
        $this->insertProductTable(); // Ensure data is inserted first

        // Paginate products with 2 items per page
        $paginatedProducts = DB::table('products')->paginate(perPage: 2);
        $this->assertCount(2, $paginatedProducts->items(), 'Expected 2 products per page');
        Log::info('Paginated products: ' . json_encode($paginatedProducts->items()));

        $this->assertEquals(4, $paginatedProducts->total(), 'Expected total products to be 4');
        $this->assertEquals(2, $paginatedProducts->perPage(), 'Expected 2 products per page');
        $this->assertEquals(1, $paginatedProducts->currentPage(), 'Expected current page to be 1');
        $this->assertEquals(2, $paginatedProducts->lastPage(), 'Expected last page to be 2');
        $this->assertEquals(2, $paginatedProducts->count(), 'Expected 2 products on the current page');

        // Check pagination links
        $this->assertTrue($paginatedProducts->hasMorePages(), 'Expected more pages of products');
    }

    public function testQueryBuilderPaginationLoop()
    {
        $this->insertProductTable(); // Ensure data is inserted first
        $page = 1;

        while (true) {
            // Paginate products with 2 items per page
            $paginatedProducts = DB::table('products')->paginate(perPage: 2, page: $page);
            if (!$paginatedProducts->hasMorePages()) {
                break;
            } else {
                $page++;

                $collection = $paginatedProducts->items();
                foreach ($collection as $product) {
                    Log::info('Product: ' . json_encode($product));
                };
                $this->assertEquals(2, $paginatedProducts->perPage(), 'Expected 2 products per page');
            }
        }
    }
}
