<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebController;
use App\PostMeToo;
use App\PostProblem;
use App\PostProblumLanguage;
use App\PostSolution as AppPostSolution;
use Illuminate\Http\Request;
use PostSolution;

class PostController extends WebController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.post.index', [
            'title' => 'Problum',
            'breadcrumb' => breadcrumb([
                'Problem' => route('admin.post.index'),
            ]),
        ]);
    }



    public function solutionListing(Request $request)
    {
        $datatable_filter = datatable_filters();
        $offset = $datatable_filter['offset'];
        $search = $datatable_filter['search'];
        $postId = $request->post_id;
        $return_data = array(
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $main = AppPostSolution::select('*')->with('languageSolution')->where('post_id', $postId);
        $return_data['recordsTotal'] = $main->count();
        if (!empty($search)) {

            $main->where(function ($query) use ($search) {
                $query->whereHas('languageSolution', function ($name) use ($search) {
                    $name->where('text', 'like', "%$search%");
                });
                  $query->Orwhere('comment', 'like', '%' . $search . '%');
            });
        }
        if ($datatable_filter['sort'] == 'first_name') {
            $datatable_filter['sort'] = 'id';
        }

        $return_data['recordsFiltered'] = $main->count();

        $all_data = $main->orderBy($datatable_filter['sort'], $datatable_filter['order'])
            ->offset($offset)
            ->limit($datatable_filter['limit'])
            ->get();
        $exprot = '<i class="fas fa-file-export"></i>';
        $names = collect(get_all_languages())->pluck('unique_name')->toArray();
        if (!empty($all_data)) {
            foreach ($all_data as $key => $value) {
                $get_language_text = $value->languageSolution->where('object_id', $value->id);
                $param = [
                    'id' => $value->id,
                    'url' => [
                        //'status' => route('admin.user.status_update', $value->id),
                        //'edit' => route('admin.user.edit', $value->id),
                        // 'delete' => route('admin.post.destroy', $value->id),
                        // 'view' => route('admin.post.show', $value->id),
                    ],
                    //'checked' => ($value->status == 'active') ? 'checked' : ''
                ];
                $link = "<a target='_blanck' href=".$value->solution_link.">".$value->solution_link."</a>";


                $values = [
                    'first_name' => $value->getUser->first_name,
                    'last_name' => $value->getUser->last_name,
                    'email' => $value->getUser->email,
                    'status' => @$value->status,
                    //'comment' => $value->comment,
                    'solution_link' => $link,
                    
                ];
                foreach ($names as $key1 => $name) {
                    $have = $get_language_text->contains('language_unique_name', $name);
                    $values[$name] = $have ? $get_language_text->firstWhere('language_unique_name', $name)->text : "";
                }
                $return_data['data'][] = $values;
            }
        }
        return $return_data;
    }

    public function meTooListing(Request $request)
    {
        $datatable_filter = datatable_filters();
        $offset = $datatable_filter['offset'];
        $search = $datatable_filter['search'];
        $postId = $request->post_id;
        $return_data = array(
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $main = PostMeToo::select('*')->where('post_id', $postId);
        $return_data['recordsTotal'] = $main->count();
        if (!empty($search)) {

            $main->where(function ($query) use ($search) {
                $query->whereHas('getUser',function($query) use ($search){
                    $query->where('first_name','like','%'.$search.'%')
                    ->orwhere('last_name','like','%'.$search.'%');
                });
            });
        }
        if ($datatable_filter['sort'] == 'first_name') {
            $datatable_filter['sort'] = 'id';
        }

        $return_data['recordsFiltered'] = $main->count();

        $all_data = $main->orderBy($datatable_filter['sort'], $datatable_filter['order'])
            ->offset($offset)
            ->limit($datatable_filter['limit'])
            ->get();
        $exprot = '<i class="fas fa-file-export"></i>';
        if (!empty($all_data)) {
            foreach ($all_data as $key => $value) {
                $param = [
                    'id' => $value->id,
                    'url' => [
                        //'status' => route('admin.user.status_update', $value->id),
                        //'edit' => route('admin.user.edit', $value->id),
                        // 'delete' => route('admin.post.destroy', $value->id),
                        // 'view' => route('admin.post.show', $value->id),
                    ],
                    //'checked' => ($value->status == 'active') ? 'checked' : ''
                ];
                $return_data['data'][] = array(
                    'first_name' => $value->getUser->first_name,
                    'last_name' => $value->getUser->last_name,
                    'name' => $value->getUser->name,
                    'email' => $value->getUser->email,
                    // 'action' => $exprot.$this->generate_actions_buttons($param),
                );
            }
        }
        return $return_data;
    }

    public function listing()
    {
        $datatable_filter = datatable_filters();
        $offset = $datatable_filter['offset'];
        $search = $datatable_filter['search'];
        $return_data = array(
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        $main = PostProblem::select('*')->has('getUser')->with('languagePost');
        $return_data['recordsTotal'] = $main->count();
        if (!empty($search)) {

            $main->where(function ($query) use ($search) {
                $query->whereHas('languagePost', function ($name) use ($search) {
                    $name->where('title', 'like', "%$search%")
                    ->orwhere('description', 'like', "%$search%");
                });

                $query->Orwhere('hastag', 'like', '%' . $search . '%');
            });
        }
        $return_data['recordsFiltered'] = $main->count();
        $all_data = $main->orderBy($datatable_filter['sort'], $datatable_filter['order'])
            ->offset($offset)
            ->limit($datatable_filter['limit'])
            ->get();
           
        
        if (!empty($all_data)) {
           $names = collect(get_all_languages())->pluck('unique_name')->toArray();
             
            foreach ($all_data as $key => $value) {
                $get_language_text = $value->languagePost->where('object_id', $value->id);
              //  $get_language_text = PostProblumLanguage::where(['object_id' => $value->id])->get();
               // dd($get_language_text);

                $param = [
                    'id' => $value->id,
                    'url' => [
                        //'status' => route('admin.user.status_update', $value->id),
                        //'edit' => route('admin.user.edit', $value->id),
                        'delete' => route('admin.post.destroy', $value->id),
                        'view' => route('admin.post.show', $value->id),
                    ],
                    //'checked' => ($value->status == 'active') ? 'checked' : ''
                ];
                $exprot = '<a href="'.route('admin.PostExport',$value->id).'" style="text-decoration:none;color: inherit;"><i class="fas fa-file-export"></i></a>';
                $values = [
                    'id' => $offset + $key + 1,
                    'user_id' => @$value->getUser->username,
                    'hastag' => $value->hastag,
                    'action' => $exprot . $this->generate_actions_buttons($param),
                    
                ];
                foreach ($names as $key1 => $name) {
                    $have = $get_language_text->contains('language_unique_name', $name);
                    $values['title_'.$name] = $have ? $get_language_text->firstWhere('language_unique_name', $name)->title : "";
                    $values['description_'.$name] = $have ? $get_language_text->firstWhere('language_unique_name', $name)->description : "";
                }
               
                $return_data['data'][] = $values;
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = PostProblem::where('id', $id)->first();

        return view('admin.post.view', [
            'title' => 'Problum',
            'post' => $post,
            'breadcrumb' => breadcrumb([
                'Problem' => route('admin.post.index'),
            ]),
        ]);
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
        $data = PostProblem::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('Problem Deleted successfully');
        } else {
            error_session('Problem not found');
        }
        return redirect()->route('admin.post.index');
    }
}
