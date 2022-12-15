<?php

namespace App\Http\Controllers\Api\V1;

use App\Category;
use App\DeviceToken;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Controllers\Controller;
use App\Language;
use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CategoryController extends ResponseController
{
    public function get_categories_listing(Request $request)
    {

        $language = App::getLocale();
        $search = $request->search ?? '';

        $token = get_header_auth_token();
        $notificationCount = 0;
        if ($token) {
            $is_login = DeviceToken::where('token', $token)->with('user')->has('user')->first();
           
            if ($is_login) {
                $user_data = $is_login->user;
                if ($user_data->status == "active") {
                    $user_data = $user_data;
                    $notificationCount =  Notification::where('user_id', $user_data->id)->where('is_read',0)->orderBy('id', 'desc')->count();
                } 
            }
        }
       
        
        $limit = isset($request->limit) ? $request->limit : 10;
        $offset = isset($request->offset) ? $request->offset : 0;
        $categoryId = isset($request->category_id) ? $request->category_id : 0;

         $all_categorys = Category::select('categories.id', 'categories.image', 'language_texts.text')->where('deleted_at',null)

        

        ->Join('language_texts', function ($join) use ($language) {

            $join->on('categories.id', '=', 'language_texts.object_id')->where('language_unique_name', $language);
           
            

            
        });

        if (!empty($search)) {
            $all_categorys = $all_categorys->where('language_texts.text','like','%'.$search.'%');
            // $postProblemData->where(function ($query) use ($search) {
            //     $query->whereHas('language_texts', function ($name) use ($search) {
            //         $name->where('title', 'like', "%$search%");
            //     });

                
            // });
        }

        $all_categorys = $all_categorys->orderBy('id', 'DESC')->get();


        $alldata = [];
        if(count($all_categorys) > 0){

            foreach($all_categorys as $category){
                $detail = [];
                $detail['id'] = $category->id;
                $detail['image'] = $category->image;
                $detail['text'] = $category->text;
                $alldata[] =  $detail;
            }
        }

        


        $this->sendResponse(200, __('api.succ'),$alldata, ['notification' => $notificationCount]);
    }
    public function get_language(Request $request)
    {

        $storeId = $request->store_id ?? 0;
        $categoryId = $request->category_id ?? 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $offset = isset($request->offset) ? $request->offset : 0;
        $categoryId = isset($request->category_id) ? $request->category_id : 0;

        $allLanguages = Language::select('*')->orderBy('id', 'DESC')->limit($limit)->offset($offset)->get();

        $alldata = [];
        if(count($allLanguages) > 0){

            foreach($allLanguages as $language){
                $detail = [];
                $detail['id'] = $language->id;
                $detail['name'] = $language->name;
                $detail['unique_name'] = $language->unique_name;
                $alldata[] =  $detail;
            }
        }


        $this->sendResponse(200, __('api.succ'), $alldata);
    }

    
}
