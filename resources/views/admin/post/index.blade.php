@extends('layouts.master')
@section('title') @lang('translation.Data_Tables') @endsection
@section('css')

<!-- DataTables -->
<link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
<style>
    table.dataTable.nowrap th:nth-child(3), table.dataTable.nowrap td:nth-child(3),
    table.dataTable.nowrap th:nth-child(5), table.dataTable.nowrap td:nth-child(5),
    table.dataTable.nowrap th:nth-child(7), table.dataTable.nowrap td:nth-child(7){
        white-space:normal;
    }
</style>
@endsection
@section('content')

@include('components.breadcum')

<div class="row">
    <div class="col-12">
        {!! success_error_view_generator() !!}

    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive ">
                <table id="listResults" class="table dt-responsive mb-4  nowrap w-100 mb-">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>UserName</th>
                            <th>HasTag</th>
                                @foreach(get_all_languages() as $language)
                                    <th>Title {{$language->name}}</th>
                                    <th>Description {{$language->name}}</th>
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
            "order": [
                [0, "DESC"]
            ],
            "ajax": "{{route('admin.post.listing')}}",
            "columns": [{
                    "data": "id",
                    searchable: false,
                    sortable: false
                },
                {
                    "data": "user_id",
                    searchable: false,
                    sortable: false
                },
                
                {
                    "data": "hastag",
                    sortable: false
                },
                 @foreach(get_all_languages() as $language)
                    {"data": "title_{{$language->unique_name}}", sortable: false},
                    {"data": "description_{{$language->unique_name}}", sortable: false},
                    @endforeach
               
                {
                    "data": "action",
                    searchable: false,
                    sortable: false
                }
            ]
        });
    });
</script>
@endsection
