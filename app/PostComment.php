<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    protected $table = "post_comment";
    protected $guarded = ['id'];

    public function getUser(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
