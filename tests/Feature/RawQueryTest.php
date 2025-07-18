<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\QueryExecuted;

class RawQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from categories'); // Clear categories table before each test
    }

    public function testCrud()
    {
        // Example of a raw query test
        $result = DB::select('SELECT * FROM categories WHERE id = ?', [1]);
        $this->assertEmpty($result, 'Expected no results for non-existing category');

        // Insert a new category using named bindings
        DB::insert('INSERT INTO categories (id, name, description, created_at, updated_at) VALUES (:id, :name, :description, :created_at, :updated_at)', [
            'id' => "GADGET",
            'name' => 'Gadget',
            'description' => 'Gadget Category',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $results =  DB::select('SELECT * FROM categories WHERE id = ?', ["GADGET"]);

        // Verify the insertion
        $this->assertNotEmpty($results, 'Expected to find the inserted category');
        $this->assertEquals('Gadget', $results[0]->name);
        $this->assertEquals('Gadget Category', $results[0]->description);
        $this->assertNotNull($results[0]->created_at);
    }


    // public function testRawQueryExecution(): void
    // {
    //     // Example of a raw query execution test
    //     $result = DB::select('SELECT * FROM users WHERE id = ?', [1]);

    //     $this->assertNotEmpty($result);
    //     $this->assertEquals(1, $result[0]->id);
    // }
}
