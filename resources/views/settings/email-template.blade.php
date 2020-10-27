@extends('layouts.home')

@section('content')
<style type="text/css">
/* width */
#style-1::-webkit-scrollbar {
  width: 4px;
}
/* Track */
#style-1::-webkit-scrollbar-track {
  background: #f1f1f1; 
}
 
/* Handle */
#style-1::-webkit-scrollbar-thumb {
  background: #028fab; 
}

/* Handle on hover */
#style-1::-webkit-scrollbar-thumb:hover {
  background: #555; 
}
</style>
    <!-- page wrapper start -->

        <div class="wrapper">

            <div class="container-fluid">

                <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

                <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

                <div class="row">

                    <div class="col-sm-12">

                        <div class="page-title-box">

                            <div class="btn-group pull-right">

                                <ol class="breadcrumb hide-phone p-0 m-0">

                                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>

                                    <li class="breadcrumb-item"><a href="{{ url('/settings') }}">Settings</a></li>

                                    <li class="breadcrumb-item active">Email Template</li>

                                </ol>

                            </div>

                            <h4 class="page-title">Email Template</h4>

                        </div>

                    </div>

                </div>



                <div class="row">


                    <div class="col-md-2">

                        <div class="card m-b-20">

                            

                            <div class="card-body right-nav">

                                <ul>

                                    <li><a href="{{ url('/settings') }}">General</a></li>                                    

                                    <li><a href="{{url('/template')}}" class="selected">Email Template</a></li>

                                    <li><a href="{{ url('/roles') }}">Roles</a></li>

                                    <li><a href="{{ url('/countries') }}">Countries</a></li>

                                    <li><a href="{{ url('/credits') }}">Credit</a></li>

                                    <li><a href="{{ url('/coupons') }}">Coupons</a></li>

                                    <li><a href="{{ url('/switch') }}">Switch</a></li>

                                    <li><a href="{{ url('/did-pool') }}">DID Pool</a></li>

                                    <li><a href="{{ url('/throttles') }}">Throttles</a></li>

                                    <li><a href="{{ url('/firewall') }}">Firewall</a></li>

                                    <li><a href="{{ url('/scheduled-tasks') }}">Cron Jobs</a></li>

                                    <li><a href="{{ url('/payment-gateway') }}">Payment Gateways</a></li>

                                    <li><a href="{{ url('/stock-list') }}">Stock</a></li>

                                    <li><a href="{{ url('/api-logger') }}">API Log</a></li>



                                    <!-- <li><a href="#">Leads</a></li>

                                    <li><a href="#">SMS</a></li>

                                    <li><a href="#">Calendar</a></li>

                                    <li><a href="#">PDF</a></li>

                                    <li><a href="#">E-Sign</a></li>

                                    <li><a href="#">Cron Job</a></li>

                                    <li><a href="#">Tags</a></li>

                                    <li><a href="#">Pusher.com</a></li>

                                    <li><a href="#">Google</a></li>

                                    <li><a href="#">Misc</a></li> -->

                                </ul>                           

                            </div>

                        </div>

                    </div>
        <div class="col-md-10">
            <div class="card m-b-20">
                <div class="card-body my-setting-page">   
                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-1" role="tab">
                                <span class="d-none d-md-block">General</span><span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-2" role="tab">
                                <span class="d-none d-md-block">Theme</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-3" role="tab">
                                <span class="d-none d-md-block">Marketing Tags</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-4" role="tab">
                                <span class="d-none d-md-block">Advance Settings</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-5" role="tab">
                                <span class="d-none d-md-block">Plain Text Version</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-6" role="tab">
                                <span class="d-none d-md-block">Dynamic Content</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                            </a>
                        </li>  
                    </ul>
            <div class="tab-content">
                <div class="tab-pane p-3" id="tab-1" role="tabpanel"><h5>General</h5></div>
                <div class="tab-pane p-3" id="tab-2" role="tabpanel"><h5>Theme</h5></div>
                <div class="tab-pane p-3" id="tab-3" role="tabpanel"><h5>Marketing Tags</h5></div>
                <div class="tab-pane p-3" id="tab-4" role="tabpanel"><h5>Advance Settings</h5></div>
                <div class="tab-pane p-3" id="tab-5" role="tabpanel"><h5>Plain Text Version</h5></div>
                <div class="tab-pane active p-3" id="tab-6" role="tabpanel">
                <div class="row">
                    <div class="col-md-9">
                        <input type="hidden" name="edit_id" value="{{ (!empty($content)) ? Crypt::encrypt($content->id) : "" }}" id="edit_id">
                        <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label>Template Name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control " placeholder="eg. order_confirmation" name="template_name" maxlength="50" value="{{ (!empty($content)) ? $content->email_name : "" }}" id="template_name">
                                </div>
                                <span></span>
                            </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 textarea-email">
                                <h5>Dynamic Content</h5>
                                <form method="post">
                                    <textarea id="email_template_editor" name="email_template_editor">{{ (!empty($content)) ? $content->email_content : "" }}</textarea>
                                </form>
                                <br clear="all" />

                                <div class="col-md-12 button-bottom-top">
                                    <div class="button-bottom">
                                        <div class="alert alert-success new-orderbutton" role="alert" style="display: inline-block; float: right; text-align: center;">
                                            <a href="javascript:void(0)" id="saveEmailTemplate"><strong>{{ (!empty($content)) ? "Update Template" : "Save new Template" }}</strong></a>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                        </div> 
                    </div>
                    <div class="col-md-3">
                        <div class="right-nav">
                        <ul>
                            <li><a href="{{url('/template')}}" class="add-new">Add New</a></li>
                        </ul>
                        </div>
                        <div class="right-nav" style="height: 90px;overflow-y: scroll;padding: 1px;" id="style-1">
                        <ul>
                            @if(!empty($list))
                            @foreach($list as $lkey => $lval)
                            @php
                            if($id != "" &&  $lval->id == $id){
                                unset($list[$lkey]);
                            }else{ continue; }
                            @endphp
                            <li><a href="javascript:void(0);" class="text-muted nav-del-icon sel delete_template" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" data-id="{{ Crypt::encrypt($lval->id) }}" data-tempname="{{ ucwords(str_replace("_"," ",$lval->email_name)) }}"><i class="mdi mdi-delete font-18"></i></a><a href="{{url('/template/'.Crypt::encrypt($lval->id))}}" class="selected">{{ ucwords(str_replace("_"," ",$lval->email_name)) }}</a></li>
                            @endforeach
                            @endif

                            @if(!empty($list))
                            @foreach($list as $lkey => $lval)
                            @php
                            @endphp
                            <li><a href="javascript:void(0);" class="text-muted nav-del-icon delete_template" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" data-id="{{ Crypt::encrypt($lval->id) }}" data-tempname="{{ ucwords(str_replace("_"," ",$lval->email_name)) }}"><i class="mdi mdi-delete font-18"></i></a><a href="{{url('/template/'.Crypt::encrypt($lval->id))}}">{{ ucwords(str_replace("_"," ",$lval->email_name)) }}</a></li>
                            @endforeach
                            @endif
                        </ul>
                        </div>

                        {!! $template_html !!}
                    </div>
                    </div>
                    </div>
                    </div>
                            </div>
                        </div>
                    </div>

                </div>




                <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>

                <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>

                <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>

                <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

            </div>

        </div>

        <!-- page wrapper end -->
 <!--Wysiwig js-->
<script src="{{ asset('public/plugins/tinymce/tinymce.min.js') }}"></script>
<script>
$(document).ready(function () {
        if($("#email_template_editor").length > 0){
            tinymce.init({
                selector: "textarea#email_template_editor",
                theme: "modern",
                height:300,
                plugins: [
                    "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                    "save table contextmenu directionality emoticons template paste textcolor"
                ],
                toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",
                style_formats: [
                    {title: 'Bold text', inline: 'b'},
                    {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                    {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                    {title: 'Example 1', inline: 'span', classes: 'example1'},
                    {title: 'Example 2', inline: 'span', classes: 'example2'},
                    {title: 'Table styles'},
                    {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
                ]
            });
        }
    });
</script>
@endsection

