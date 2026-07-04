<?php

namespace Pion\Support\Eloquent\Tests;

use Illuminate\Database\Eloquent\Model;
use Pion\Support\Eloquent\Traits\RelationCountTrait;
use Pion\Support\Eloquent\Traits\RelationJoinTrait;

class TestUser extends Model
{
    use RelationCountTrait;
    use RelationJoinTrait;

    public $timestamps = false;

    protected $table = 'users';

    protected $guarded = [];

    public function profile()
    {
        return $this->hasOne(TestProfile::class, 'user_id', 'id')->where('profiles.active', 1);
    }

    public function posts()
    {
        return $this->hasMany(TestPost::class, 'user_id', 'id');
    }
}
