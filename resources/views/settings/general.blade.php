@extends('layouts.home')

@section('content')

    <!-- page wrapper start -->

        <div class="wrapper">

            <div class="container-fluid">

                <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

                <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

                <div class="row">

                    <div class="col-sm-12">

                        <div class="page-title-box">

                            <div class="btn-group pull-right">

                                <ol class="breadcrumb hide-phone p-0 m-0">

                                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>

                                    <li class="breadcrumb-item active">Settings</li>

                                </ol>

                            </div>

                            <h4 class="page-title">Settings</h4>

                        </div>

                    </div>

                </div>



                <div class="row">

                    <div class="col-md-2">

                        <div class="card m-b-20">



                            <div class="card-body right-nav">

                                <ul>

                                    <li><a href="{{ url('/settings') }}" class="selected">General</a></li>

                                    <li><a href="{{ url('/template') }}">Email Template</a></li>

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
                                    @if(Helper::has_permission('api-log'))
                                    <li><a href="{{ url('/api-logger') }}">API Log</a></li>
                                    @endif

                                    <li><a href="{{ url('/activity-log') }}">Activity Log</a></li>
                                    @if(Helper::has_permission('stock'))
                                    <li><a href="{{ url('/stock-list') }}">Stock</a></li>
                                    @endif



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

                                <div class="col-md-12 m-b-20">

                                    <div class=" text-right">

                                        <a href="javascript:void(0);">

                                            <button type="button" class="btn btn-primary waves-effect waves-light" id="add_settings">Add Settings</button>

                                        </a>

                                    </div>

                                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">

                                        <li class="nav-item">

                                            <a class="nav-link active" data-toggle="tab" href="#general" role="tab">

                                                <span class="d-none d-md-block">Company Setting</span><span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>

                                            </a>

                                        </li>

                                        <li class="nav-item">

                                            <a class="nav-link" data-toggle="tab" href="#sms_settings" role="tab">

                                                <span class="d-none d-md-block">SMS / Email</span><span class="d-block d-md-none"><i class="mdi mdi-email h5"></i></span>

                                            </a>

                                        </li>

                                        <li class="nav-item">

                                            <a class="nav-link" data-toggle="tab" href="#switch_settings" role="tab">

                                                <span class="d-none d-md-block">Mobile App</span><span class="d-block d-md-none"><i class="mdi mdi-cellphone-iphone h5"></i></span>

                                            </a>

                                        </li>

                                        <li class="nav-item d-none">

                                            <a class="nav-link" data-toggle="tab" href="#custom_settings" role="tab">

                                                <span class="d-none d-md-block">Custom Setting</span><span class="d-block d-md-none"><i class="mdi mdi-settings h5"></i></span>

                                            </a>

                                        </li>

                                    </ul>

                                </div>



                                <div class="tab-content">

                                    <div class="tab-pane active p-3" id="general" role="tabpanel">
                                        @php
                                         $company = (!empty($company) ? json_decode($company->value) : []);
                                        @endphp
                                        <form class="company_settings_form mt-0-fix" id="company_settings_form" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">

                                            <div class="col-md-4">

                                                <div class="form-group">

                                                    <label>Company Icon</label>

                                                    <input type="file" class="filestyle" data-buttonname="btn-secondary" id="filestyle-0" tabindex="-1" style="position: absolute; clip: rect(0px, 0px, 0px, 0px);">

                                                    <div class="bootstrap-filestyle input-group">

                                                        <input type="text" class="form-control " placeholder="" disabled>

                                                        <span class="group-span-filestyle input-group-append" tabindex="0">

                                                            <label for="filestyle-0" class="btn btn-secondary ">

                                                                <span class="icon-span-filestyle fa fa-folder-open"></span>

                                                                <span class="buttonText">Choose file</span>

                                                            </label>

                                                        </span>

                                                    </div>

                                                </div>

                                            </div>



                                            <div class="col-md-4">

                                                <div class="form-group">

                                                    <label>Company Logo</label>

                                                    <input type="file" class="filestyle" data-buttonname="btn-secondary" id="filestyle-1" tabindex="-1" style="position: absolute; clip: rect(0px, 0px, 0px, 0px);" >

                                                    <div class="bootstrap-filestyle input-group company_images" data-type="logo">

                                                        <input type="text" class="form-control " placeholder="" disabled>

                                                        <span class="group-span-filestyle input-group-append" tabindex="0">

                                                            <label for="filestyle-0" class="btn btn-secondary ">

                                                                <span class="icon-span-filestyle fa fa-folder-open"></span>

                                                                <span class="buttonText">Choose file</span>

                                                            </label>

                                                        </span>

                                                    </div>

                                                </div>

                                            </div>



                                            <div class="col-md-4">

                                                <div class="form-group">

                                                    <label>Company Favicon</label>

                                                    <input type="file" class="filestyle" data-buttonname="btn-secondary" id="filestyle-2" tabindex="-1" style="position: absolute; clip: rect(0px, 0px, 0px, 0px);">

                                                    <div class="bootstrap-filestyle input-group company_images" data-type="favicon">

                                                        <input type="text" class="form-control " placeholder="" disabled>

                                                        <span class="group-span-filestyle input-group-append" tabindex="0">

                                                            <label for="filestyle-0" class="btn btn-secondary ">

                                                                <span class="icon-span-filestyle fa fa-folder-open"></span>

                                                                <span class="buttonText">Choose file</span>

                                                            </label>

                                                        </span>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="row">

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>Company Name</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control " placeholder="Company Name" name="company_name" maxlength="50" value="{{ (!empty($company) ? $company->company_name : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>Company Main Domain</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control " placeholder="Main Domain eg. www.example.com" name="company_website" maxlength="50" value="{{ (!empty($company) ? $company->company_website : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>Phone</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control" placeholder="Phone" name="company_phone" minlength="8" maxlength="15" value="{{ (!empty($company) ? $company->company_phone : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>Email</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control" placeholder="Email" name="company_email" maxlength="30" value="{{ (!empty($company) ? $company->company_email : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>Adress</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control " placeholder="Street" name="company_street" maxlength="30" id="company_street" value="{{ (!empty($company) ? $company->company_street : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>City</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control " placeholder="City" name="company_city" maxlength="30" id="company_city" value="{{ (!empty($company) ? $company->company_city : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>State</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control " placeholder="State" name="company_state" maxlength="30" id="company_state" value="{{ (!empty($company) ? $company->company_state : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>Country</label>

                                                    <div class="input-group">
                                                        <select name="company_country" id="company_country" class="form-control" aria-invalid="false">
                                                        <option value="" disabled="" selected="">Select Country</option>
                                                        @foreach (Helper::getCountry() as $countrylist)
                                                        @php
                                                        if(!empty($company) && $company->company_country == $countrylist->country_name){

                                                            $selected = 'selected';
                                                        }else{   $selected = '';
                                                        }
                                                    @endphp
                                                        <option {{$selected}} value="{{$countrylist->country_name}}" data-dialcode="{{$countrylist->dial_code}}" data-shortcode="{{$countrylist->short_code}}">{{$countrylist->country_name}} ({{$countrylist->dial_code}})
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>Postal Code</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control " placeholder="Postal Code" name="company_postcode" maxlength="15" id="company_postcode" value="{{ (!empty($company) ? $company->company_postcode : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>VAT Number</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control " placeholder="VAT Number" name="company_vat" maxlength="20" id="company_vat" value="{{ (!empty($company) ? $company->company_vat : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>Dial Code</label>

                                                    <div class=" input-group">

                                                        <input type="text" class="form-control " placeholder="Dial Code" name="company_dialcode" readonly maxlength="6" id="company_dialcode" value="{{ (!empty($company) ? $company->company_dialcode : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>Country Code</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control " placeholder="Country Code" name="company_cntry_code" readonly maxlength="3" id="company_cntry_code" value="{{ (!empty($company) ? $company->company_cntry_code : '')}}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>

                                        </div>



                                        <div class="row">

                                            <div class="col-md-12 text-right">

                                                <button class="btn btn-secondary">Cancel</button>

                                                <button class="btn btn-success waves-effect waves-light" type="submit">Save</button>

                                            </div>

                                        </div>
                                        </form>
                                    </div>



                                    <div class="tab-pane p-3" id="sms_settings" role="tabpanel">
                                        <form class="custom_sms_form mt-0-fix" id="custom_sms_form">
                                        @csrf
                                        <div class="row">
                                            @foreach($sms as $skey => $slist)
                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>{{ ucwords(str_replace("_"," ",$slist->name)) }}</label>

                                                    <div class="input-group">

                                                       <input type="text" class="form-control " name="{{ $slist->name }}" value="{{$slist->value}}" placeholder="{{ ucwords(str_replace("_"," ",$slist->name)) }}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>
                                            @endforeach
                                        </div>



                                        <div class="row">

                                            <div class="col-md-12 text-right">

                                                <button class="btn btn-secondary">Cancel</button>

                                                <button class="btn btn-success waves-effect waves-light" type="submit">Save</button>

                                            </div>

                                        </div>
                                    </form>
                                    </div>



                                    <div class="tab-pane p-3" id="switch_settings" role="tabpanel">
                                        <form class="custom_switch_form mt-0-fix" id="custom_switch_form">
                                        @csrf
                                        <div class="row">
                                            @foreach($switch as $swkey => $swlist)
                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>{{ ucwords(str_replace("_"," ",$swlist->name)) }}</label>

                                                    <div class="input-group">

                                                       <input type="text" class="form-control " name="{{ $swlist->name }}" value="{{$swlist->value}}" placeholder="{{ ucwords(str_replace("_"," ",$swlist->name)) }}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="row">

                                            <div class="col-md-12 text-right">

                                                <button class="btn btn-secondary">Cancel</button>

                                                <button class="btn btn-success waves-effect waves-light" type="submit">Save</button>

                                            </div>

                                        </div>
                                    </form>
                                    </div>

                                    <div class="tab-pane p-3" id="custom_settings" role="tabpanel">
                                        <form class="custom_setting_form mt-0-fix" id="custom_setting_form">
                                        @csrf
                                        <div class="row">
                                            @php $json_settings = []; @endphp
                                            @foreach($options as $okey => $olist)
                                            @php
                                            if($olist->name == "conference_settings_name" || $olist->name == "sim_stock_box_category"){
                                                $json_settings[$olist->name] = $olist;
                                                continue;

                                            }
                                            if($olist->name == "conference_settings"){ continue; }
                                            @endphp
                                            <div class="col-md-4">

                                                <div class="form-group">

                                                    <label>{{ ucwords(str_replace("_"," ",$olist->name)) }}</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control " name="{{ $olist->name }}" value="{{$olist->value}}" placeholder="{{ ucwords(str_replace("_"," ",$olist->name)) }}">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>
                                            @endforeach
                                            @php $sim_stock = $json_settings['sim_stock_box_category'];
                                            $simvals = json_decode($sim_stock->value);  @endphp
                                            @foreach($simvals as $skey => $slist)
                                            <div class="col-md-2">

                                                <div class="form-group">

                                                    <label>Sim stock box category {{ $skey }}</label>

                                                    <div class="input-group">

                                                        <input type="text" class="form-control " name="sim_stock_box_category[{{$skey}}]" value="{{$slist}}" placeholder="">

                                                    </div>
                                                    <span></span>
                                                </div>

                                            </div>
                                            @endforeach
                                        </div>



                                        <div class="row">

                                            <div class="col-md-12 text-right">

                                                <button class="btn btn-secondary">Cancel</button>

                                                <button class="btn btn-success waves-effect waves-light" type="submit">Save</button>

                                            </div>

                                        </div>
                                    </form>
                                    <hr>
                                    <h5>Conference Settings Controls</h5>
                                    @php $conf_settings = $json_settings['conference_settings_name'];
                                    $vals = json_decode($conf_settings->value); @endphp
                                    @foreach($vals as $vkey => $vlist)
                                    @php $checked = ($vlist->value == 1) ? 'checked' : "";
                                    $changable = ($vlist->changeable == true) ? 'checked' : "";
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-2">
                                        <label>{{ $vlist->name }}</label>
                                        </div>
                                        <div class="col-md-2">
                                        <input type="checkbox" {{$checked}} data-toggle="toggle" data-on="Enabled" data-off="Disabled" data-onstyle="success" data-offstyle="danger" data-width="100%" name="{{ $vkey }}" value="{{ $vlist->value }}" class="conf_settings" data-type="value">
                                        </div>
                                        <div class="col-md-2">
                                        <input type="checkbox" {{$changable}} data-toggle="toggle" data-on="Changeable" data-off="Not changeable" data-onstyle="info" data-offstyle="dark" data-width="100%" name="{{ $vkey }}" value="{{ $vlist->changeable }}" class="conf_settings" data-type="changeable">
                                        </div>
                                    </div>
                                    <br>
                                    @endforeach
                                    </div>



                            </div>

                        </div>

                    </div>





                </div>



                <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>

                <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>

                <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>

                <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

            </div>

        </div>

        <!-- page wrapper end -->
                <!-- custom modal popup start -->
        <div id="addSettingsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0" id="addSettingsLabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" id="addSettingsbody">

                    </div>
                </div>
            </div>
        </div>
        <!-- custom modal popup end -->


        <script type="text/javascript">

            $(document).ready(function(){

                var notificationTable = $('#api-log').DataTable({

                    processing: true,

                    serverSide: true,

                    pageLength: 25,

                    responsive: true,

                    searching: true,

                    "bSort" : false,

                    autoWidth: false,

                    ajax: {

                        url: 'list-api-log',

                        data: function (d) {

                        }

                    },

                    columns: [

                        {data: 'name', name: 'usr.name'},

                        {data: 'path', name: 'al.path'},

                        {data: 'method', name: 'al.method'},

                        {data: 'exec_time', name: 'al.exec_time'},

                        {data: 'created_at', name: 'al.created_at'},

                        {data: 'request', name: 'al.request'},

                        {data: 'response', name: 'al.response'},

                    ],

                    columnDefs: [

                       { width: '100px', targets: 2 }

                    ]

                });



                $('.dataTables_filter input').attr("placeholder", "Search");

            });

        </script>

@endsection

