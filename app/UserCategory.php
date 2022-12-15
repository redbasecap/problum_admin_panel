<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCategory extends Model
{
    protected $table = "user_category";
    protected $guarded = ["id"];

    public function names()
    {
        return $this->hasMany(UserCategoryLanguage::class, 'object_id')->where('type', 'category');
    }

    public function getUser()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function getImageAttribute($val)
    {
        return get_asset('uploads/'.$val, false, get_constants('default.user_image'));
    }
    

    
}
