<?php

namespace App\Http\Controllers\Admin;

use App\Exports\postExport;
use App\Http\Controllers\Controller;
use App\PostProblem;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function PostExport($id,Request $request){
        
        // $postdata = PostProblem::where('id', 2)->first();
        // return view('exports.postexport', [
        //     'postdata' => $postdata
        //  ]);

        return Excel::download(new postExport($id), 'postExport.xlsx');
    }

}
