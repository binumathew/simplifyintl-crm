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
                                    <li class="breadcrumb-item active">Clawback Plan List</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Clawback Plan List</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('clawback-plan-list') }}" id="clawback-plan-list-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Plan Type</label>
                                                <select name="plan_type" id="planType" class="form-control custom-select">
                                                    <option value="" selected disabled>Choose</option>
                                                    <option value="1">Plan</option>
                                                    <option value="2">Bundle</option>  
                                                </select>
                                            </div>
                                        </div> 
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Plan Name</label>
                                                <div class="input-group">
                                                    <select class="form-control" name="plan_id" id="planId" required>
                                                    <option value="">Choose</option>
                                                    </select>
                                                </div>
                                                <span></span>
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
                                    @if(Helper::has_permission('commission','create'))
                                    <a href="{{ url('/clawback-manage/plan')}}">
                                        <button type="button" class="btn btn-info waves-effect waves-light pull-right">Add Plan Clawback</button>
                                    </a>
                                    @endif
                                </div>                              
                                <table id="clawbackPlan-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Plan Type</th>
                                            <th>Plan Name</th>
                                            <th>Period (Months)</th>
                                            <th>Status</th>
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
<script>
$(document).ready(function(){

    $('#resetBtn').on('click', function(e) {
       $('#clawback-plan-list-form')[0].reset();
       $('#clawbackPlan-table').DataTable().draw();       
    });

    var clawbackplanTable = $('#clawbackPlan-table').DataTable({
        dom: 'Bfrltip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'clawback-list',
            data: function (d) {
                d.data_type = "plan";
            	d.plan_type = $("#planType").val();
                d.plan_id   = $("#planId").val();
            },
        },
        drawCallback:function(settings)
        {
            $('#preloader').hide();
        },
        "dataType": "jsonp",
        "columns": [
            {"data": "plan_type", "name": "plan_type"},                     
            {"data" : "plan_name","name":"plan_name"},
            {"data" : "period","name":"period"},
            {"data" : "status","name":"status"},
            {"data" : "created_at","name":"created_at"},
            {"data" : "actions","name":"actions"},
        ],
        "columnDefs": [
            //{"defaultContent": "-","targets": "_all"},
            {"targets": [0,1,2,3],"orderable": false}
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
        clawbackplanTable.draw();
        e.preventDefault();
    });
});
</script>
@endsection