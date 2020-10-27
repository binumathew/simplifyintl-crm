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
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">Payment History</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Payment History</h4>
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
                                    <form action="{{ url('payment-pagination') }}" id="payment-search-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Transaction ID</label>
                                                <input class="form-control" name="transaction_id" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>CLI</label>
                                                <input class="form-control" name="user_cli" type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Payment Method</label>
                                                <select name="payment_method" class="form-control custom-select">
                                                    <option value="0">All</option>  
                                                    <option value="1">Paypal</option>
                                                    <option value="2">Braintree</option> 
                                                    <option value="3">Bank Transfer</option> 
                                                    <option value="4">Direct Cash</option>
                                                    <option value="5">App Payment</option> 
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Date From</label>
                                                <input type="text" class="form-control datepicker" name="from" id="from" placeholder="Date From" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Date To</label>
                                                <input type="text" class="form-control datepicker" name="to" id="to" placeholder="Date To" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Currency</label>
                                                <select name="currency" class="form-control custom-select">
                                                    <option value="£" selected>GBP</option>
                                                    <option value="$">USD</option>  
                                                    <option value="€">EUR</option>
                                                </select>
                                            </div>
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
                                <table id="payment-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Billing Name</th>
                                            <th>Transaction ID</th>                               
                                            <th>Amount</th>
                                            <th>Description</th>
                                            <th>Card Detail</th>
                                            <th>Gateway</th>
                                            <th>Payment Date</th>
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
                                        <td>Jul 20, 2020</td> rcly3165-->
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
                var table = $('#payment-table').DataTable({
                    dom: 'Bfrtip', //Bfrtip
                    responsive: true,
                    bSort : true,
                    pageLength:25,
                    language: { search: "" },
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: 'payment-pagination',
                        data: function (d) {
                            d.transaction_id = $('input[name=transaction_id]').val();
                            d.phone = $('input[name=user_cli]').val();
                            d.from = $('input[name=from]').val();
                            d.to = $('input[name=to]').val();
                            d.method = $('select[name=payment_method]').val();
                            d.currency = $('select[name=currency]').val();
                        }
                    },
                    drawCallback:function(settings)
                    {
                        $('#preloader').hide();
                        $('#total_amount').html(settings.json.currency + settings.json.total_sum);
                        $('#total_buy_amount').html(settings.json.currency + settings.json.total_buy);
                        $('#total_refund').html(settings.json.currency + settings.json.refund);
                        $('#total_profit').html(settings.json.currency + (settings.json.total_sum - settings.json.total_buy).toFixed(2));
                    },
                    "dataType": "jsonp",
                    "columns": [
                        {"data": "name", "name": "usr.name"},
                        {"data" : function(data){
                            return '<span class="font-600 text-muted">'+data.transaction_id+'</span>' ;
                        },"name":"transaction_id"}, 

                        // {"data" : function(data){
                        //     return data.currency_symbol+data.buy_price; 
                        // },"name":"buy_price"},
                        {"data" : "total_amount", "name":"total_amount"},                       
                        {"data" : "description","name":"description"},
                        {"data" : "card_type","name":"card_type"},
                        {"data" : "payment_method","name":"payment_method"},
                        {"data" : function (data) {
                            return data.created_at;
                        },"name":"user_payments.created_at"},
                        {"data": function(data){
                            switch(parseInt(data.status)){
                                case 0:
                                return '<span class="badge badge-danger">Failed</span>';
                                break;
                                case 1:
                                return '<span class="badge badge-success">Success</span>';
                                break;
                                case 2:
                                return '<span class="badge badge-danger">Success/Error</span>';
                                break;
                                case 3:
                                return '<span class="badge badge-warning">Refund</span>';
                                break;
                                case 4:
                                return '<span class="badge badge-primary">Deduct</span>';
                                break;
                            }
                        },"name":"status"},
                        {"data": "action", "name": "action",'orderable':false,'searchable':false},
                    ],
                    createdRow: function(row, data, index) {
                        $('td', row).eq(2).addClass('text-right');
                    },
                    "order":[[6, 'desc']],
                    "columnDefs": [
                        {"defaultContent": "-","targets": "_all"}
                    ],
                    buttons: [
                    // {
                    //  extend: 'excel',
                    //  text: 'Export',
                    //  className: 'btn-primary',
                    //  exportOptions: {
                    //      orthogonal: null
                    //  }
                    // },
                    ],

                });

                $('.dataTables_filter input').attr("placeholder", "Search");

                $('#searchBtn').on('click', function(e) {
                    table.draw();
                    e.preventDefault();
                });

                $(document).on('click', '.show_user_data', function(e) {              
                    e.preventDefault();                    
                    $(this).parents('form').submit();
                });
                
                $(document).on('click', '.action_refund', function(e) {              
                    e.preventDefault();                    
                    var tx_id = $(this).data('id');
                    var amount = $(this).data('amount');
                    var currency = $(this).data('currency');
                    $('#orderCustomLabel').text('Refund Transaction');
                    $('#orderCustombody').html('<label class="form-label">Amount</label> <div class="input-group mb-3"> <input type="hidden" name="txn_id" id="txn_id" value="'+ tx_id +'"> <div class="input-group-prepend"><span class="input-group-text">'+ currency +'</span></div> <input type="text" name="refund_amount" id="refund_amount" class="form-control" required placeholder="Amount" value="'+ amount +'" max="'+ amount +'"></div><div class="form-group"><label class="form-label">Description</label> <input type="text" name="description" id="description" class="form-control" required placeholder="Description"> <div id="custom_status"></div> </div> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="action_refund_process" class="btn btn-danger pull-right">Refund</button>'); 
                    $('#orderCustomModal').modal('show');
                });
                
                $(document).on('click', '#action_refund_process', function(e) {              
                    e.preventDefault();
                    $(this).attr('disabled','true');
                    var txn_id = $('#txn_id').val();
                    var amount = $('#refund_amount').val();
                    var description = $('#description').val();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',                                                
                        url: base_url+'/refund-process',
                        data: {txn_id:txn_id, amount:amount, description:description},
                        success:function(data){ 
                            if (data.error) {
                                $('#custom_status').html('<div class="text-danger">'+data.message+'</div>');
                            } else {
                                $('#custom_status').html('<div class="text-success">Refund Processed successfully</div>');
                                table.draw();
                            }
                            $('#action_refund_process').attr('disabled', false);
                            
                        }
                    });
                });

                $('#resetBtn').on('click', function(e) {
                   $('#payment-search-form')[0].reset();
                });
                $('#payment-search-form').on('submit', function(e) {
                   $(this).submit();
                });

                $('.datepicker').datepicker({
                    autoclose: true,
                    orientation:'bottom left',
                    format: 'yyyy-mm-dd',
                    todayHighlight: true
                });

                setTimeout(function() {
                    $('#success').fadeOut('fast');
                }, 5000);
            });
        </script>
@endsection
