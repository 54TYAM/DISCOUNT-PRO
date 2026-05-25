<?php

use Illuminate\Database\Migrations\Migration;

// Roles are stored as a string field on the users collection — no separate collection needed.
return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
