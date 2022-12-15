<?php

namespace App;
use App\PostImages;

use Illuminate\Database\Eloquent\Model;
use App\PostMeToo;

class PostProblem extends Model
{
    protected $table = "post_problem";
    protected $guarded = ["id"];

    public function getUser(){
        return $this->belongsTo(User::class,'user_id','id');
    }
    public function getUserTrace(){
        return $this->belongsTo(User::class,'user_id','id')->withTrashed();
    }
    
    public function getImages(){
        return $this->hasMany(PostImages::class,'post_id','id');
    }


    public function getcountMetoo(){
        return $this->hasMany(PostMeToo::class,'post_id','id');
    }

    public function getCommentCount(){
        return $this->hasMany(PostComment::class,'post_id','id');
    }
    public function getSolutionCount(){
        return $this->hasMany(PostSolution::class,'post_id','id')->where('status','Approved');
    }

    public function languagePost()
    {
        return $this->hasMany(PostProblumLanguage::class, 'object_id');
    }
}

    
