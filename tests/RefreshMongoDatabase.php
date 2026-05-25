<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

/**
 * Drop every collection in the test database before each test.
 *
 * Laravel's standard RefreshDatabase trait wraps tests in transactions for
 * fast rollback. MongoDB requires a replica-set to support transactions;
 * a typical local dev setup (XAMPP, plain mongod) runs in standalone mode.
 *
 * This trait achieves the same "clean slate per test" guarantee by calling
 * the schema builder's dropAllCollections() — supported by mongodb/laravel-mongodb
 * on any topology — instead of issuing a transaction.
 *
 * Usage: replace `use RefreshDatabase;` with `use RefreshMongoDatabase;`
 */
trait RefreshMongoDatabase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::connection('mongodb')->getSchemaBuilder()->dropAllTables();
    }
}
