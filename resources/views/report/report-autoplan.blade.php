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
                                    <li class="breadcrumb-item active">Auto Plan</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Auto Plan</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('list-autoplan') }}" id="autoplan-search-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Number</label>
                                                <input class="form-control number" name="number" type="text" minlength="8" maxlength="15">
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
                                                <label>Provider</label>
                                                <select name="provider" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                    @foreach($providers as $pkey => $plist)
                                                    <option value="{{ $plist->provider}}">{{ $plist->provider}}</option>
                                                    @endforeach
                                                    <option value="Bridge">Bridge</option>  
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Plan</label>
                                                <select name="plans" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                    @foreach($plans as $lkey => $list)
                                                    @php 
                                                    $type = ($list->provider == 'Bridge') ? 'Conference Plan-' : ""; 
                                                    $for = ($list->provider == 'Bridge') ? 'conf-'.$list->plan_name : 'plan-'.$list->id; 
                                                    @endphp
                                                    <option value="{{ $for }}">{{ $type.''.$list->plan_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                    <option value="1">Active</option>
                                                    <option value="0">In Active</option>  
                                                </select>
                                            </div>
                                        </div> 
                                        <div class=" col-md-12">
                                            <button type="button" id="searchBtn" class="btn btn-primary ">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                            @if(Helper::has_permission('reports'))
                                            <button type="submit" class="btn btn-info pull-right" id="export" name="exportdata" value="1">Export</button>
                                            @endif
                                            
                                            
                                        </div>
                                    </div>
                                    </form>
                                </div>                                
                                <table id="autoplan-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Phone Number</th>                               
                                            <th>Child Number</th>
                                            <th>Plan</th>
                                            <th>Plan Type</th>
                                            <th>Provider</th>
                                            <th>Gateway</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Next Renewal</th>
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
    $('.datepicker').datepicker({
        autoclose: true,
        orientation:'bottom left',
        format: 'yyyy-mm-dd',
        todayHighlight: true
    });

    $('#resetBtn').on('click', function(e) {
       $('#autoplan-search-form')[0].reset();
       $('#autoplan-table').DataTable().draw();
    });

    var autoplanTable = $('#autoplan-table').DataTable({
        dom: 'Bfrtip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'list-autoplan',
            data: function (d) {
                d.number = $('input[name=number]').val();
                d.from = $('input[name=from]').val();
                d.to = $('input[name=to]').val();
                d.provider = $('select[name=provider]').val();
                d.status = $('select[name=status]').val();
                d.plans = $('select[name=plans]').val();
            }
        },
        drawCallback:function(settings)
        {
            $('#preloader').hide();
        },
        "dataType": "jsonp",
        "columns": [
            {"data": "name", "name": "name"},
            {"data": "phone_number", "name": "phone_number"},
            {"data" : "child", "name":"child"},                       
            {"data" : "plan","name":"plan"},
            {"data" : "plan_type","name":"plan_type"},
            {"data" : "provider","name":"provider"},
            {"data" : "gateway","name":"gateway"},
            {"data" : "amount","name":"amount"},
            {"data" : "status","name":"status"},
            {"data" : "next_renewal","name":"next_renewal"},
            {"data" : "created_at","name":"created_at"},
        ],
        "order":[[0, 'asc']],
        "columnDefs": [
            {"defaultContent": "-","targets": "_all"},
            {"targets": [1,2,3,6,10],"orderable": false}
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
        autoplanTable.draw();
        e.preventDefault();
    });
});
</script>
@endsection