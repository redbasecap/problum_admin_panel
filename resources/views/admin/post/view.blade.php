@extends('layouts.master')
@section ('css')
<style>
    .margindata {
        margin-bottom: 10px;
    }
</style>
<link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css" />
@endisection

@section('content')

@include('components.breadcum')
<div class="row wd-sl-row">
    <div class="col-md-4">
        <div class="card ">
            <div class="card-body">
                <div class="card-title">
                    <div class="kt-widget__media text-center w-100">

                    </div>
                </div>


                <div class="d-flex justify-content-between margindata">
                    <span class="font-weight-bold">Creator Email:</span>
                    <a href="#">{{$post->getUser->email}}</a>
                </div>
                <div class="d-flex justify-content-between margindata">
                    <span class="font-weight-bold">Pre Name:</span>
                    <a href="#">{{$post->getUser->first_name}}</a>
                </div>

                <div class="d-flex justify-content-between margindata">
                    <span class="font-weight-bold">Last Name:</span>
                    <a href="#">{{$post->getUser->last_name}}</a>
                </div>

                @if(count($post->languagePost) > 0)
                    @foreach($post->languagePost as $languageData)
                    <div class="d-flex justify-content-between margindata">
                        <span class="font-weight-bold">Problem Title  {{$languageData->language_unique_name}}:</span>
                        <a href="#">{{$languageData->title}}</a>
                    </div>
                <div class="d-flex justify-content-between  margindata">
                    <span class="font-weight-bold" style="margin-right:10px">Description  {{$languageData->language_unique_name}}:</span>
                    <span class="kt-widget__data">{{$languageData->description}}</span>
                </div>

                    @endforeach
                @endif
                

                <div class="d-flex justify-content-between  margindata">
                    <span class="font-weight-bold">Hashtag:</span>
                    <a href="mailto:{{$post->email}}">{{$post->hastag}}</a>
                </div>
                
                @if(count($post->getImages) > 0)

                <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">Problem Images:</span>
                </div>
                <div class="row">

                    @foreach($post->getImages as $images)

                    <div class="col-md-3">
                        {!!get_fancy_box_html($images->image_url,'image_75') !!}
                    </div>

                    @endforeach


                </div>
                @endif


            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card ">
            <div class="card-body">
                <div class="card-title">
                    Mee Too Info
                </div>
                <input type="hidden" name="post_id" id="post_id" value="{{$post->id}}" />
                <div class="table-responsive ">
                    <table id="meeTooList" class="table dt-responsive mb-4  nowrap w-100 mb-">
                        <thead>
                            <tr>
                                <th>Pre Name</th>
                                <th>Last Name</th>
                                <th> Name</th>
                                <th> Email</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-body">
                <div class="card-title">

                    Solution Info
                </div>
                <input type="hidden" name="post_id" id="post_id" value="{{$post->id}}" />
                <div class="table-responsive ">

                    <table id="solutionList" class="table dt-responsive mb-4  nowrap w-100 mb-">
                        <thead>
                            <tr>
                                <th>Pre Name</th>
                                <th>Last Name</th>
                                <th>Solution provider Email</th>
                                <th>Status</th>
                                <!-- <th>Description</th> -->
                                @foreach(get_all_languages() as $language)
                                    <th>Description {{$language->name}}</th>
                                @endforeach
                                <th>Solution Link</th>

                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
<script src="{{asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {


        oTable1 = $('#meeTooList').DataTable({
            "processing": true,
            "serverSide": true,
            "order": [
                [0, "DESC"]
            ],
            "ajax": {
                "url": "{{ route('admin.post.meTooListing')}}",
                "data": function(d) {
                    d.post_id = $('#post_id').val();
                }
            },
            "columns": [{
                    "data": "first_name",
                    searchable: false,
                    sortable: false
                },
                {
                    "data": "last_name",
                    searchable: false,
                    sortable: false
                },

                {
                    "data": "name",
                    searchable: false,
                    sortable: false
                },

                {
                    "data": "email",
                    searchable: false,
                    sortable: false
                },
            ]
        });

        oTable = $('#solutionList').DataTable({
            "processing": true,
            "serverSide": true,
            "order": [
                [0, "DESC"]
            ],
            "ajax": {
                "url": "{{route('admin.post.solutionListing')}}",
                "data": function(d) {
                    d.post_id = $('#post_id').val();
                }
            },
            "columns": [{
                    "data": "first_name",
                    searchable: false,
                    sortable: false
                },
                {
                    "data": "last_name",
                    searchable: false,
                    sortable: false
                },

                {
                    "data": "email",
                    searchable: false,
                    sortable: false
                },
                {
                    "data": "status",
                    sortable: false
                },
                @foreach(get_all_languages() as $language)
                 {"data": "{{$language->unique_name}}", sortable: false},
                @endforeach
                
                {
                    "data": "solution_link",
                    searchable: false,
                    sortable: false
                }

            ]
        });




    });
</script>
@endsection