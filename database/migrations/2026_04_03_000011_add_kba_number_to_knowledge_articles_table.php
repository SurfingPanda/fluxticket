<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->string('kba_number')->nullable()->unique()->after('id');
        });

        // Back-fill existing rows
        foreach (\App\Models\KnowledgeArticle::all() as $article) {
            $article->update([
                'kba_number' => '#KBA-' . str_pad($article->id, 4, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->dropColumn('kba_number');
        });
    }
};
