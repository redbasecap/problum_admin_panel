<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WebController;
use App\Language;
use App\LanguageText;
use App\UserCategory;
use App\UserCategoryLanguage;
use Category as GlobalCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends WebController
{
    
    public function userCategory()
    {
        $title = "Suggested Category";
        return view('admin.user-category.index', [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                $title => route('admin.category.user_categroy'),
            ]),
        ]);
    }

    public function userCategoryListing(Request $request)
    {
       
        $datatable_filter = datatable_filters();
        $offset = $datatable_filter['offset'];
        $search = $datatable_filter['search'];
        $return_data = array(
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        $main = UserCategory::has('names')->has('getUser')->with(['getUser']);
        $return_data['recordsTotal'] = $main->count('id');
        if (!empty($search)) {
            $main->whereHas('names', function ($name) use ($search) {
                $name->where('text', 'like', "%$search%");
            })->orWhereHas('getUser', function ($name) use ($search) {
                $name->where('name', 'like', "%$search%");
            });
        }
        $return_data['recordsFiltered'] = $main->count('id');
        $all_data = $main->orderBy($datatable_filter['sort'], $datatable_filter['order'])
            ->offset($offset)
            ->limit($datatable_filter['limit'])
            ->get();
        if (!empty($all_data)) {
            $return_keys = [
                'id',
                'icon',
            ];
            $names = collect(get_all_languages())->pluck('unique_name')->toArray();
            $return_keys = array_merge(array_merge($return_keys, $names), ['status', 'action']);
            foreach ($all_data as $key => $value) {
                $get_language_text = UserCategoryLanguage::where(['type' => 'category', 'object_id' => $value->id])->get();
                $param = [
                    'id' => $value->id,
                    'url' => [
                        //'status' => route('admin.category.status_update', $value->id),
                        //'edit' => route('admin.category.edit', $value->id),
                        'delete' => route('admin.category.user_category_destroy', $value->id)
                    ],
                    'checked' => ($value->status == 'active') ? 'checked' : ''
                ];
                $button = '<div class="btn-group" role="group">
                <button id="btnGroupVerticalDrop1" type="button" class="btn btn-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    ' . $value->status . ' <i class="mdi mdi-chevron-down"></i>
                </button>
                    <div class="dropdown-menu" aria-labelledby="btnGroupVerticalDrop1" style="">
                        <a class="dropdown-item action_btn" data-id="' . $value->id . '" data-status="pending" href="javascript:;">Penddng</a>
                        <a class="dropdown-item action_btn" data-id="' . $value->id . '"  data-status="approve" href="javascript:;">Approve</a>
                        <a class="dropdown-item action_btn" data-id="' . $value->id . '"  data-status="decline" href="javascript:;">Decline</a>
                    </div>
                </div>';
                $values = [
                    'id' => $offset + $key + 1,
                    'user_id' => $value->getUser->name,
                    'status' => $button,
                    'image' => "<img class='bg_green_new' src='" . $value['image'] . "' height='50' width='50'>",
                ];
                foreach ($names as $key1 => $name) {
                    $have = $get_language_text->contains('language_unique_name', $name);
                    $values[$name] = $have ? $get_language_text->firstWhere('language_unique_name', $name)->text : "";
                }
                $return_data['data'][] = array_merge($values, [
                    //'status' => $this->generate_switch($param),
                    'action' => $this->generate_actions_buttons($param),
                ]);
            }
        }
        return $return_data;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = "Category";
        return view('admin.category.index', [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                $title => route('admin.category.index'),
            ]),
        ]);
    }
    public function categoryStatusUpdate(Request $request)
    {
        $status = $request->status;
        
       
        $user = UserCategory::where('id', $request->id)->first();
        if ($user) {
            if($status == 'approve'){
                $category = [
                    'image' => 'uploads/'.$user->getRawOriginal('image'),
                    'parent_id' => '0',
                ];

                $maincategory = Category::Create($category);
                if($maincategory){

                    send_push($user->user_id, [
                        'push_type' => 4,
                        'from_user_id' => Auth::user()->id,
                        'push_message' => "Wow, your category has been approved by Admin team",
                        'push_title' => 'Category Approved',
                        'object_id' => $maincategory->id,
                        'country_id' => 0,
                        'message' => 'Category Approved',
                        'body' => "Wow, your category has been approved by Admin team",
                    ], true, false,1);

                    $categoryTitle = $user->names;
                   
                    if(count($categoryTitle) > 0){
                        foreach($categoryTitle as $suggestCategory){

                            $categoryText = [
                                'language_unique_name' => $suggestCategory->language_unique_name,
                                'type' => 'category',
                                'text' => $suggestCategory->text,
                                'object_id' => $maincategory->id,
                                
                            ];
                            LanguageText::create($categoryText);
                            $suggestCategory->delete();

                            if(count($categoryTitle) == 1){

                                $otherLanguages = Language::where('unique_name','!=',$suggestCategory->language_unique_name)->get();
                                    if(count($otherLanguages) > 0){
                                        foreach($otherLanguages as $otherLang){
                                           
                                            $categoryText = [
                                                'language_unique_name' => $otherLang->unique_name,
                                                'type' => 'category',
                                                'text' => $suggestCategory->text,
                                                'object_id' => $maincategory->id,
                                                
                                            ];
                                            LanguageText::create($categoryText);
                                        }
                                    }

                            }
                        }


                    }
                    $user->delete();

                }

                $data = ['status' => true, 'msg' => "Category Updated Successfully."];
                return Response()->json($data);

            }
            $status = $request->status;
            $user->status = $status;
            $user->save();

            $data = ['status' => true, 'msg' => "Category Updated Successfully."];
            return Response()->json($data);
        }
        $data = ['status' => false, 'msg' => "No category found"];
        return Response()->json($data);
    }

    public function listing(Request $request)
    {
        $datatable_filter = datatable_filters();
        $offset = $datatable_filter['offset'];
        $search = $datatable_filter['search'];
        $return_data = array(
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        $main = Category::where('parent_id', 0)->has('names');
        $return_data['recordsTotal'] = $main->count('id');
        if (!empty($search)) {
            $main->whereHas('names', function ($name) use ($search) {
                $name->where('text', 'like', "%$search%");
            });
        }
        $return_data['recordsFiltered'] = $main->count('id');
        $all_data = $main->orderBy($datatable_filter['sort'], $datatable_filter['order'])
            ->offset($offset)
            ->limit($datatable_filter['limit'])
            ->get();
        if (!empty($all_data)) {
            $return_keys = [
                'id',
                'icon',
            ];
            $names = collect(get_all_languages())->pluck('unique_name')->toArray();
            $return_keys = array_merge(array_merge($return_keys, $names), ['status', 'action']);
            foreach ($all_data as $key => $value) {
                $get_language_text = LanguageText::where(['type' => 'category', 'object_id' => $value->id])->get();
                $param = [
                    'id' => $value->id,
                    'url' => [
                        //'status' => route('admin.category.status_update', $value->id),
                        //'edit' => route('admin.category.edit', $value->id),
                        'delete' => route('admin.category.destroy', $value->id)
                    ],
                    'checked' => ($value->status == 'active') ? 'checked' : ''
                ];
                $values = [
                    'id' => $offset + $key + 1,
                    'image' => "<img class='bg_green_new' src='" . $value['image'] . "' height='50' width='50'>",
                ];
                foreach ($names as $key1 => $name) {
                    $have = $get_language_text->contains('language_unique_name', $name);
                    $values[$name] = $have ? $get_language_text->firstWhere('language_unique_name', $name)->text : "";
                }
                $return_data['data'][] = array_merge($values, [
                    //'status' => $this->generate_switch($param),
                    'action' => $this->generate_actions_buttons($param),
                ]);
            }
        }
        return $return_data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Add Category';
        return view('admin.category.create', [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                __('admin.category_listing') => route('admin.category.index'),
                'Add' => route('admin.category.create'),
            ]),
        ]);
    
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $request->validate([
            'name' => ['required', 'array'],
            'file_url' => ['required', 'file', 'image'],
        ]);

        $data['parent_id'] = 0;
        $data['image'] = $this->upload_file('file_url', 'category');
       $category =  Category::create($data);
       if ($category) {
           
        foreach ($request->name as $key => $value) {
                LanguageText::create([
                'language_unique_name' => $key,
                'type' => 'category',
                'object_id' => $category->id,
                'text' => $value,
            ]);
        }
        $add = true;
    }


        success_session('category Added Successful');
        return redirect()->route('admin.category.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if($category){
            un_link_file($category->image);
            Category::where('id', $id)->delete();
            success_session('Category Deleted successfully');
        }else{
            success_session('Category Deleted successfully');
            
        }
        return redirect()->route('admin.category.index');
        
    }
    public function user_category_destroy($id)
    {
        $category = UserCategory::find($id);
        if($category){
            un_link_file($category->image);
            UserCategory::where('id', $id)->delete();
            UserCategoryLanguage::where('object_id',$category->id)->delete();
            success_session('Suggesion category deleted successfully');
        }else{
            success_session('Suggestion category deleted successfully');
            
        }
        return redirect()->route('admin.category.user_categroy');
        
    }

    
}
