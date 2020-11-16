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
                                    <li class="breadcrumb-item active">User Services</li>
                                </ol>
                            </div>
                            <h4 class="page-title">User Services</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('user-services') }}" id="services-search-form" method="POST">
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
                                                <select name="opted_status" id="opted_status" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                    <option value="0">Request Received</option>
                                                    <option value="1">Processed</option>
                                                    <option value="2">Failed </option>
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
                                <table id="services-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Phone Number</th>
                                            <th>Email</th>
                                            <th>Provider</th>
                                            <th>Service</th>
                                            <th>Requested</th>
                                            <th>Status</th>
                                            <th>Description</th>
                                            <th>Done By</th>
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
       $('#services-search-form')[0].reset();
       $('#services-table').DataTable().draw();
    });

    var servicesTable = $('#services-table').DataTable({
        dom: 'Bfrtip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'user-services',
            data: function (d) {
            	d.number = $('input[name=number]').val();
            	d.opted_status = $("#opted_status").val();
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
            {"data" : "provider", "name":"provider"},
            {"data" : "service_name", "name":"service_name"},
            {"data" : "opted_value", "name":"opted_value"},
            {"data" : "opted_status","name":"opted_status"},
            {"data" : "description","name":"description"},
            {"data" : "doneby","name":"doneby"},
            {"data" : "created_at","name":"created_at"},
            {"data" : "action","name":"action"},
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
        servicesTable.draw();
        e.preventDefault();
    });
    $(document).on("click", ".service_actions", function () {
      var $this   = $(this);
      var dataid  = $(this).attr('data-opted');
      var datatag = $(this).attr('data-tag');
      var datauser = $(this).attr('data-user');
      var reqtype  = $(this).attr('data-type');
      alertify.confirm('Service Confirmation', 'Are you sure to '+datatag+' ?',
        function() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: base_url+'/services-change',
                data: {dataid:dataid,user_id:datauser,requesttype:reqtype},
                beforeSend: function(){
                    $this.html('changing..');
                },
                complete: function(){
                    $this.html(datatag);
                },
                success:function(data){
                  if (data.status == 200) {
                      alertify.success(data.message);
                  }else{
                     alertify.error(data.message);
                  }
                  $('#services-table').DataTable().draw();
                }
            });
        },function(){ alertify.error('Cancel')});
    });
});
</script>
@endsection
