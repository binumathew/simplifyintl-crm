@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
        <div class="wrapper">
            <div class="container-fluid">
                <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
                <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
                <link href="{{ asset('plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="btn-group pull-right">
                                <ol class="breadcrumb hide-phone p-0 m-0">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">User Invoice Details</li>
                                </ol>
                            </div>
                            <h4 class="page-title">User Invoice Details</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('list-invoice') }}" id="invoice-search-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Order ID</label>
                                                <input class="form-control" name="order_id" type="text" placeholder="Order ID">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>CLI</label>
                                                <input class="form-control" name="phone" type="text" placeholder="eg.447676...">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Date From</label>
                                                <input type="text" class="form-control datepicker" name="from" id="from" placeholder="Date From" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Date To</label>
                                                <input type="text" class="form-control datepicker" name="to" id="to" placeholder="Date To" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="payment_status" class="form-control custom-select">
                                                    <option value="">All</option>
                                                    <option value="0">Not Processed</option>
                                                    <option value="1">Paid</option>
                                                    <option value="2">Partially Paid</option>
                                                    <option value="3">Unpaid</option>
                                                    <option value="4">Cancel</option>
                                                    <option value="5">Pending</option>
                                                    <option value="6">Refund</option>
                                                    <option value="7">Dispute</option>
                                                    <option value="8">Failed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Currency</label>
                                                <select name="currency" class="form-control custom-select">
                                                    <option value="GBP" selected>GBP</option>
                                                    <option value="USD">USD</option>
                                                    <option value="EUR">EUR</option>
                                                    <option value="AUD">AUD</option>
                                                </select>
                                            </div>
                                        </div> -->
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
                                            @if(Helper::has_permission('user_invoice'))
                                            <button type="submit" class="btn btn-info" id="export" name="exportdata" value="1">Export</button>
                                            @endif
                                            <button type="button" id="searchBtn" class="btn btn-primary pull-right" style="display: inline-block; float: right; text-align: center;">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary"  style="display: inline-block; float: right; text-align: center; background: #90a4ae; border-color:#90a4ae; margin-right:10px;" >Reset</button>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                                <table id="payment-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Billing Name</th>
                                            <th>Phone</th>
                                            <th>Sub Total</th>
                                            <th>Tax</th>
                                            <th>Total</th>
                                            <th>Credit Applied</th>
                                            <th>Amount Due</th>
                                            <th>Invoice Date</th>
                                            <th>Payment Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                            <th>PDF</th>
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

                <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
                <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
                <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
                <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
                <script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
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
                        url: 'list-invoice',
                        data: function (d) {
                            d.order_id = $('input[name=order_id]').val();
                            d.phone = $('input[name=phone]').val();
                            d.from = $('input[name=from]').val();
                            d.to = $('input[name=to]').val();
                            d.method = $('select[name=payment_method]').val();
                            d.currency = $('select[name=currency]').val();
                            d.payment_status = $('select[name=payment_status]').val();
                        }
                    },
                    // drawCallback:function(settings)
                    // {
                    //     $('#preloader').hide();
                    //     $('#total_amount').html(settings.json.currency + settings.json.total_sum);
                    //     $('#total_buy_amount').html(settings.json.currency + settings.json.total_buy);
                    //     $('#total_refund').html(settings.json.currency + settings.json.refund);
                    //     $('#total_profit').html(settings.json.currency + (settings.json.total_sum - settings.json.total_buy).toFixed(2));
                    // },
                    "dataType": "jsonp",
                    "columns": [
                        {"data": "order_id", "name": "rq.order_id"},
                        {"data": "name", "name": "usr.name"},
                        {"data": "phone", "name": "usr.phone"},

                        // {"data" : function(data){
                        //     return data.currency_symbol+data.buy_price;
                        // },"name":"buy_price"},
                        {"data" : "sub_total", "name":"sub_total"},
                        {"data" : "tax","name":"tax"},
                        {"data" : "total","name":"total"},
                        {"data" : "credits_applied","name":"credits_applied"},
                        {"data" : "amount_due","name":"amount_due"},
                        {"data" : function(data){
                            return '<span class="font-600 text-muted">'+data.date+'</span>' ;
                        },"name":"date"},
                        {"data" : function (data) {
                            return data.paid_at;
                        },"name":"paid_at"},
                        {"data": function(data){
                            switch(parseInt(data.status)){
                                case 0:
                                return '<span class="badge badge-danger">Not Processed</span>';
                                break;
                                case 1:
                                return '<span class="badge badge-success">Paid</span>';
                                break;
                                case 2:
                                return '<span class="badge badge-warning">Partially Paid</span>';
                                break;
                                case 3:
                                return '<span class="badge badge-danger">Unpaid</span>';
                                break;
                                case 4:
                                return '<span class="badge badge-primary">Cancel</span>';
                                break;
                                case 5:
                                return '<span class="badge badge-info">Pending</span>';
                                break;
                                case 6:
                                return '<span class="badge badge-warning">Refund</span>';
                                case 7:
                                return '<span class="badge badge-warning">Dispute</span>';
                                case 8:
                                var payelem = "";
                                if(data.payment_requests_count > 0){
                                    payelem = '<br><span>link sent count - '+data.payment_requests_count+'</span><br><span>Last payment link sent on - '+data.last_payment_request_at+' </span>';
                                }else{
                                    payelem = '<br><span>link sent count - '+data.payment_requests_count+'</span>';
                                }
                                return '<span class="badge badge-danger" title="'+data.failed_desc+'">Failed <i class="mdi mdi-information"></i></span>'+payelem;
                                break;
                            }
                        },"name":"status"},
                        {"data": "action", "name": "action",'orderable':false,'searchable':false},
                        {
                            "data": "downloadurl",
                            "render": function(data, type, row, meta){
                                data = '<a href="'+base_url+'/generate-invoices/'+data+'"><button class="btn btn-info btn-sm" >Download</button></a>'
                                return data;
                            }
                        },
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
                
                $(document).on('click', '.pay_link', function(e) {
                    e.preventDefault();
                    var $this = $(this);
                    alertify.confirm('Send Payment Link', 'Are you sure to send payment link?',
                    function(){
                        var inv_id = $this.attr('data-id');
                        $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: base_url+'/invoice-pay-link',
                        data: {inv_id:inv_id},
                        success:function(data){
                            if (data.status == 200) {
                                alertify.success(data.message);
                            } else {
                                alertify.error(data.message);
                            }
                            table.draw();
                        }
                    });
                    },function(){ });
                });
            });
        </script>
@endsection
