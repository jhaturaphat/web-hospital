<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['news_category_id', 'title', 'body', 'is_published'])]
class NewsPost extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(NewsAttachment::class);
    }
}