<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::connection('mongodb')->create('discount_usages', function (Blueprint $collection) {
            $collection->index('discount_id');
            $collection->index('user_id');
            $collection->index('used_at');
            $collection->index(['discount_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('discount_usages');
    }
};
