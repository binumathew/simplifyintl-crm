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
                                    <li class="breadcrumb-item active">Plan Commission List</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Plan Commission List</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('comm-plan-list') }}" id="comm-plan-list-form" method="POST">
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
                                    <a href="{{ url('/commplan-manage')}}">
                                        <button type="button" class="btn btn-info waves-effect waves-light pull-right">Add Plan Commission</button>
                                    </a>
                                    @endif
                                </div>                              
                                <table id="commPlan-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Plan Type</th>
                                            <th>Plan Name</th>
                                            <!-- <th>Duration</th>
                                            <th>Commission Type</th>
                                            <th>Commission Rate</th>
                                            <th>Status</th> -->
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
        <div id="viewcommModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0">Plan Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" id="commviewbody"> 
                         
                    </div>
                </div>
            </div>
        </div>
        <!-- custom modal popup end -->
<script>
$(document).ready(function(){

    $('#resetBtn').on('click', function(e) {
       $('#comm-plan-list-form')[0].reset();
       $('#commPlan-table').DataTable().draw();       
    });

    var commplanTable = $('#commPlan-table').DataTable({
        dom: 'Bfrltip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'comm-plan-list',
            data: function (d) {
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
            // {"data" : "name","name":"name"},
            // {"data" : "comm_type","name":"comm_type"},
            // {"data" : "comm_rate","name":"comm_rate"},
            // {"data" : "status","name":"status"},
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
        commplanTable.draw();
        e.preventDefault();
    });
});
</script>
@endsection