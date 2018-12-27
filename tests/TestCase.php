<?php

namespace Zbiller\Duplicate\Tests;

use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Zbiller\Duplicate\Tests\Models\Post;
use Zbiller\Duplicate\Tests\Models\Review;
use Zbiller\Duplicate\Tests\Models\Tag;

abstract class TestCase extends Orchestra
{
    /**
     * @var Post
     */
    protected $post;

    /**
     * @var Review
     */
    protected $review;

    /**
     * @var array
     */
    protected $comments = [];

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Set up the database and migrate the necessary tables.
     *
     * @param  $app
     */
    protected function setUpDatabase(Application $app)
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    /**
     * @param Post|null $model
     */
    protected function makeModels(Post $model = null)
    {
        $model = $model && $model instanceof Post ? $model : new Post;

        // create a post
        $this->post = $model->create([
            'title' => 'Post test title',
            'intro' => 'Post test intro',
            'content' => 'Post test content',
            'views' => 100,
            'approved' => true,
            'published_at' => Carbon::now(),
        ]);

        // create a post review
        $this->review = $this->post->review()->create([
            'name' => 'Review test name',
            'content' => 'Review test content',
            'rating' => 5,
        ]);

        // create 3 identical post comments
        for ($i = 1; $i <= 3; $i++) {
            $this->comments[] = $this->post->comments()->create([
                'subject' => 'Comment test subject',
                'comment' => 'Comment test comment',
                'votes' => 10,
            ]);
        }

        // create 3 tags
        for ($i = 1; $i <= 3; $i++) {
            $this->tags[] = Tag::create([
                'name' => 'Tag test name ' . $i
            ]);
        }

        // attach tags to post
        foreach ($this->tags as $tag) {
            $this->post->tags()->attach($tag->id);
        }
    }
}
