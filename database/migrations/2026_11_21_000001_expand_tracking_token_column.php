<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Retained as a no-op so a fresh install never creates or stores tracking tokens.
    }

    public function down(): void
    {
        // No persisted token column exists in the stateless-token design.
    }
};
