<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stop deleting a user from destroying the records they authored.
     *
     * tickets.user_id, ticket_notes.user_id and knowledge_articles.user_id
     * were ON DELETE CASCADE — so removing a user (e.g. a departed employee
     * via the Roles page) silently deleted every ticket they raised, their
     * notes, and their KB articles. They become ON DELETE SET NULL so the
     * records survive, authored by an "unknown" user.
     *
     * ticket_notifications.user_id is intentionally left CASCADE — a
     * notification only has meaning for its recipient.
     */
    private array $tables = ['tickets', 'ticket_notes', 'knowledge_articles'];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->unsignedBigInteger('user_id')->nullable()->change();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                // The column is left nullable on rollback: reverting it to
                // NOT NULL would fail if orphaned rows already exist.
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }
};
