<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from categories'); // Clear transactions table before each test
    }

    public function testTransaction()
    {
        DB::transaction(function () {
            DB::insert('INSERT INTO categories (id, name, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?)', [
                'GADGET',
                'Gadget',
                'Gadget Category',
                now(),
                now()
            ]);

            DB::insert('INSERT INTO categories (id, name, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?)', [
                'FOOD',
                'Food',
                'Food Category',
                now(),
                now()
            ]);
        });

        $results = DB::select('SELECT * FROM categories');
        $this->assertCount(2, $results, 'Expected 2 categories after transaction commit');
    }

    public function testFailedTransaction()
    {
        try {
            DB::transaction(function () {
                DB::insert('INSERT INTO categories (id, name, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?)', [
                    'GADGET',
                    'Gadget',
                    'Gadget Category',
                    now(),
                    now()
                ]);

                // This will cause an error due to duplicate primary key
                DB::insert('INSERT INTO categories (id, name, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?)', [
                    'GADGET',
                    'Gadget',
                    'Gadget Category',
                    now(),
                    now()
                ]);
            });
        } catch (QueryException $e) {
            // Catch the exception to prevent test failure
        }

        $results = DB::select('SELECT * FROM categories');
        $this->assertCount(0, $results, 'Expected no categories after failed transaction');
    }

    public function testManualTransaction()
    {
        DB::beginTransaction();
        try {
            DB::insert('INSERT INTO categories (id, name, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?)', [
                'GADGET',
                'Gadget',
                'Gadget Category',
                now(),
                now()
            ]);

            DB::insert('INSERT INTO categories (id, name, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?)', [
                'FOOD',
                'Food',
                'Food Category',
                now(),
                now()
            ]);

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            // throw $e; // Re-throw the exception for the test to fail
        }

        $results = DB::select('SELECT * FROM categories');
        $this->assertCount(2, $results, 'Expected 2 categories after manual transaction commit');
    }

    public function testManualFailedTransaction()
    {
        DB::beginTransaction();
        try {
            DB::insert('INSERT INTO categories (id, name, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?)', [
                'GADGET',
                'Gadget',
                'Gadget Category',
                now(),
                now()
            ]);

            DB::insert('INSERT INTO categories (id, name, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?)', [
                'GADGET', // This will cause a duplicate primary key error
                'Gadget',
                'Gadget Category',
                now(),
                now()
            ]);

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            // throw $e; // Re-throw the exception for the test to fail
        }

        $results = DB::select('SELECT * FROM categories');
        $this->assertCount(0, $results, 'Expected no categories after failed manual transaction');
    }
}
