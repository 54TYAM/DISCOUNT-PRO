<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::connection('mongodb')->create('analytics_snapshots', function (Blueprint $collection) {
            $collection->index('discount_id');
            $collection->index('date');
            $collection->index(['discount_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('analytics_snapshots');
    }
};
