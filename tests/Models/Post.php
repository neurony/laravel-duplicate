<?php

namespace Zbiller\Duplicate\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Zbiller\Duplicate\Options\DuplicateOptions;
use Zbiller\Duplicate\Traits\HasDuplicates;

class Post extends Model
{
    use HasDuplicates;

    /**
     * The database table.
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'intro',
        'content',
        'views',
        'approved',
        'published_at',
    ];

    /**
     * A post has one review.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function review()
    {
        return $this->hasOne(Review::class, 'post_id');
    }

    /**
     * A post has many comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    /**
     * A post has and belongs to many tags.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    /**
     * Get the options for the HasDuplicates trait.
     *
     * @return DuplicateOptions
     */
    public function getDuplicateOptions(): DuplicateOptions
    {
        return DuplicateOptions::instance();
    }
}
