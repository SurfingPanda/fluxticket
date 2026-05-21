<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reference users by foreign key instead of by name string.
     *
     * The tickets table historically stored people as plain name strings
     * (assignee, resolved_by, rejected_by, routed_to). That breaks when a
     * user is renamed and is ambiguous when two users share a name. This
     * adds proper nullable FK columns and backfills them from the names.
     * The legacy name columns are kept as a denormalised display cache.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('assignee_id')->nullable()->after('assignee')
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by_id')->nullable()->after('resolved_by')
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by_id')->nullable()->after('rejected_by')
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('routed_to_id')->nullable()->after('routed_to')
                  ->constrained('users')->nullOnDelete();
            $table->index(['assignee_id', 'status']);
        });

        // Backfill the new id columns by matching the existing name strings.
        // Note: if two users share a name the match is arbitrary — acceptable
        // for a one-time backfill of legacy data.
        DB::statement('UPDATE tickets t JOIN users u ON u.name = t.assignee    SET t.assignee_id    = u.id WHERE t.assignee    IS NOT NULL');
        DB::statement('UPDATE tickets t JOIN users u ON u.name = t.resolved_by SET t.resolved_by_id = u.id WHERE t.resolved_by IS NOT NULL');
        DB::statement('UPDATE tickets t JOIN users u ON u.name = t.rejected_by SET t.rejected_by_id = u.id WHERE t.rejected_by IS NOT NULL');
        DB::statement('UPDATE tickets t JOIN users u ON u.name = t.routed_to   SET t.routed_to_id   = u.id WHERE t.routed_to   IS NOT NULL');

        // Backfill requester_id where it is still empty but the name matches.
        DB::statement('UPDATE tickets t JOIN users u ON u.name = t.requester   SET t.requester_id   = u.id WHERE t.requester_id IS NULL AND t.requester IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['assignee_id', 'status']);
            $table->dropConstrainedForeignId('assignee_id');
            $table->dropConstrainedForeignId('resolved_by_id');
            $table->dropConstrainedForeignId('rejected_by_id');
            $table->dropConstrainedForeignId('routed_to_id');
        });
    }
};
