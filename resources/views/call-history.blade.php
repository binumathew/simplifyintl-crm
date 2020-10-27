@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
        <div class="wrapper">
            <div class="container-fluid">
                <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
                <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
                <link href="{{ asset('public/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="btn-group pull-right">
                                <ol class="breadcrumb hide-phone p-0 m-0">
                                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Call History</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Call History</h4>
                        </div>
                    </div>
                </div>
                @if(Helper::has_permission('reports'))
                <div class="row">
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-primary mr-0 float-right"><i class="mdi mdi-cart-outline"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="total_amount">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Total Calls<span class="pull-right"><i class="fa fa-caret-down text-danger m-r-5"></i>3.25%</span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-success mr-0 float-right"><i class="mdi mdi-currency-gbp"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="total_buy_amount">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Switch Calls<span class="pull-right"><i class="fa fa-caret-up text-success m-r-5"></i>8.51%</span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-danger mr-0 float-right"><i class="mdi mdi-rotate-right"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="total_refund">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Sim Calls<span class="pull-right">
                                <i class="fa fa-caret-up text-success m-r-5"></i>7.10%</span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-warning mr-0 float-right"><i class="mdi mdi-fingerprint"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="total_profit">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Paid Calls <span class="pull-right"><i class="fa fa-caret-down text-danger m-r-5"></i>5.52%</span></p>
                        </div>
                    </div>                    
                </div>
                @endif
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('call-history-list') }}" id="call-history-form" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>CLI</label>
                                                    <input class="form-control" id="user_cli" name="user_cli" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Date From</label>
                                                    <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="Date From" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Date To</label>
                                                    <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="Date To" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Call Channel</label>
                                                    <select name="channel" id="call_channel" class="form-control">
                                                        <option value="0">All</option>                                                          
                                                        <option value="1">Application</option>
                                                        <option value="2">Mobile</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Call Type</label>
                                                    <select name="call_type" id="call_type" class="form-control">
                                                        <option value="0">All</option>  
                                                        <option value="1">Free Call</option>
                                                        <option value="2">Paid Call</option>
                                                    </select>
                                                </div>
                                            </div>                                            
                                            <div class=" col-md-12">
                                                @if(Helper::has_permission('reports'))
                                                <button type="submit" class="btn btn-info" id="export" name="exportdata" value="1">Export</button>
                                                @endif
                                                <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                                <button type="button" id="searchBtn" class="btn btn-primary pull-right">Search</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>                                
                                <table id="calllist" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>                                           
                                            <th>CLI</th>
                                            <th>CLD</th>    
                                            <th>Duration</th>
                                            <th>Cost</th>   
                                            <th>Connect Time</th>
                                            <th>Platform</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                        
                                        <!-- <td><i class="fa fa-cc-visa text-muted font-20"></i> Visa ****123</td>
                                        <td><i class="fa fa-cc-mastercard text-muted font-20"></i> </td>
                                        <td><i class="fa fa-cc-paypal text-muted font-20"></i> </td>
                                        <td><i class="fa fa-cc-amex text-muted font-20"></i> </td> 
                                        <td><i class="fa fa-cc-discover text-muted font-20"></i> </td>
                                        <td>Jul 20, 2020</td>-->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
                <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
                <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
                <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
                <script src="{{ asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
            </div>
        </div>
        <!-- page wrapper end -->

        <script type="text/javascript">
            $(document).ready(function(){
                function secondsTimeSpanToHMS(s) {
                    var h = Math.floor(s/3600); 
                    s -= h*3600;
                    var m = Math.floor(s/60); 
                    s -= m*60;
                    return (h < 10 ? '0'+h : h)+":"+(m < 10 ? '0'+m : m)+":"+(s < 10 ? '0'+s : s); 
                }

                $('#calllist').DataTable({
                    dom: 'Bfrtip',
                    responsive: true,
                    bSort: true,
                    pageLength: 25,
                    language: { search: "" },
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: 'call-history-list',
                        data: function (d) {
                            d.user_cli = $('#user_cli').val();
                            d.from_date = $('#from_date').val();
                            d.to_date = $('#to_date').val();
                            d.channel = $('#call_channel').val();
                            d.call_type = $('#call_type').val();
                        }
                    },
                    "dataType": "jsonp",
                    "columns": [
                        {"data": "username", "name": "username"},
                        {"data" : "cli","name":"cli"},
                        {"data" : "cld","name":"cld"},
                        {"data": function(data){
                            return secondsTimeSpanToHMS(data.duration);
                        },"name":"duration"},
                        {"data" : "cost","name":"cost"},
                        {"data" : "connect_date","name":"connect_date"},
                        {"data" : function (data) {
                            return (data.history_from == 1)?'App':'Sim';
                        },"name":"history_from"},
                    ],
                    "order":[[5, 'desc']],
                    "columnDefs": [
                        {"defaultContent": "-","targets": "_all"}
                    ],
                    buttons: [],
                });

                $('.dataTables_filter input').attr("placeholder", "Search");

                $('#searchBtn').on('click', function(e) {
                    $('#calllist').DataTable().draw();                   
                });

                $('#resetBtn').on('click', function(e) {
                    $('#call-history-form')[0].reset();
                    $('#calllist').DataTable().draw(); 
                });

                $('.datepicker').datepicker({
                    autoclose: true,
                    orientation:'bottom left',
                    format: 'yyyy-mm-dd',
                    todayHighlight: true
                });

            });
        </script>
@endsection
