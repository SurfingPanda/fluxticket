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
            $table->string('resolution_image')->nullable()->after('resolution');
            $table->string('department')->nullable()->after('assignee');
            $table->string('routed_to')->nullable()->after('department');
            $table->text('routing_note')->nullable()->after('routed_to');
            $table->timestamp('routed_at')->nullable()->after('routing_note');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['resolution_image','department','routed_to','routing_note','routed_at']);
        });
    }
};
