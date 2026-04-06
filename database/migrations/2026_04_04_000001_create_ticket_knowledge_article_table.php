<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_knowledge_article', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('knowledge_article_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ticket_id', 'knowledge_article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_knowledge_article');
    }
};
