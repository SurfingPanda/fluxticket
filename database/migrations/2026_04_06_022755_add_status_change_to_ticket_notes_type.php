<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE ticket_notes MODIFY COLUMN type ENUM('note','route_event','status_change') NOT NULL DEFAULT 'note'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE ticket_notes ALTER COLUMN type TYPE VARCHAR(50)");
        }
        // sqlite: no change needed — column already accepts any text value
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE ticket_notes MODIFY COLUMN type ENUM('note','route_event') NOT NULL DEFAULT 'note'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE ticket_notes ALTER COLUMN type TYPE VARCHAR(50)");
        }
    }
};
