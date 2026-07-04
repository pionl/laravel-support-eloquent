<?php

namespace Pion\Support\Eloquent\Tests;

use Illuminate\Database\Eloquent\Model;

class TestPost extends Model
{
    public $timestamps = false;

    protected $table = 'posts';

    protected $guarded = [];
}
