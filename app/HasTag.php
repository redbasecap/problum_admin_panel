<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HasTag extends Model
{
    protected $table = "hastagdata";
    protected $guarded = ['id'];
}
