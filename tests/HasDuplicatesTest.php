<?php

namespace Neurony\Duplicate\Tests;

use Neurony\Duplicate\Options\DuplicateOptions;
use Neurony\Duplicate\Tests\Models\Comment;
use Neurony\Duplicate\Tests\Models\Post;
use Neurony\Duplicate\Tests\Models\Review;
use Neurony\Duplicate\Tests\Models\Tag;

class HasDuplicatesTest extends TestCase
{
    /** @test */
    public function it_duplicates_a_model_instance()
    {
        $this->makeModels();

        $model = $this->post->saveAsDuplicate();

        foreach ($this->post->getFillable() as $field) {
            $this->assertEquals($this->post->{$field}, $model->{$field});
        }

        $this->assertEquals(2, Post::count());
    }

    /** @test */
    public function it_can_save_unique_columns_when_duplicating_a_model_instance()
    {
        $model = new class extends Post {
            public function getDuplicateOptions(): DuplicateOptions
            {
                return parent::getDuplicateOptions()->uniqueColumns('title');
            }
        };

        $this->makeModels($model);

        for ($i = 1; $i <= 5; $i++) {
            $model = $this->post->saveAsDuplicate();

            $this->assertEquals($this->post->title.' ('.$i.')', $model->title);
        }
    }

    /** @test */
    public function it_can_exclude_columns_when_duplicating_a_model_instance()
    {
        $model = new class extends Post {
            public function getDuplicateOptions(): DuplicateOptions
            {
                return parent::getDuplicateOptions()->excludeColumns('views', 'approved', 'published_at');
            }
        };

        $this->makeModels($model);

        for ($i = 1; $i <= 5; $i++) {
            $model = $this->post->saveAsDuplicate();
            $model = $model->fresh();

            $this->assertEquals(0, $model->views);
            $this->assertEquals(0, $model->approved);
            $this->assertNull($model->published_at);
        }
    }

    /** @test */
    public function it_can_duplicate_one_to_one_relations_when_duplicating_a_model_instance()
    {
        $this->makeModels();

        $model = $this->post->saveAsDuplicate();

        foreach ($this->review->getFillable() as $field) {
            $this->assertEquals($this->review->{$field}, $model->review->{$field});
        }

        $this->assertEquals(2, Review::count());
    }

    /** @test */
    public function it_can_duplicate_one_to_many_relations_when_duplicating_a_model_instance()
    {
        $this->makeModels();

        $model = $this->post->saveAsDuplicate();

        foreach ($model->comments as $index => $comment) {
            foreach ($comment->getFillable() as $field) {
                $this->assertEquals($this->comments[$index]->{$field}, $comment->{$field});
            }
        }

        $this->assertEquals(6, Comment::count());
    }

    /** @test */
    public function it_can_duplicate_many_to_many_relations_when_duplicating_a_model_instance()
    {
        $this->makeModels();

        $this->assertEquals(3, Tag::count());

        $model = $this->post->saveAsDuplicate();

        foreach ($model->tags as $index => $tag) {
            $this->assertEquals($model->id, $tag->pivot->post_id);
            $this->assertEquals($this->tags[$index]->id, $tag->pivot->tag_id);
        }

        $this->assertEquals(3, Tag::count());
    }

    /** @test */
    public function it_can_save_unique_columns_when_duplicating_a_relation_of_the_model_instance()
    {
        $model = new class extends Post {
            public function getDuplicateOptions(): DuplicateOptions
            {
                return parent::getDuplicateOptions()->uniqueRelationColumns([
                    'review' => ['name'], 'comments' => ['subject'],
                ]);
            }
        };

        $this->makeModels($model);

        $count = 1;

        for ($i = 1; $i <= 5; $i++) {
            $model = $this->post->saveAsDuplicate();
            $model = $model->fresh();

            $this->assertEquals($this->post->review->name.' ('.$i.')', $model->review->name);

            foreach ($model->comments as $index => $comment) {
                $this->assertEquals($this->comments[$index]->subject.' ('.$count.')', $comment->subject);

                $count++;
            }
        }
    }

    /** @test */
    public function it_can_exclude_columns_when_duplicating_a_relation_of_the_model_instance()
    {
        $model = new class extends Post {
            public function getDuplicateOptions(): DuplicateOptions
            {
                return parent::getDuplicateOptions()->excludeRelationColumns([
                    'review' => ['content', 'rating'], 'comments' => ['comment', 'votes'],
                ]);
            }
        };

        $this->makeModels($model);

        for ($i = 1; $i <= 5; $i++) {
            $model = $this->post->saveAsDuplicate();
            $model = $model->fresh();

            $this->assertNull($model->review->content);
            $this->assertEquals(0, $model->review->rating);

            foreach ($model->comments as $index => $comment) {
                $this->assertNull($comment->comment);
                $this->assertEquals(0, $comment->votes);
            }
        }
    }

    /** @test */
    public function it_can_duplicate_only_the_targeted_model_instance_without_any_relations()
    {
        $model = new class extends Post {
            public function getDuplicateOptions(): DuplicateOptions
            {
                return parent::getDuplicateOptions()->disableDeepDuplication();
            }
        };

        $this->makeModels($model);

        $model = $this->post->saveAsDuplicate();

        $this->assertEquals(0, $model->review()->count());
        $this->assertEquals(0, $model->comments()->count());
        $this->assertEquals(0, $model->tags()->count());
    }
}
