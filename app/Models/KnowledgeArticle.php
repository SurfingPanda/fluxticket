<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeArticle extends Model
{
    protected $fillable = ['user_id', 'kba_number', 'title', 'category', 'content', 'tags', 'status'];

    public static function generateNumber(): string
    {
        $last = static::max('id') ?? 0;
        return '#KBA-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tickets()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_knowledge_article');
    }

    public function tagList(): array
    {
        if (!$this->tags) return [];
        return array_filter(array_map('trim', explode(',', $this->tags)));
    }
}
