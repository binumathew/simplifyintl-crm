@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Country</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Country List</h4>
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
                            <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <table id="countryList" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th class="d-none">#</th>
                                            <th>Flag</th>
                                            <th>Country Name</th>                                           
                                            <th>Country Code</th>
                                            <th>Dial Code</th>  
                                            <th>Currency</th>
                                            <th>Symbol</th> 
                                            <th>Tax</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>                                    
                                    </thead>
                                    <tbody> 
                                        @php $i=0; @endphp
                                        @foreach ($countries as $country)
                                        <tr id="{{ ++$i }}">
                                            <td class="d-none">{{ $i }}</td>
                                            <td><img src="{{ asset('public/images/flags') }}/{{ strtolower( $country->short_code ) }}.png" width="32" height="32"></td>
                                            <td>{{$country->country_name}}</td>                         
                                            <td>{{$country->country_code}}</td>
                                            <td>{{$country->dial_code}}</td>
                                            <td>{{$country->currency}} </td>                                        
                                            <td>{{$country->currency_symbol}}</td>  
                                            <td>{{$country->tax}}</td>
                                            <td>
                                                @if($country->status)
                                                <span class="badge badge-success">Active</span>
                                                @else
                                                <span class="badge badge-danger">In-Active</span>
                                                @endif
                                            </td>
                                            <td> 
                                                @if(Helper::has_permission('countries','edit'))
                                                <a title="" href="{{ url('/country', $country->id) }}" data-original-title="Edit" data-toggle="tooltip" class="text-muted"><i class="mdi mdi-pencil mdi-24px"></i></a>
                                                @endif
                                                @if(Helper::has_permission('countries','delete'))
                                                <!-- <a data-toggle="tooltip" title="Delete" href="{{ url('/delete-country', Crypt::encrypt($country->id)) }}" class="fa fa-trash-o"></a> -->
                                                @endif                                                
                                            </td>
                                        </tr>
                                        @endforeach                                      
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

            
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#countryList').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search: '' },});

                    $('.dataTables_filter input').attr('placeholder', 'Search');
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection