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
                                    <form action="{{ url('list-user') }}" id="user-search-form" method="POST">
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
                                                <label>Status</label>
                                                <select name="user_status" id="user_status" class="form-control custom-select">
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
                                <table id="user-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Phone Number</th>
                                            <th>Email</th>
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

                <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
                <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
                <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
                <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
                <script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
            </div>
        </div>
        <!-- page wrapper end -->
<script>
$(document).ready(function(){

    $('#resetBtn').on('click', function(e) {
       $('#user-search-form')[0].reset();
       $('#user-table').DataTable().draw();
    });

    var userTable = $('#user-table').DataTable({
        dom: 'Bfrtip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'list-user',
            data: function (d) {
            	d.number = $('input[name=number]').val();
            	d.user_status = $("#user_status").val();
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
            {"data" : "status","name":"status"},
            {"data" : "created_at","name":"created_at"},
        ],
        "order":[[0, 'asc']],
        "columnDefs": [
            {"defaultContent": "-","targets": "_all"},
            {"targets": [1,2],"orderable": false}
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
        userTable.draw();
        e.preventDefault();
    });
});
</script>
@endsection
