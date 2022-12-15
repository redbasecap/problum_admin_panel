@extends('layouts.master')

@section('css')
<link href="{{asset('assets/libs/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
@section('title')
@lang('translation.Form_Layouts')
@endsection @section('content')
@include('components.breadcum')
<div class="row">
    <div class="col-12">
    </div>
    <div class="card">
        <div class="card-body">
            <form class="" name="main_form" id="main_form" method="post" action="{{route('admin.category.store')}}" enctype="multipart/form-data">
                {!! get_error_html($errors) !!}
                @csrf

                
               
                

                <div class="mb-3 row">
                    <label for="formFile" class="col-md-2 col-form-label"><span class="text-danger">*</span>Category Image</label>
                    <div class="col-md-10">
                        <input class="form-control" type="file" id="file_url" name="file_url" accept="image/*">
                    </div>
                </div>
                <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
        
                                        <h4 class="card-title">Detail</h4>
                                        <!-- <p class="card-title-desc">Example of custom tabs</p> -->
        
                                        <!-- Nav tabs -->
                                        <ul class="nav nav  -tabs nav-tabs-custom nav-justified" id="tabs" role="tablist">
                                        @foreach(get_all_languages() as$key=> $language)
                                            

                                            <li class="nav-item">
                                                <a class="nav-link {{$key==0?"active":""}}" data-bs-toggle="tab" href="#div_{{$language->unique_name}}"" role="tab">
                                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                    <span class="d-none d-sm-block">{{ucfirst($language->name)}}</span> 
                                                </a>
                                            </li>
                                        @endforeach
                                        

                                        </ul>
        
                                        <!-- Tab panes -->
                                        <div class="tab-content p-3 text-muted">
                                        @foreach(get_all_languages() as$key=> $language)
                                            

                                            
                                            <div class="tab-pane  {{$key==0?"active":""}}" id="div_{{$language->unique_name}}" role="tabpanel">
                                                <p class="mb-0">
                                                  <div class="mb-3 row">
                                                    <label for="example-text-input" class="col-md-2 col-form-label"><span class="text-danger">*</span>Category Name</label>
                                                    <div class="col-md-10">
                                                            <input type="text" name="name[{{$language->unique_name}}]" id="name_input_{{$language->unique_name}}" class="form-control" maxlength="255">
                                                    </div>
                                                </div>
                                                </p>
                                            </div>
                                        @endforeach
                                        
                                            
                                            
                                        </div>
        
                                    </div>
                                </div>
                            </div>

               
                <div class="kt-portlet__foot">
                    <div class=" ">
                        <div class="row">
                            <div class="wd-sl-modalbtn">
                                <button type="submit" class="btn btn-primary waves-effect waves-light" id="save_changes">Submit</button>
                                <a href="{{route('admin.category.index')}}" id="close"><button type="button" class="btn btn-outline-secondary waves-effect">Cancel</button></a>

                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection


@section('script')

<script src="{{asset('assets/libs/select2/js/select2.min.js')}}"></script>
<script>
    $(function() {

        

        $("#main_form").validate({
            rules: {
                file_url: {
                    required: true
                },
                @foreach(get_all_languages() as $key=> $language)
                "name[{{$language->unique_name}}]": {required: true},
                @endforeach
                
            },
            messages: {
                @foreach(get_all_languages() as$key=> $language)
                "name[{{$language->unique_name}}]": {required: "Please enter category name"},
                @endforeach
            },
            ignore: '',
            invalidHandler: function (event, validator) {
                    $('#tabs a[href="#' + jQuery(validator.errorList[0].element).closest(".tab-pane").attr('id') + '"]').tab('show');
                    // var alert = $('#kt_form_1_msg');
                    // alert.removeClass('kt--hide').show();
                    // KTUtil.scrollTo('m_form_1_msg', -200);
                },
            submitHandler: function(form) {
                addOverlay();
                form.submit();
            }
        });
    });
</script>
@endsection