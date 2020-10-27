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
                                    <li class="breadcrumb-item active">Usage</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Usage</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('list-usage') }}" id="usage-search-form" method="POST">
                                        @csrf
                                    <div class="row">
                                    	<div class="col-md-4">
                                            <div class="form-group">
                                                <label>Number</label>
                                                <input class="form-control number" name="number" type="text" minlength="8" maxlength="15" placeholder="eg. 447877776776">
                                            </div>
                                        </div>
  										<div class="col-md-4">
                                            <div class="form-group">
                                                <label>Month</label>
                                               <select name="usage_month" id="usage_month" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                   	@php 
						                            $now   = Carbon::now()->format('Y-m-d');
						                            $month = strtotime($now);
						                            $currmonth = date('m', $month);
						                            @endphp
						                            @for($i=1; $i<=12; $i++)
						                            @php 
						                            $month_name = date('M', $month);
						                            $monthid = date('m', $month);
						                            $month  = strtotime('+1 month', $month);
						                            @endphp
                                                    <option {{ ($currmonth == $monthid) ? 'selected':'' }} value="{{$monthid}}">{{$month_name}}</option>
                            						@endfor  
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Year</label>
                                                @php $firstYear = (int)Carbon::now()->format('Y'); 
					                            $lastYear = $firstYear - 5;
					                            @endphp
					                            <select name="usage_year" id="usage_year" class="form-control custom-select">
					                            <option value="">Choose</option>
					                                @for($i=$firstYear;$i >= $lastYear;$i--)

					                                <option {{ ($i == $firstYear) ? 'selected':'' }} value="{{$i}}" >{{$i}}</option>
					                                @endfor
					                            </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="usage_status" id="usage_status" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">In Active</option>  
                                                </select>
                                            </div>
                                        </div> 
                                        <div class=" col-md-12">
                                            @if(Helper::has_permission('reports'))
                                            <button type="submit" class="btn btn-info pull-right" id="export" name="exportdata" value="1">Export</button>
                                            @endif
                                            <button type="button" id="searchBtn" class="btn btn-primary ">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                            
                                        </div>
                                    </div>
                                    </form>
                                </div>                                
                                <table id="usage-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Phone Number</th>                               
                                            <th>Email</th>
                                            <th>Plan</th>
                                            <th>Plan Type</th>
                                            <th>Call Duration</th>
                                            <th>Data Usage</th>
                                            <th>Sms Count</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Created At</th>
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
       $('#usage-search-form')[0].reset();
       $('#usage-table').DataTable().draw();       
    });

    var usageTable = $('#usage-table').DataTable({
        dom: 'Bfrtip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'list-usage',
            data: function (d) {
            	d.number = $('input[name=number]').val();
            	d.usage_month  = $("#usage_month").val();
            	d.usage_year   = $("#usage_year").val();
            	d.usage_status = $("#usage_status").val();
            },
        },
        drawCallback:function(settings)
        {
            $('#preloader').hide();
        },
        "dataType": "jsonp",
        "columns": [
            {"data": "name", "name": "name"},
            {"data": "phone", "name": "phone"},
            {"data" : "email", "name":"email"},                       
            {"data" : "plan","name":"plan"},
            {"data" : "plan_type","name":"plan_type"},
            {"data" : "call_usage","name":"call_usage"},
            {"data" : "data_usage","name":"data_usage"},
            {"data" : "sms_count","name":"sms_count"},
            {"data" : "service_total","name":"service_total"},
            {"data" : "status","name":"status"},
            {"data" : "created_at","name":"created_at"},
        ],
        "order":[[0, 'asc']],
        "columnDefs": [
            {"defaultContent": "-","targets": "_all"},
            {"targets": [1,2,3,5,6,7],"orderable": false}
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
        usageTable.draw();
        e.preventDefault();
    });
});
</script>
@endsection