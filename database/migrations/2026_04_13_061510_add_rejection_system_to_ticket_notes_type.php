<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ticket_notes MODIFY COLUMN type ENUM('note','route_event','status_change','rejection','system') NOT NULL DEFAULT 'note'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ticket_notes MODIFY COLUMN type ENUM('note','route_event','status_change') NOT NULL DEFAULT 'note'");
    }
};
