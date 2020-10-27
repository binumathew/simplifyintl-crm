@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
            <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
            <link href="{{ asset('public/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a></li>
                                <li class="breadcrumb-item active">Order History</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Order History</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div class="order-search">
                                <form action="{{ url('list-order') }}" id="order-search-form" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input class="form-control" name="order_id" id="order_id" type="text" placeholder="Order ID">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input class="form-control" name="user_phone" id="user_phone" type="text" placeholder="CLI">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">                            
                                                <select name="delivery_status" id="delivery_status" class="form-control">
                                                    <option value="">Status</option>  
                                                    <option value="1">To Activate</option>
                                                    <option value="2">Welcome Call</option> 
                                                    <option value="3">Not Packed</option> 
                                                    <option value="4">All</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="Date From" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="Date To" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="sim_number" id="sim_number" placeholder="Serial Number">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12">
                                                
                                                <button type="button" id="searchBtn" class="btn btn-primary ">Search</button>
                                                <button type="button" id="resetBtn" class="btn btn-secondary" >Reset</button>
                                                @if(Helper::has_permission('reports'))
                                                <button type="submit" class="btn btn-info pull-right" id="export" name="exportdata" value="1" style="display: inline-block; margin-left:10px;">Export</button>
                                                @endif
                                        </div>
                                        <!-- <div class="col-md-12">
                                            <label class="checkbox">
                                                <input type="checkbox" name="created_after" value="2019-11-01T13:52:25" checked=""> Only orders from the last 6 months
                                            </label>
                                        </div>
                                        <div class=" col-md-6">
                                            <div class="alert alert-success new-orderbutton" role="alert" style="display: inline-block; float: left; text-align: center;">
                                                <a href="#" style="color: #FFF;"><strong>New Order</strong></a>
                                            </div>                                   
                                        </div> -->
                                    </div>
                                </form>
                            </div>
                            <!-- <div style="width: 100%; float: left; overflow: scroll;"> -->
                            <table id="orderlist" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Order Date</th>
                                        <th>Customer</th>
                                        <th>Contact Number</th>
                                        <th>Shipping Date</th>
                                        <th>Sim</th>
                                        <th>Sim Number</th>                                               
                                        <th>Agent Name</th>                                               
                                        <th>Status</th>                                          
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <!-- </div> -->
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
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
<script type="text/javascript">
    $(document).ready(function(){
    $('#orderlist').DataTable({
        responsive: true,
        "bSort" : true,
        pageLength: 25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        "ajax": {
            "url": "list-order",
            "data": function ( d ) {
                d.order_id = $('#order_id').val();
                d.user_phone = $('#user_phone').val();
                d.delivery_status = $('#delivery_status').val();
                d.from_date = $('#from_date').val();
                d.to_date = $('#to_date').val();
                d.sim_number = $('#sim_number').val();
            }
        },
        
        "dataType": "jsonp",
        "columns": [
        {"data": "order_id", "name": "rq.order_id"},
        {"data" : "date","name" : "date", "searchable": false},
        {"data": "name", "name": "usr.name"},
        {"data" : "phone", "name": "usr.phone" },
        {"data" : "ship_date","name" : "ship_date", "searchable": false},
        {"data" : "sim_count","name":"sim_count", "orderable": false, "searchable": false},
        {"data" : "sim_number","name" : "sim_number", "orderable": true, "searchable": false},
        {"data" : "promocode","name" : "rq.promocode"},
        {"data" : "delivery_status","name" : "delivery_status"},
        ],
        'order':[[1, 'desc']],
        "columnDefs": [
        { "targets": 1,"width": '80px'},
        {"defaultContent": "-","targets": "_all"}
        ],
    });
    $('#searchBtn').on('click', function(e) {
        e.preventDefault();
        $('#orderlist').DataTable().draw();                        
    });

    $('#resetBtn').on('click', function(e) {
        $('#order-search-form')[0].reset();
        $('#orderlist').DataTable().draw();                          
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