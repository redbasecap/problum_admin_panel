<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\PostSolution;

class Notification extends Model
{
    protected $guarded = [];
    protected $table = "notification";

    public static function Get_Count()
    {
        if (Auth::check()) {
            $user_data = Auth::user();
            return Notification::where(['country_id' => $user_data->country, 'user_id' => $user_data->id])->count();
        } else {
            return 0;
        }
    }


    public function getObjectIdAttribute($val)
    {
        return ($val) ? ((string)$val) : "0";
    }

    public function getSolution()
    {
        return $this->hasOne(PostSolution::class,'id','main_object_id');
    }

}
