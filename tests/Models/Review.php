<?php

namespace Zbiller\Duplicate\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /**
     * The database table.
     *
     * @var string
     */
    protected $table = 'post_review';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'content',
        'rating',
    ];

    /**
     * A review belongs to a post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
