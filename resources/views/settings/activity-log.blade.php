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
                                    <li class="breadcrumb-item active">Activity Log</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Activity Log</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('activity-log') }}" id="activity-log-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Choose</label>
                                                <select name="admin_id" id="admin_id" class="form-control custom-select">
                                                    <option value="" disabled selected>Choose</option>
                                                    @if(!empty($adminlist))
                                                    @foreach($adminlist as $akey =>$alist)
                                                    <option value="{{ Crypt::encrypt($alist->id)}}">{{$alist->name}}</option>
                                                    @endforeach
                                                    @endif 
                                                </select>
                                            </div>
                                        </div>
                                        @if(!empty($userslist))
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Users</label>
                                                <select name="user_id" id="user_id" class="form-control custom-select">
                                                    <option value="" disabled selected>Choose</option>
                                                    @foreach($userslist as $ukey =>$ulist)
                                                    <option value="{{$ulist->id}}">{{$ulist->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endif 
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>From</label>
                                                <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="Date From" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>To</label>
                                                <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="Date To" autocomplete="off">
                                            </div>
                                        </div> 
                                        <div class=" col-md-12">
                                            <button type="button" id="searchBtn" class="btn btn-primary ">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                            
                                        </div>
                                    </div>
                                    </form>
                                </div>                                
                                <table id="activitylog-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>                               
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
       $('#activity-log-form')[0].reset();
       $('#activitylog-table').DataTable().draw();       
    });

    var actlogTable = $('#activitylog-table').DataTable({
        dom: 'Bfrtip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'list-activitylog',
            data: function (d) {
                d.adminid = $("#admin_id").val();
                d.userid = $("#user_id").val();
                d.from_date = $('#from_date').val();
                d.to_date = $('#to_date').val();
            },
        },
        drawCallback:function(settings)
        {
            $('#preloader').hide();
        },
        "dataType": "jsonp",
        "columns": [
            {"data": "name", "name": "name"},
            {"data": "description", "name": "description"},
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
        actlogTable.draw();
        e.preventDefault();
    });
    $('.datepicker').datepicker({
        autoclose: true,
        orientation:'bottom left',
        format: 'yyyy-mm-dd',
        todayHighlight: true
    });
});
</script>
@endsection

