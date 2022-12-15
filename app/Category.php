<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    protected $table = 'categories';
    protected $guarded = ['id'];
    use SoftDeletes;

    public function getImageAttribute($val)
    {
        return get_asset($val);
    }

    public function parent()
    {
        return $this->belongsTo(Categorie::class, 'parent_id', 'id');
    }


    public function childrens()
    {
        return $this->hasMany(Categorie::class, 'parent_id', 'id');
    }

    public function names()
    {
        return $this->hasMany(LanguageText::class, 'object_id')->where('type', 'category');
    }

}
