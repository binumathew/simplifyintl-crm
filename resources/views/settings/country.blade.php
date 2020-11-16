@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">{{($country)?'Edit':'Add'}} Country</li>
                            </ol>
                        </div>
                        <h4 class="page-title">{{($country)?'Edit':'Add'}} Country</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="card m-b-20">

                        <div class="card-body right-nav">
                            <ul>
                                <li><a href="{{ url('/settings') }}">General</a></li>
                                <li><a href="{{ url('/template') }}">Email Template</a></li>
                                <li><a href="{{ url('/roles') }}">Roles</a></li>
                                <li><a href="{{ url('/countries') }}" class="selected">Countries</a></li>
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
                        <div class="card-body">

                            <form action="{{ url('save-country') }}" method="post" id="country-form">
                            @csrf
                                <input type="hidden" name="country_id" value="{{ ($country)?$country->id:'' }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="country_name" class="col-form-label">Country Name</label>
                                            <input id="country_name" name="country_name" type="text" class="form-control" value="{{ ($country)?$country->country_name:'' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="flag" class="col-form-label">Flag</label>
                                            <div class="bootstrap-filestyle input-group">
                                                <input type="file" id="importfile" name="stock_list" accept=".png" style="position: absolute; clip: rect(0px, 0px, 0px, 0px);">
                                                <input type="text" class="form-control importfile" disabled placeholder="{{($country)?(strtolower($country->short_code).'.png'):''}}">
                                                <span class="group-span-filestyle input-group-append" tabindex="0">
                                                    <label for="importfile" class="btn btn-secondary">
                                                        <span class="icon-span-filestyle fa fa-folder-open"></span>
                                                        <span class="buttonText">Choose file</span>
                                                    </label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="time_zone" class="col-form-label">Time Zone</label>
                                            <input id="time_zone" name="time_zone" type="text" class="form-control" value="{{ ($country)?$country->time_zone:'' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="country_code" class="col-form-label">Country Code</label>
                                            <input id="country_code" name="country_code" type="text" class="form-control" value="{{ ($country)?$country->country_code:'' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="short_code" class="col-form-label">Short Code</label>
                                            <input id="short_code" name="short_code" type="text" class="form-control" value="{{ ($country)?$country->short_code:'' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="dial_code" class="col-form-label">Dial Code</label>
                                            <input id="dial_code" name="dial_code" type="text" class="form-control" value="{{ ($country)?$country->dial_code:'' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="otp_type" class="col-form-label">OTP Type</label>
                                            <select id="otp_type" name="otp_type" class="custom-select">
                                                <option value="1" {{ ($country->otp_type == 1)?'selected':'' }}>SMS Alert</option>
                                                <option value="0" {{ ($country->otp_type == 0)?'selected':'' }}>Call Alert</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="otp_type" class="col-form-label">Access Number Support</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="access_support" name="access_support" value="1" {{ ($country && $country->accessnumber_support == 1)? 'checked':'' }}>
                                                <label class="custom-control-label" for="access_support"></label>
                                                <!-- <input type="checkbox" id="switch1" checked switch="none" />
                                                <label for="switch1" data-on-label="On" data-off-label="Off"></label> -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="switch_id" class="col-form-label">Callback Support</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="callback_support" name="callback_support" value="1" {{ ($country && $country->callback_support == 1)? 'checked':'' }}>
                                                <label class="custom-control-label" for="callback_support"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="access_number" class="col-form-label">Wifi Support</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="wifi_support" name="wifi_support" value="1" {{ ($country && $country->wifi_support == 1)? 'checked':'' }}>
                                                <label class="custom-control-label" for="wifi_support"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="access_number" class="col-form-label">Conference Support</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="conference_support" name="conference_support" value="1" {{ ($country && $country->conference_support == 1)? 'checked':'' }}>
                                                <label class="custom-control-label" for="conference_support"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="access_number" class="col-form-label">Popular</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="popular" name="popular" value="1" {{ ($country && $country->popular == 1)? 'checked':'' }}>
                                                <label class="custom-control-label" for="popular"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="switch_id" class="col-form-label">Switch</label>
                                            <select id="switch_id" name="switch_id" class="custom-select">
                                                @foreach ($switch as $item)
                                                    <option value="{{$item->id}}" {{ ($country->switch_id == $item->id)?'selected':'' }}>{{$item->currency}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="access_number" class="col-form-label">Access Number</label>
                                            <input id="access_number" name="access_number" type="text" class="form-control" value="{{ ($country)?$country->access_number:'' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="land_price" class="col-form-label">Land Line Price</label>
                                            <input id="land_price" name="land_price" type="text" class="form-control" value="{{ ($country)?$country->land_price:'' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="mob_price" class="col-form-label">Mobile Price</label>
                                            <input id="currency" name="currency" type="text" class="form-control" value="{{ ($country)?$country->mob_price:'' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="currency" class="col-form-label">Currency</label>
                                            <input id="currency" name="currency" type="text" class="form-control" value="{{ ($country)?$country->currency:'' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="currency_symbol" class="col-form-label">Currency Symbol</label>
                                            <input id="currency_symbol" name="currency_symbol" type="text" class="form-control" value="{{ ($country)?$country->currency_symbol:'' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tax" class="col-form-label">Tax %</label>
                                            <input id="tax" name="tax" type="text" class="form-control" value="{{ ($country)?$country->tax:'' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="c-status" class="col-form-label">Status</label>
                                            <select id="c-status" name="status" class="custom-select"> value=""
                                                <option value="1" {{ ($country->status == 1)?'selected':'' }}>Active</option>
                                                <option value="0" {{ ($country->status == 0)?'selected':'' }}>In Active</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row pull-right">
                                    <div class="col-md-12">
                                        <a href="{{ url('/countries') }}" class="btn btn-secondary mr-md-1" >Cancel</a>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>





            </div>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->

     <script type="text/javascript">
        $(document).ready(function(){
            $(document).on('change','#importfile', function() {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".importfile").attr('placeholder',fileName);
            });

            $('#country-form').validate({
                errorClass: "text-danger",
                rules: {
                    country_name: 'required',
                    country_code: 'required',
                    short_code: 'required',
                    dial_code: 'required',
                    currency: 'required',
                    currency_symbol: 'required',
                },
            });
        });
    </script>

@endsection
