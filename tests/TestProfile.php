<?php

namespace Pion\Support\Eloquent\Tests;

use Illuminate\Database\Eloquent\Model;

class TestProfile extends Model
{
    public $timestamps = false;

    protected $table = 'profiles';

    protected $guarded = [];
}
