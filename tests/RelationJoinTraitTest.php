<?php

namespace Pion\Support\Eloquent\Tests;

class RelationJoinTraitTest extends TestCase
{
    public function test_model_join_hydrates_related_belongs_to_model_and_applies_relation_where(): void
    {
        $user = TestUser::query()
            ->select('users.*')
            ->modelJoin('profile')
            ->where('users.id', 1)
            ->firstOrFail();

        self::assertTrue($user->relationLoaded('profile'));
        self::assertSame('alpha', $user->profile->nickname);
    }

    public function test_model_join_keeps_null_relation_when_custom_where_filters_row_out(): void
    {
        $user = TestUser::query()
            ->select('users.*')
            ->modelJoin('profile')
            ->where('users.id', 2)
            ->firstOrFail();

        self::assertTrue($user->relationLoaded('profile'));
        self::assertNull($user->profile);
    }
}
