<?php

namespace Pion\Support\Eloquent\Tests;

class RelationCountTraitTest extends TestCase
{
    public function testItCountsRelatedModels(): void
    {
        $user = TestUser::query()->findOrFail(1);

        $this->assertSame(3, $user->relationCount('posts_count', TestPost::class, 'user_id', 'id'));

        $index = 'published_posts_';
        $this->assertSame(2, $user->relationCountWithWhere($index, 'status', 'published', TestPost::class, 'user_id', 'id'));
        $this->assertSame('published_posts_status_published', $index);
    }
}
