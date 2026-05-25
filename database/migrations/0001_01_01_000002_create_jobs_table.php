<?php

use Illuminate\Database\Migrations\Migration;

// Queue driver is set to 'sync' — no collection needed.
return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
