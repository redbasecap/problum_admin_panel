<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostMeToo extends Model
{
    protected $table = "post_me_too";
    protected $guarded = ['id'];


    public function getUser(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
