<?php

namespace App\Http\Controllers\Api\V1;

use App\Category;
use App\DeviceToken;
use App\HasTag;
use App\Http\Controllers\Api\ResponseController;
use App\LanguageText;
use App\Notification;
use App\PostComment;
use App\PostCommentLanguage;
use App\PostImages;
use App\PostMeToo as AppPostMeToo;
use App\PostProblem;
use App\PostProblumLanguage;
use App\PostSolution;
use App\UserCategory;
use App\UserCategoryLanguage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use stdClass;

class PostController extends ResponseController
{

    public function addPost(Request $request)
    {

        $user = $request->user();
        $valid = $this->directValidation([
            'category_id' => ['required'],
            'title.*' => ['required'],
            'description.*' => ['required'],
            'hastag' => ['required'],

            'problem_image.*' => ['image'],
        ]);

        $category = Category::where('id', $request->category_id)->first();
        if ($category) {
            $postData = [
                'user_id' => $user->id,
                'hastag' => $request->hastag,
                'category_id' => $request->category_id,
            ];

            $postProblem = PostProblem::create($postData);

            if ($postProblem) {
                foreach ($request->title as $key => $value) {
                    $description = ($request->description[$key]) ?? '';
                    PostProblumLanguage::create([
                        'language_unique_name' => $key,
                        'type' => 'post_problum',
                        'object_id' => $postProblem->id,
                        'title' => $value,
                        'description' => $description,
                    ]);
                }

                $hastagData = explode('#', $request->hastag);
                if (is_array($hastagData) && count($hastagData) > 0) {
                    foreach ($hastagData as $hastag) {
                        if (!empty($hastag)) {
                            $hastagDetail = [
                                'hastag' => trim($hastag),
                            ];

                            HasTag::updateOrCreate($hastagDetail, $hastagDetail);
                        }
                    }
                }

                if ($files = $request->file('problem_image')) {
                    $images = array();

                    foreach ($files as $file) {
                        $name = time() . $file->getClientOriginalName();
                        $imagename = $file->move(public_path('uploads'), $name);

                        //$file->file($name)->store('uploads/',config('constants.upload_type'));
                        $image['image_url'] = "uploads/" . $name;
                        $image['post_id'] = $postProblem->id;
                        $image['created_at'] = date('Y-m-d H:i:s');
                        $image['updated_at'] = date('Y-m-d H:i:s');
                        $images[] = $image;
                    }

                    PostImages::insert($images);
                }
                $this->sendResponse(200, __('api.succ_post_add'), $postProblem);
            }
        } else {
            $this->sendError(__('api.err_category_not_found'), false);
        }




        $this->sendError(__('api.err_something_went_wrong'), false);
    }

    public function editPost(Request $request)
    {
       // dd($request->all());

        $user = $request->user();
        $valid = $this->directValidation([
            'post_id' => ['required'],
            'category_id' => ['required'],
            'title.*' => ['required'],
            'description.*' => ['required'],
            'hastag' => ['required'],
        ]);

        $category = Category::where('id', $request->category_id)->first();
        if ($category) {

            $post = PostProblem::where('id', $request->post_id)->where('user_id', $user->id)->first();
            if ($post) {

                foreach ($request->title as $key => $value) {
                    $description = ($request->description[$key]) ?? '';
                    $where = [
                        'object_id' => $post->id,
                        'language_unique_name' => $key,
                    ];

                    PostProblumLanguage::updateOrcreate($where,[
                        'language_unique_name' => $key,
                         //'type' => 'post_problum',
                        'object_id' => $post->id,
                        'title' => $value,
                        'description' => $description,
                    ]);
                }



                $hastagData = explode('#', $request->hastag);

                if (is_array($hastagData) && count($hastagData) > 0) {
                    foreach ($hastagData as $hastag) {
                        if (!empty($hastag)) {
                            $hastagDetail = [
                                'hastag' => trim($hastag),
                            ];

                            HasTag::updateOrCreate($hastagDetail, $hastagDetail);
                        }
                    }
                }
                $postData = [
                    'category_id' => $request->category_id,
                    'hastag' => $request->hastag,
                    //'description' => $request->description,
                ];
                $postProblem = PostProblem::where('id', $request->post_id)->update($postData);

                $postProblem = $post->refresh();

                $this->sendResponse(200, __('api.succ_post_edit'), $postProblem);
            }
        } else {
            $this->sendError(__('api.err_category_not_found'), false);
        }
        $this->sendError(__('api.errr_no_post'), false);
    }

    public function commentList(Request $request)
    {
        $language = App::getLocale();
        $user_data = $request->user();
        $limit = $request->limit ?? 20;
        $offset = $request->offset ?? 0;
        $valid = $this->directValidation([
            'post_id' => ['required'],
        ]);
        $postId = $request->post_id;

        $postProblemData = PostComment::select('post_comment.*','post_comment_language.text as lang_comment',)->Join('post_comment_language', function ($join) use ($language) {
            $join->on('post_comment.id', '=', 'post_comment_language.object_id')->where('language_unique_name', $language)->where('type','comment');
        })->with('getUser')->where('post_id', $postId)->orderBy('id', 'desc')->limit($limit)->offset($offset)->get();

        $postProblemData = $postProblemData->map(function ($data) {
            $data->created_at_new = Carbon::parse($data->created_at)->diffForHumans();

            return $data;
        });

        $this->sendResponse(200, __('api.succ_comment_list'), $postProblemData);
    }

    public function hastagList(Request $request)
    {
        $user_data = $request->user();
        $limit = $request->limit ?? 20;
        $offset = $request->offset ?? 0;
        $valid = $this->directValidation([
            'hastag' => ['required'],
        ]);
        $hastag = $request->hastag;

        $postProblemData = HasTag::select('id', 'hastag')->where('hastag', 'like', "%" . $hastag . "%")->limit(10)->get();
        $postProblemData = $postProblemData->map(function ($postdata) {
            $postdata->hastag = '#' . $postdata->hastag;
            return $postdata;
        });

        $this->sendResponse(200, "Hastag listing successfully", $postProblemData);
    }

    public function solutionList(Request $request)
    {
        $language = App::getLocale();
        $user_data = $request->user();
        $limit = $request->limit ?? 20;
        $offset = $request->offset ?? 0;

        $valid = $this->directValidation([
            'post_id' => ['required'],
        ]);

        $postId = $request->post_id;

        $postProblemData = PostSolution::
        select('post_solution.*','post_comment_language.text as lang_comment',)->Join('post_comment_language', function ($join) use ($language) {
            $join->on('post_solution.id', '=', 'post_comment_language.object_id')->where('language_unique_name', $language)->where('type','solution');
        })->with('getUser')
        ->where('post_solution.status','Approved')
        ->where('post_solution.post_id', $postId)->orderBy('id', 'desc')->limit($limit)->offset($offset)->get();

        $postProblemData = $postProblemData->map(function ($data) {
            $data->created_at_new = Carbon::parse($data->created_at)->diffForHumans();

            return $data;
        });

        $this->sendResponse(200, "Solution listing successfully", $postProblemData);
    }

    public function myProblumList(Request $request)
    {

        $user_data = $request->user();
        $limit = $request->limit ?? 20;
        $offset = $request->offset ?? 0;
        $language = App::getLocale();
        $postProblemData = PostProblem::select('post_problem.id', 'post_problem.user_id', 'post_problem.created_at', 'post_problem.hastag', 'post_problum_language.title', 'post_problum_language.description')->where('deleted_at', null)
            ->Join('post_problum_language', function ($join) use ($language) {
                $join->on('post_problem.id', '=', 'post_problum_language.object_id')->where('language_unique_name', $language);
            })->
        with('getImages')->withCount(['getcountMetoo as mee_to_count' => function ($query) {
            $query->select(DB::raw('count(id)'));
        }])
            ->withCount(['getCommentCount as comment_count' => function ($query) use($language) {
                $query->select(DB::raw('count(id)'))->where('language_unique_name_main',$language);
            }])
            ->withCount(['getSolutionCount as solution_count' => function ($query) use($language) {
                $query->select(DB::raw('count(id)'))->where('language_unique_name_main',$language);
            }])

            ->where('user_id', $user_data->id)->orderBy('id', 'desc')->limit($limit)->offset($offset)->get();

        $postProblemData = $postProblemData->map(function ($data) {
            $data->created_at_new = Carbon::parse($data->created_at)->diffForHumans();

            return $data;
        });

        $this->sendResponse(200, "Post listing successfully", $postProblemData);
    }

    public function problumDetail(Request $request){
        $valid = $this->directValidation([
            'post_id' => ['required'],
        ]);
        $postId = $request->post_id;
        $postProblum = PostProblem::where('id',$postId)->first();
        if($postProblum){


        $postProblemData_en = PostProblem::select('post_problem.id', 'post_problem.user_id', 'post_problem.created_at', 'post_problem.hastag','post_problem.category_id', 'post_problum_language.title', 'post_problum_language.description')->where('deleted_at', null)
        ->Join('post_problum_language', function ($join)  {
            $join->on('post_problem.id', '=', 'post_problum_language.object_id')->where('language_unique_name', 'en');
        })->
        with('getImages')->withCount(['getcountMetoo as mee_to_count' => function ($query) {
            $query->select(DB::raw('count(id)'));
        }])->where('post_problem.id', $postId)->orderBy('id', 'desc')->first();


        $postProblemData_gn = PostProblem::select('post_problem.id', 'post_problem.user_id', 'post_problem.created_at', 'post_problem.hastag','post_problem.category_id', 'post_problum_language.title', 'post_problum_language.description')->where('deleted_at', null)
        ->Join('post_problum_language', function ($join)  {
            $join->on('post_problem.id', '=', 'post_problum_language.object_id')->where('language_unique_name', 'gn');
        })->
        with('getImages')->withCount(['getcountMetoo as mee_to_count' => function ($query) {
            $query->select(DB::raw('count(id)'));
        }])->where('post_problem.id', $postId)->orderBy('id', 'desc')->first();

       
        if($postProblemData_en){

            $postProblemData_en->created_at_new = Carbon::parse($postProblemData_en->created_at)->diffForHumans();
        }
        if($postProblemData_gn){

            $postProblemData_gn->created_at_new = Carbon::parse($postProblemData_gn->created_at)->diffForHumans();
        }
         

         $problumData['post_data_en'] = ($postProblemData_en) ? $postProblemData_en : new stdClass();
         $problumData['post_data_gn'] = ($postProblemData_gn) ? $postProblemData_gn : new stdClass();
         

         $this->sendResponse(200, __('api.succ_post_detail'), $problumData);
        
        }else{
            $this->sendError(__('api.err_something_went_wrong'), false);

        }

    }
    public function post_detail(Request $request)
    {

        $valid = $this->directValidation([
            'post_id' => ['required']
        ]);

        $search = $request->search ?? '';


        $user_data = $request->user();
        $token = get_header_auth_token();
        if ($token) {
            $is_login = DeviceToken::where('token', $token)->with('user')->has('user')->first();
           
            if ($is_login) {
                $user_data = $is_login->user;
                if ($user_data->status == "active") {
                    $user_data = $user_data;
                } 
            }
        }


        $language = App::getLocale();
        $post_id = $request->post_id ?? 0;

        $limit = $request->limit ?? 20;
        $offset = $request->offset ?? 0;
        $postProblemData = PostProblem::select('post_problem.id', 'post_problem.user_id', 'post_problem.created_at', 'post_problem.hastag', 'post_problum_language.title', 'post_problum_language.description')->where('deleted_at', null)
            ->Join('post_problum_language', function ($join) use ($language) {
                $join->on('post_problem.id', '=', 'post_problum_language.object_id')->where('language_unique_name', $language);
            })->has('getUser')->with(['getImages', 'getUser:id,username,profile_image'])

           
            ->withCount(['getcountMetoo as mee_to_count' => function ($query) {
                $query->select(DB::raw('count(id)'));
            }])
            ->withCount(['getCommentCount as comment_count' => function ($query) use($language) {
                $query->select(DB::raw('count(id)'))->where('language_unique_name_main',$language);
            }])
            ->withCount(['getSolutionCount as solution_count' => function ($query)  use($language){
                $query->select(DB::raw('count(id)'))->where('language_unique_name_main',$language);;
            }])->where('post_problem.id',$post_id);

           




           $postProblemData = $postProblemData->orderBy('id', 'desc')->first();

            if($postProblemData ){

                $postProblemData->created_at_new = Carbon::parse($postProblemData->created_at)->diffForHumans();
                $postProblemData->mee_too = checkMeToo(@$user_data->id, $postProblemData->id);
            }

        $this->sendResponse(200, __('api.succ_post_list'), $postProblemData);
    }


    public function problumList(Request $request)
    {

        $valid = $this->directValidation([
            'category_id' => ['required']
        ]);

        $search = $request->search ?? '';


        $user_data = $request->user();
        $token = get_header_auth_token();
        if ($token) {
            $is_login = DeviceToken::where('token', $token)->with('user')->has('user')->first();
           
            if ($is_login) {
                $user_data = $is_login->user;
                if ($user_data->status == "active") {
                    $user_data = $user_data;
                } 
            }
        }


        $language = App::getLocale();
        $categoryId = $request->category_id ?? 0;

        $limit = $request->limit ?? 20;
        $offset = $request->offset ?? 0;
        $postProblemData = PostProblem::select('post_problem.id', 'post_problem.user_id', 'post_problem.created_at', 'post_problem.hastag', 'post_problum_language.title', 'post_problum_language.description')->where('deleted_at', null)
            ->Join('post_problum_language', function ($join) use ($language) {
                $join->on('post_problem.id', '=', 'post_problum_language.object_id')->where('language_unique_name', $language);
            })->has('getUser')->with(['getImages', 'getUser:id,username,profile_image'])

           
            ->withCount(['getcountMetoo as mee_to_count' => function ($query) {
                $query->select(DB::raw('count(id)'));
            }])
            ->withCount(['getCommentCount as comment_count' => function ($query) use($language) {
                $query->select(DB::raw('count(id)'))->where('language_unique_name_main',$language);
            }])
            ->withCount(['getSolutionCount as solution_count' => function ($query)  use($language){
                $query->select(DB::raw('count(id)'))->where('language_unique_name_main',$language);;
            }])->where('post_problem.category_id',$categoryId);

            if (!empty($search)) {

                $postProblemData->where(function ($query) use ($search) {
                    $query->whereHas('languagePost', function ($name) use ($search) {
                        $name->where('title', 'like', "%$search%")
                        ->orwhere('description', 'like', "%$search%");
                    })->orwhereHas('getUser', function ($name) use ($search) {
                        $name->where('username', 'like', "%$search%");
                    });
    
                    $query->Orwhere('hastag', 'like', '%' . $search . '%');
                });
            }

            if ($user_data) {
                $postProblemData = $postProblemData->where('post_problem.user_id','!=',$user_data->id);
            }




           $postProblemData = $postProblemData->orderBy('id', 'desc')->limit($limit)->offset($offset)->get();

         $postProblemData = $postProblemData->map(function ($data) use ($user_data) {
            $data->created_at_new = Carbon::parse($data->created_at)->diffForHumans();
            $data->mee_too = checkMeToo(@$user_data->id, $data->id);

            return $data;
        });

        $this->sendResponse(200, __('api.succ_post_list'), $postProblemData);
    }

    public function notificationList(Request $request)
    {

        $user_data = $request->user();
        $limit = $request->limit ?? 20;
        $offset = $request->offset ?? 0;
        $language = App::getLocale();

        $postNotifications = Notification::where('user_id', $user_data->id)->where('language_unique_name_main',$language)->orderBy('id', 'desc')->limit($limit)->offset($offset)->get();

        Notification::where('user_id', $user_data->id)->update(['is_read' => 1]);
        $postNotifications = $postNotifications->map(function ($data) use ($language){
            $data->created_at_new = Carbon::parse($data->created_at)->diffForHumans();
            $data->solution = new stdClass;
            if ($data->push_type == 2) {
               // $solution  =  PostSolution::where('post_id',$data->object_id)->where('language_unique_name_main',$language)->first();
                
                $data->solution = $data->getSolution;
                //$data->solution = $solution;
                
            }

            return $data;
        });

        $this->sendResponse(200, __('api.succ_noti_list'), $postNotifications);
    }

    public function deletePost(Request $request)
    {
        $postId = $request->post_id;
        $user_data = $request->user();

        $valid = $this->directValidation([
            'post_id' => ['required'],
        ]);

        $myPost = PostProblem::where('id', $postId)->where('user_id', $user_data->id)->first();
        if ($myPost) {

            $images = $myPost->getImages;
            if (count($images) > 0) {
                foreach ($images as $image) {
                    un_link_file($image->image_url);
                    PostImages::where('id', $image->id)->delete();
                }
            }

            $meto = AppPostMeToo::where('post_id', $myPost->id)->delete();
            PostComment::where('post_id', $myPost->id)->delete();
            PostSolution::where('post_id', $myPost->id)->delete();
            PostProblem::where('id', $postId)->where('user_id', $user_data->id)->delete();

            $this->sendResponse(200, __('api.succ_post_delete'));
        } else {
            $this->sendError(__('api.err_something_went_wrong'), false);
        }
    }

    public function solutionStatusUpdate(Request $request)
    {
        $user_data = $request->user();
        $valid = $this->directValidation([
            'solution_id' => ['required'],
            'status' => ['required', 'in:accept,reject'],
        ]);
        $solution = PostSolution::where('id', $request->solution_id)->first();
        if ($solution) {
            $status = "Approved";
            if ($request->status == "reject") {
                $status = 'Cancelled';
            }
            $solution->status = $status;
            $solution->save();

            $this->sendResponse(200, __('api.succ_solution_status'));
        } else {
            $this->sendError(__('api.err_something_went_wrong'), false);
        }
    }

    public function addMeeToo(Request $request)
    {
        $user_data = $request->user();
        $valid = $this->directValidation([
            'post_id' => ['required'],
        ]);
        $metooData = [
            'user_id' => $user_data->id,
            'post_id' => $request->post_id,
            'mee_too' => 1,
        ];
        $metoo = AppPostMeToo::create($metooData);
        if ($metoo) {
            $post = PostProblem::where('id', $request->post_id)->first();

            if ($post) {
                $username = $user_data->name;
                send_push($post->user_id, [
                    'push_type' => 1,
                    'from_user_id' => $user_data->id,
                    'push_message' => $username . " Added mee too your post",
                    'push_title' => 'Mee Too',
                    'object_id' => $post->id,
                    'country_id' => 0,
                    'message' => 'Mee Too',
                    'body' => $username . " Added mee too your post",
                ], true, false);
            }

            $this->sendResponse(200, __('api.succ_add_mee_too'), $metoo);
        } else {

            $this->sendError(__('api.err_something_went_wrong'), false);
        }
    }

    public function postSolution(Request $request)
    {
        $language = App::getLocale();
        $user_data = $request->user();
        $valid = $this->directValidation([
            'post_id' => ['required'],
            'comment' => ['required'],
        ]);
        $post = PostProblem::where('id', $request->post_id)->first();
        if ($post) {
            $commendData = [
                'user_id' => $user_data->id,
                'post_id' => $request->post_id,
                'comment' => $request->comment,
                'solution_link' => $request->solution_link,
                'is_future_solution' => $request->is_future ?? 0,
                'language_unique_name_main' => $language,
            ];
            $postComment = PostSolution::create($commendData);
            if ($postComment) {

                PostCommentLanguage::create([
                    'language_unique_name' => $language,
                    'type' => 'solution',
                    'object_id' => $postComment->id,
                    'text' => $request->comment,
                ]);

                $username = $user_data->name;
                send_push($post->user_id, [
                    'push_type' => 2,
                    'from_user_id' => $user_data->id,
                    'push_message' => $username . " Added solution to your post",
                    'push_title' => 'Solution added',
                    'object_id' => $post->id,
                    'main_object_id' => $postComment->id,
                    'country_id' => 0,
                    'message' => 'Solution added',
                    'body' => $username . " Added solution to your post",
                ], true, false);

                $this->sendResponse(200, __('api.succ_add_solution'), $postComment);
            } else {

                $this->sendError(__('api.err_something_went_wrong'), false);
            }
        } else {
            $this->sendError("No post found", false);
        }
    }

    public function addComment(Request $request)
    {
        $language = App::getLocale();
        $user_data = $request->user();
        $valid = $this->directValidation([
            'post_id' => ['required'],
            'comment' => ['required'],
        ]);
        $commendData = [
            'user_id' => $user_data->id,
            'post_id' => $request->post_id,
            'comment' => $request->comment,
            'language_unique_name_main' => $language,
        ];

        $post = PostProblem::where('id',$request->post_id)->first();
        if($post){
            
            $postComment = PostComment::create($commendData);
                if ($postComment) {
                    
                     PostCommentLanguage::create([
                        'language_unique_name' => $language,
                        'type' => 'comment',
                        'object_id' => $postComment->id,
                        'text' => $request->comment,
                    ]);

                    send_push($post->user_id, [
                        'push_type' => 3,
                        'from_user_id' => $user_data->id,
                        'push_message' => $user_data->username . " Added comment to your post",
                        'push_title' => 'Comment added',
                        'object_id' => $post->id,
                        'country_id' => 0,
                        'message' => 'Comment added',
                        'body' => $user_data->username  . " Added comment to your post",
                    ], true, false);

                    $this->sendResponse(200, __('api.succ_comment_add'), $postComment);
                }
                else {

                    $this->sendError(__('api.err_something_went_wrong'), false);
                }

        }
        else {
            $this->sendError(__('api.err_post_not_found'), false);
        }
    }
    

    public function addUserSuggestedCategory(Request $request)
    {

        $user = $request->user();
        $valid = $this->directValidation([
            'title.*' => ['required'],
            'category_image' => ['required','image'],
        ]);

            $postCategory = [
                'user_id' => $user->id,
                'status' => 'pending',
            ];
             
            
            $file = $request->file('category_image');

            $name = time() . $file->getClientOriginalName();
            $imagename = $file->move(public_path('uploads'), $name);
            $postCategory['image'] = $name;

            $category =  UserCategory::create($postCategory);
            if ($category) {
                
                foreach ($request->title as $key => $value) {
                        UserCategoryLanguage::create([
                        'language_unique_name' => $key,
                        'type' => 'category',
                        'object_id' => $category->id,
                        'text' => $value,
                    ]);
                }

                $this->sendResponse(200, __('api.succ_category_add'), $category);
            }


        $this->sendError(__('api.err_something_went_wrong'), false);
    }
}
