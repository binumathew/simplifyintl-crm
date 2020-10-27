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
                                    <li class="breadcrumb-item active">Commission Payments List</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Commission Payments List</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('comm-payment-list') }}" id="comm-payment-list-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Name</label>
                                                 <select class="form-control" name="promocode" id="promocode" required>
                                                    <option value="">Choose</option>
                                                    @foreach ($dealer as $usr)
                                                        <option value="{{Crypt::encrypt($usr->id)}}">{{$usr->first_name.'-'.$usr->promocode}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div> 
                                        <div class=" col-md-12">
                                            <button type="button" id="searchBtn" class="btn btn-primary ">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                            @if(Helper::has_permission('commission','create'))
                                            @if((in_array(Auth::user()->roles->short_code,['ADMIN'])) || $haschild)
                                            <button type="button" class="btn btn-info waves-effect waves-light pull-right" id="paycommModal">Pay Commission</button>
                                            @endif
                                            @endif
                                        </div>
                                    </div>
                                    </form>
                                </div>                             
                                <table id="commPayment-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Promocode</th>
                                            <th>Total Commission</th>
                                            <th>Total commission Paid</th>
                                            <th>Advance Balance</th>
                                            <!-- <th>This month Commission</th> -->
                                            <!-- <th>This month Not Paid</th> -->
                                            <th>Amount to Pay</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                        
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
        <!-- custom modal popup start -->
        <div id="viewpaycommModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0">Commission Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" id="paycommviewbody"> 
                    <form id="new-paycomm-form" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Choose Dealer</label>
                            <select id="pay_dealer" class="form-control" name="pay_dealer">
                                <option value="">Choose</option>
                                @foreach($paycomm as $list)
                                    <option value="{{Crypt::encrypt($list->id)}}">{{$list->first_name}} {{$list->last_name}} ({{$list->promocode}})</option>
                                @endforeach
                            </select>
                            <span></span>
                        </div>  
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="text" id="pay_amount" class="form-control" name="pay_amount" required maxlength="10">
                            <span></span>
                        </div>
                        <input type="hidden" name="type" value="dealer">
                        <button type="submit" class="btn btn-primary" id="payCommission">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </form>   
                    </div>
                </div>
            </div>
        </div>
        <!-- custom modal popup end -->
<script>
$(document).ready(function(){

    $('#resetBtn').on('click', function(e) {
       $('#comm-payment-list-form')[0].reset();
       $('#commPayment-table').DataTable().draw();       
    });

    var commpaymentTable = $('#commPayment-table').DataTable({
        dom: 'Bfrltip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:10,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        lengthMenu: [10, 25, 50, 100,500,1000],
        ajax: {
            url: 'comm-payment-list',
            data: function (d) {
                d.promocode = $("#promocode").val();
            },
        },
        drawCallback:function(settings)
        {
            $('#preloader').hide();
        },
        "dataType": "jsonp",
        "columns": [
            {data: 'name', name: 'name'},
            {data: 'promocode', name: 'promocode'},
            {data: 'total', name: 'total'},
            {data: 'totalpaid', name: 'totalpaid'},
            {data: 'advancebal', name: 'advancebal'},
            // {data: 'monthcomm', name: 'monthcomm'},
            // {data: 'monthnotpaid', name: 'monthnotpaid'},
            {data: 'topay', name: 'topay'},
            {"data": function(data){
            var route = "{{URL::to('comm-user')}}";
            var html = '<form method="post" action="'+route+'">@csrf<input type="hidden" name="user_id" value="'+data.userid+'"><button type="submit" class="btn btn-default btn-xs" title="View Details"><i class="mdi mdi-eye font-18"></i></button></form>';
            return  html;
            }, "name": "action"},
        ],
        "columnDefs": [
            //{"defaultContent": "-","targets": "_all"},
            {"targets": [0,1,2,3,4,5,6],"orderable": false}
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

    $('#searchBtn').on('click', function(e) {
        commpaymentTable.draw();
        e.preventDefault();
    });
    $(document).on("click","#paycommModal", function () {
        $('#new-paycomm-form')[0].reset();
        $("#viewpaycommModal").modal('show');
    });

});
</script>
@endsection