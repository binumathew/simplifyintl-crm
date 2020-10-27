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
                                    <li class="breadcrumb-item active">Sim Plans</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Sim Plans</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('sim-plans-list') }}" id="sim-plans-list-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Dealer</label>
                                                <select name="dealer_id" class="form-control custom-select" required id="dealer_id">
                                                    <option value="">Choose</option>
                                                    @foreach($dealer as $skey =>$slist)
                                                    <option value="{{ Crypt::encrypt($slist->id) }}">{{ $slist->first_name.''.$slist->last_name.' - '.$slist->promocode }}</option> 
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div> 
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="plan_status" id="plan_status" class="form-control custom-select">
                                                    <option value="" selected disabled>Choose</option>
                                                    <option value="1">Active</option>
                                                    <option value="0">In Active</option>  
                                                </select>
                                            </div>
                                        </div> 
                                        <div class=" col-md-12">
                                            <button type="button" id="searchBtn" class="btn btn-primary ">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                        </div>
                                    </div>
                                    </form>
                                </div>  
                                <div class="col-md-12">
                                    @if(Helper::has_permission('plan_management','create'))
                                    <a href="{{ url('/sim-plans-manage')}}">
                                        <button type="button" class="btn btn-info waves-effect waves-light pull-right">Add Plan</button>
                                    </a>
                                    @endif
                                </div>                              
                                <table id="simPlans-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Sell Price</th>
                                            <th>Data Limit</th>
                                            <th>In call Limit</th>
                                            <th>Period</th>
                                            <th>Status</th>
                                            <th>Order by</th>
                                            <th>Provider</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
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
        <div id="viewplanModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0">Plan Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" id="planviewbody"> 
                         
                    </div>
                </div>
            </div>
        </div>
        <!-- custom modal popup end -->
<script>
$(document).ready(function(){

    $('#resetBtn').on('click', function(e) {
       $('#sim-plans-list-form')[0].reset();
       $('#simPlans-table').DataTable().draw();       
    });

    var simplanTable = $('#simPlans-table').DataTable({
        dom: 'Bfrltip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'sim-plans-list',
            data: function (d) {
            	d.plan_status = $("#plan_status").val();
                d.dealer_id  = $("#dealer_id").val();
            },
        },
        drawCallback:function(settings)
        {
            $('#preloader').hide();
        },
        "dataType": "jsonp",
        "columns": [
            {"data": "plan_name", "name": "plan_name"},                     
            {"data" : "sell_price","name":"sell_price"},
            {"data" : "data_limit","name":"data_limit"},
            {"data" : "in_call_limit","name":"in_call_limit"},
            {"data" : "period","name":"period"},
            {"data" : "status","name":"status"},
            {"data" : "order_by","name":"order_by"},
            {"data" : "provider","name":"provider"},
            {"data" : "created_at","name":"created_at"},
            {"data" : "actions","name":"actions"},
        ],
        "columnDefs": [
            //{"defaultContent": "-","targets": "_all"},
            {"targets": [1,2,3,9],"orderable": false}
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
        simplanTable.draw();
        e.preventDefault();
    });
});
</script>
@endsection