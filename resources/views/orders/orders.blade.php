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
                        <h4 class="page-title">Orders Awaiting Activation</h4>
                    </div>
                </div>
            </div>
            @if(Helper::has_permission('reports'))
            <div class="row">
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-primary mr-0 float-right"><i class="mdi mdi-cart-outline"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0">£0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Total Orders <span class="pull-right"><i class="fa fa-caret-down text-danger m-r-5"></i>3.25%</span></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-success mr-0 float-right"><i class="mdi mdi-currency-gbp"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0">£0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Successful Orders <span class="pull-right"><i class="fa fa-caret-up text-success m-r-5"></i>8.51%</span></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-warning mr-0 float-right"><i class="mdi mdi-fingerprint"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0">£0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Refunds <span class="pull-right"><i class="fa fa-caret-down text-danger m-r-5"></i>5.52%</span></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-danger mr-0 float-right"><i class="mdi mdi-rotate-right"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0">£0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Chargebacks <span class="pull-right"><i class="fa fa-caret-up text-success m-r-5"></i>7.10%</span></p>
                    </div>
                </div>
            </div>
            @endif
            <div class="row">
                <div class="col-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div class="order-search">
                                <form action="{{ url('payment-pagination') }}" id="order-search-form" method="POST">
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
                                                    <option value="1" selected>To Activate</option>
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
                                                <div class="btn btn-warning" style="display: inline-block; float: left; text-align: center;">
                                                    <a href="{{url('/new-order')}}" style="color: #FFF;">New Order</a>
                                                </div>
                                                @if(Helper::has_permission('reports'))
                                                <button type="submit" class="btn btn-info" id="export" name="exportdata" value="1" style="display: inline-block; margin-left:10px;">Export</button>
                                                @endif
                                                <button type="button" id="searchBtn" class="btn btn-primary pull-right" style="display: inline-block; float: right; text-align: center;">Search</button>
                                                <button type="button" id="resetBtn" class="btn btn-secondary" style="display: inline-block; float: right; text-align: center; background: #90a4ae; border-color:#90a4ae; margin-right:10px;">Reset</button>
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
                                        <th>Action</th>

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

            <div id="dealerCode" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title mt-0" id="myModalLabel">Manage Dealer Code</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body">
                            <form method="post">
                                @csrf<input type="hidden" id="orderidentifier" name="orderidentifier" value="">
                                <select name="promocode" class="form-control">
                                    <option selected>Promocode</option>
                                    @foreach($dealers as $dealer)
                                        <option value="{{$dealer->promocode}}">{{$dealer->promocode}}</option>
                                    @endforeach
                                </select> <br />                                   
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" id="update_promo_code" class="btn btn-success pull-right">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div id="enquiry-history"></div>

            <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

            
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#orderlist').DataTable({
                        responsive: true,
                        "bSort" : true,
                        pageLength: 25,
                        language: { search: "" },
                        processing: true,
                        serverSide: true,
                        "ajax": {
                            "url": "orders-list",
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
                        {"data" : function (data) {
                            if (data.delivery_status == 0) {
                                return 'Order Received';
                            } else if (data.delivery_status == 1) {
                                return 'Ready To Activate';
                            } else if (data.delivery_status == 2) {
                                return 'CallBack Pending';
                            } else if (data.delivery_status == 3) {
                                return 'Activated';
                            }
                        }, "name": "rq.delivery_status", "searchable": false},
                        {"data": function(data){
                            var route = "{{URL::to('/order-details')}}";               
                            var html = '<form method="post" id="view_order_'+data.id+'" action="'+route+'">@csrf<input type="hidden" name="order_id" value="'+data.order_id+'"></form><a data-toggle="tooltip" title="View Details" href="javascript:void(0);" class="view_order_details text-muted m-r-10" data-id="'+data.id+'"><i class="mdi mdi-eye mdi-24px"></i></a><a data-toggle="tooltip" title="Enquiry History" href="javascript:void(0);" class="enquiry_history text-muted m-r-10" data-id="'+data.id+'"><i class="mdi mdi-comment-text mdi-24px"></i></a><a href="javascript:void(0);" class="manage_promocode text-muted m-r-10" data-toggle="tooltip" data-placement="top" title="Edit Promocode" data-id="'+data.id+'"><i class="mdi mdi-account mdi-24px"></i></a>';
                            return  html;
                        }, "name": "action","orderable": false, "searchable": false},
                        ],
                        'order':[[1, 'desc']],
                        "columnDefs": [
                        { "targets": 1,"width": '80px'},
                        {"defaultContent": "-","targets": "_all"}
                        ],
                    });

                    $('.dataTables_filter input').attr("placeholder", "Search");

                    $('#searchBtn').on('click', function(e) {
                        e.preventDefault();
                        $('#orderlist').DataTable().draw();                        
                    });

                    $('#delivery_status').on('change', function(e) {
                        $('#orderlist').DataTable().draw();
                    });

                    $('#resetBtn').on('click', function(e) {
                        $('#order-search-form')[0].reset();
                        $('#orderlist').DataTable().draw();                          
                    });

                    // $('#order-search-form').on('submit', function(e) {
                    //    $(this).submit();
                    // });

                    $(document).on('click', '.view_order_details', function(e) {              
                        e.preventDefault();   
                        var id = $(this).attr('data-id');                
                        $('#view_order_'+id).submit();
                    });

                    $(document).on('click','.manage_promocode',function (e) {
                        var id = $(this).attr('data-id');   
                        $('#orderidentifier').val(id);    
                        $('#dealerCode').modal('show');                        
                    });
                    
                    $(document).on('click','#update_promo_code',function (e) {  
                        var order_id = $('#orderidentifier').val();
                        var promocode = $('select[name="promocode"]').val(); 
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',                                                
                            url: base_url+'/update-promocode',
                            data: {order_id:order_id,promocode:promocode},
                            success:function(data){                 
                                $('#dealerCode').modal('hide'); 
                                $('#orderlist').DataTable().draw(); 
                            }
                        });
                    });

                    $(document).on('click','.enquiry_history',function (e) {
                        var id = $(this).attr('data-id');
                        $('#enquiry_status').html('');
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',                                                
                            url: base_url+'/enquiry',
                            data: {id:id},
                            success:function(data){ 
                                if (data.error) {
                                    $('#enquiry-history').html('<div class="alert alert-danger">'+data.message+'</div>');
                                } else {
                                    $('#enquiry-history').html(data.html);
                                }
                                $('#enquiryHistory').modal('show');
                            }
                        });
                    });

                    $(document).on('click','#save_enq_history',function (e) {       
                        var id = $('#enq_request_id').val();
                        var note = $('#enquiry_note').val();
                        $('#enquiry_status').html('');
                        if (note == "") {
                            $('#enquiry_status').html('<div class="text-danger">Please enter the note</div>');
                        } else {
                            $('#preloader').show();
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',                                                
                                url: base_url+'/save-enquiry',
                                data: {id:id,note:note},
                                success:function(data){ 
                                    $('#preloader').hide();
                                    if (data.error) {
                                        $('#enquiry_status').html('<div class="text-danger">'+data.message+'</div>');
                                    } else {
                                        $('#enquiry_status').html('<div class="text-success">Updated successfully</div>');
                                        $('#enquiryHistory').modal('hide');
                                    }
                                }
                            });
                        }
                    });

                    $('.datepicker').datepicker({
                        autoclose: true,
                        orientation:'bottom left',
                        format: 'yyyy-mm-dd',
                        todayHighlight: true
                    });
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection