<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostImages extends Model
{
  protected $table = "post_problem_images";
  protected $guarded = ['id'];


  public function getImageUrlAttribute($val){
    return get_asset($val, false, get_constants('default.user_image'));
  }
}
