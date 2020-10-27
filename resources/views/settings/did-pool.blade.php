@extends('layouts.home')
@section('content')
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
                                    <li class="breadcrumb-item"><a href="{{ url('/roles') }}">Roles</a></li>
                                    <li class="breadcrumb-item active">Edit Role</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Role Permission</h4>
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
                                    <li><a href="{{ url('/countries') }}">Countries</a></li>
                                    <li><a href="{{ url('/credits') }}">Credit</a></li>
                                    <li><a href="{{ url('/coupons') }}">Coupons</a></li>
                                    <li><a href="{{ url('/switch') }}">Switch</a></li>
                                    <li><a href="{{ url('/did-pool') }}" class="selected">DID Pool</a></li>
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
@endsection
