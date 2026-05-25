<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::connection('mongodb')->create('discounts', function (Blueprint $collection) {
            $collection->unique('code');
            $collection->index('is_active');
            $collection->index('type');
            $collection->index('start_date');
            $collection->index('end_date');
            $collection->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('discounts');
    }
};
