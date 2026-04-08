<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('requester')->nullable()->after('user_id');
            $table->unsignedBigInteger('requester_id')->nullable()->after('requester');
            $table->string('requester_dept')->nullable()->after('requester_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['requester', 'requester_id', 'requester_dept']);
        });
    }
};
