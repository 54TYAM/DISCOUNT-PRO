<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::connection('mongodb')->create('promotions', function (Blueprint $collection) {
            $collection->index('is_active');
            $collection->index('type');
            $collection->index('discount_id');
            $collection->index('start_at');
            $collection->index('end_at');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('promotions');
    }
};
