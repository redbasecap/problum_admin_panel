<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostSolution extends Model
{
    protected $table = 'post_solution';
    protected $guarded = ['id'];

    public function getUser(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function languageSolution()
    {
        return $this->hasMany(PostCommentLanguage::class, 'object_id')->where('type','solution');
    }


}
