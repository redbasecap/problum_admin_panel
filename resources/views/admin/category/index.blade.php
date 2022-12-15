@extends('layouts.master')
@section('title') @lang('translation.Data_Tables') @endsection
@section('css')

<!-- DataTables -->
<link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
@endsection
@section('content')

@include('components.breadcum')

<div class="row">
    <div class="col-12">
        {!! success_error_view_generator() !!}

    </div>
    <div class="card">

        <div class="card-body">
        <div class="wd-sl-tableup justify-content-end">
                    <div class="wd-sl-btngrp">
                        <a  href="{{route('admin.category.create')}}" class="btn btn-primary waves-effect waves-light"> Add Category</a>
                    </div>
                </div>
            <div class="table-responsive ">
                <table id="listResults" class="table dt-responsive mb-4  nowrap w-100 mb-">
                    <thead>
                        <tr>
                        <th>No</th>
                            <th>Icon</th>
                                @foreach(get_all_languages() as $language)
                                    <th>{{$language->name}}</th>
                                @endforeach
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<!-- Required datatable js -->
<script src="{{asset('/assets/admin/vendors/general/validate/jquery.validate.min.js')}}"></script>
<script src="{{asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {

        oTable = $('#listResults').DataTable({
                "processing": true,
                "serverSide": true,
                "order": [[0, "DESC"]],
                "ajax": "{{route('admin.category.listing')}}",
                "columns": [
                    {"data": "id", searchable: false, sortable: false},
                    {"data": "image", searchable: false, sortable: false},
                    @foreach(get_all_languages() as $language)
                    {"data": "{{$language->unique_name}}", sortable: false},
                    @endforeach
                    {"data": "action", searchable: false, sortable: false}
                ]
            });
        
    });
</script>
@endsection