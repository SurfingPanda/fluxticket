<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('status');
            $table->index('assignee');
            $table->index('department');
            $table->index('priority');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['assignee', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::table('ticket_notes', function (Blueprint $table) {
            $table->index('ticket_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::table('ticket_notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['assignee']);
            $table->dropIndex(['department']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['assignee', 'status']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('ticket_notes', function (Blueprint $table) {
            $table->dropIndex(['ticket_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('ticket_notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'read_at']);
        });
    }
};
