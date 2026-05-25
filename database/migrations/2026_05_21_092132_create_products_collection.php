<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::connection('mongodb')->create('products', function (Blueprint $collection) {
            $collection->index('category');
            $collection->index('is_active');
            $collection->index('price');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('products');
    }
};
