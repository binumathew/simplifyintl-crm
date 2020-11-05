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
                                    <li class="breadcrumb-item active">Sim Stock</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Sim Stock</h4>
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
                            <p class=" mb-0 m-t-10 text-muted">Revenue<span class="pull-right"><i class="fa fa-caret-down text-danger m-r-5"></i>3.25%</span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-success mr-0 float-right"><i class="mdi mdi-currency-gbp"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="total_buy_amount">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Expense<span class="pull-right"><i class="fa fa-caret-up text-success m-r-5"></i>8.51%</span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-danger mr-0 float-right"><i class="mdi mdi-rotate-right"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="total_refund">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Refunds <span class="pull-right">
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
                            <p class=" mb-0 m-t-10 text-muted">Profit <span class="pull-right"><i class="fa fa-caret-down text-danger m-r-5"></i>5.52%</span></p>
                        </div>
                    </div>                    
                </div>
                @endif
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('/stock-list-item') }}" id="stock-list" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Phone Number</label>
                                                <input class="form-control" id="phone_number" name="phone_number" type="text" placeholder="Phone Number">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Sim Number</label>
                                                <input class="form-control" id="sim_number" name="sim_number" type="text" placeholder="Sim Number">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Dealer</label>
                                                <select name="dealer" id="dealer" class="form-control">
                                                    <option value="">All</option>  
                                                    @foreach($dealers as $dealer)
                                                    <option value="{{ $dealer->id }}">{{ $dealer->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" id="sim_status" class="form-control">
                                                    <option value="" selected>All</option>
                                                    <option value="1">Active</option>  
                                                    <option value="0">Sold</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class=" col-md-12">
                                            @if(Helper::has_permission('report'))
                                            <button type="submit" class="btn btn-info" id="export" name="exportdata" value="1">Export</button>
                                            @endif
                                            @if(Helper::has_permission('stock','create'))
                                            <a href="javascript:void(0);" class="btn btn-warning" id="import-stock" style="margin-left: 10px;">Import</a>
                                            @endif                                            
                                            <button type="button" id="searchBtn" class="btn btn-primary pull-right">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary pull-right" style="margin-right: 10px;">Reset</button>
                                        </div>
                                    </div>
                                    </form>
                                </div>                                
                                <table id="stockList" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Phone Number</th>
                                            <th>Sim Number</th>
                                            <th>Provider</th>
                                            <th>Category</th>
                                            <th>Dealer</th>
                                            <th>Price</th>
                                            <th>Box Number</th>                                            
                                            <th>Status</th>                         
                                            <th>Action</th>
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
            </div>
        </div>
        <!-- page wrapper end -->

        <script type="text/javascript">
            $(document).ready(function(){
                $('#stockList').DataTable({
                    responsive: true,
                    bSort : true,
                    pageLength: 25,
                    language: { search: '' },
                    processing: true,
                    serverSide: true,
                    ajax: {
                        'url': 'stock-list-item',
                        
                        'data': function ( d ) {
                            d.phone_number = $('#phone_number').val();
                            d.sim_number = $('#sim_number').val();
                            d.dealer = $('#dealer').val();
                            d.status = $('#sim_status').val();
                        }
                    },
                    
                    'dataType': 'jsonp',
                    'columns': [
                        {"data": function ( data ) {                
                            return (data.verified == '1')?data.phone_number:'44759xxxxxxx';
                        }, "name": "ts.phone_number"},
                        {"data": "sim_number", "name": "ts.sim_number"},
                        {"data": "provider", "name": "ts.provider"},
                        {"data": "category", "name": "ts.category"},
                        {"data": "fullname", "name": "a.first_name"},
                        {"data": "price", "name": "ts.price"},
                        {"data": "box_no", "name": "ts.box_no"},            
                        {"data": function ( data ) {                
                            return (data.status == '1')?'<span class="badge badge-success">Active</span>':'<span class="badge badge-danger">Sold</span>';
                        }, "name": "ts.status"},
                        {"data": function ( data ) {
                            // var edit_stock = base_url+'/edit-stock/'+ data.id;
                            return '<a title="Edit"  href="javascript:void(0);" class="text-muted edit_stock_detail" data-id="'+ data.id +'"><i class="mdi mdi-eye mdi-24px"></i></a>';
                        }, "name": "action","orderable": false, "searchable": false},
                    ]                    
                });

                $('.dataTables_filter input').attr('placeholder', 'Search');

                $('#searchBtn').on('click', function(e) {
                    $('#stockList').DataTable().draw();                    
                });

                $('#resetBtn').on('click', function(e) {
                   $('#stock-list')[0].reset();
                });

                $(document).on('click', '.show_user_data', function(e) {              
                    // e.preventDefault();                    
                    // $(this).parents('form').submit();
                });

                $(document).on('click', '.edit_stock_detail', function(e) { 
                    var stock_id = $(this).data('id');
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',                                                
                        url: base_url+'/edit-stock',
                        data: {stock_id:stock_id},
                        success:function(data){ 
                            $('#orderCustomLabel').text('Stock Details');
                            $('#orderCustombody').html(data.html); 
                            $('#orderCustomModal').modal('show');
                        }
                    });                    
                });

                $(document).on('click', '.stock_manage', function(e) {
                    var $this    = $(this); 
                    var formData = new FormData($('#stock-details')[0]);
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',                                                
                        url: base_url+'/manage-stock',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        beforeSend: function(){
                              $this.html('Processing..');
                        },
                        complete: function(){
                              $this.html('update');
                        },
                        success:function(data){ 
                            if(data.error){
                                alertify.error(data.msg);
                            }else{
                                alertify.success(data.msg);
                                $('#orderCustomModal').modal('hide');
                                $('#stockList').DataTable().draw();
                            }
                            
                        }
                    });                    
                });    
                
                $(document).on('click', '#import-stock', function(e) {                     
                    $('#orderCustomLabel').text('Import Stock Item');
                    $('#orderCustombody').html('<form id="import-form" method="post" enctype="multipart/form-data">@csrf<div class="form-group"><label>Import Stock</label><div class="bootstrap-filestyle input-group"><input type="file" id="importfile" name="stock_list" style="position: absolute; clip: rect(0px, 0px, 0px, 0px);" accept=".csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"><input type="text" class="form-control importfile" disabled><span class="group-span-filestyle input-group-append" tabindex="0"><label for="importfile" class="btn btn-secondary"><span class="icon-span-filestyle fa fa-folder-open"></span><span class="buttonText">Choose file</span></label> </span></div><div id="custom_status"></div></div> <button type="button" class="btn btn-secondary" data-dismiss="modal"> Close </button><button type="button" id="import-sim-stock" class="btn btn-success pull-right">Import</button></form>'); 
                    $('#orderCustomModal').modal('show');                    
                });

                $(document).on('change','#importfile', function() {
                    var fileName = $(this).val().split("\\").pop();
                    $(this).siblings(".importfile").attr('placeholder',fileName);
                });

                $(document).on('click','#import-sim-stock', function() {
                    var formData = new FormData($('#import-form')[0]);
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',                                                
                        url: base_url+'/import-stock',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success:function(data){ 
                            if (data.error) {
                                $('#custom_status').html('<div class="text-danger">'+data.message+'</div>');
                            } else {
                                $('#import-form')[0].reset();
                                $('#custom_status').html('<div class="text-success">Stock list successfully imported</div>');   
                            }
                        }
                    });
                });

                $('#stock-list').on('submit', function(e) {
                   $(this).submit();
                });
            });
        </script>
@endsection
