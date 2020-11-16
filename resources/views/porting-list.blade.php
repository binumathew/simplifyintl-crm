@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Porting Process</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Porting Process</h4>
                    </div>
                </div>
            </div>
            @if(Helper::has_permission('reports'))
                <div class="row">
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-primary mr-0 float-right"><i class="mdi mdi-account-multiple"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="total_user">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Total Users<span class="pull-right"></span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-success mr-0 float-right"><i class="mdi mdi-account-check"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="active_user">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Active Users<span class="pull-right"></span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-danger mr-0 float-right"><i class="mdi mdi-account-alert"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="inactive_user">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">In-Active Users <span class="pull-right">
                                </span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-warning mr-0 float-right"><i class="mdi mdi-account"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="weekly_user">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">This week<span class="pull-right"></span></p>
                        </div>
                    </div>
                </div>
                @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div class="order-search">
                                <form action="{{ url('port-list') }}" id="port-search-form" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input class="form-control" id="email" type="text" placeholder="Email">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input class="form-control" id="reference_id" type="text" placeholder="Reference ID">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input class="form-control" id="porting_to" type="text" placeholder="Number To Keep">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="Date From" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="Date To" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select id="port_status" class="form-control">
                                                    <option value="">Status</option>
                                                    <option value="0">Request Received</option>
                                                    <option value="1">Request Initiated</option>
                                                    <option value="2">Request Confirmed</option>
                                                    <option value="3">Request Processed</option>
                                                    <option value="4">Request Completed</option>
                                                    <option value="5">Request Canceled</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            @if(Helper::has_permission('reports'))
                                            <button type="submit" class="btn btn-info" id="export" name="exportdata" value="1">Export</button>
                                            @endif
                                            <button type="button" id="searchBtn" class="btn btn-primary pull-right">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary pull-right" style="margin-right:10px;">Reset</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <table id="portlist" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Created On</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Temporary No</th>
                                            <th>Reference ID</th>
                                            <th>PAC Number</th>
                                            <th>No To Keep</th>
                                            <th>Expected Date</th>
                                            <th>Status</th>
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
            </div>

            <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

            <script type="text/javascript">
                $(document).ready(function(){
                    $('#portlist').DataTable({
                        responsive: true,
                        bSort : true,
                        pageLength: 25,
                        language: { search: "" },
                        processing: true,
                        serverSide: true,
                        "ajax": {
                            "url": "porting-list",
                            "data": function ( d ) {
                                d.reference_id = $('#reference_id').val();
                                d.porting_to = $('#porting_to').val();
                                d.port_status = $('#port_status').val();
                                d.from_date = $('#from_date').val();
                                d.to_date = $('#to_date').val();
                                d.email = $('#email').val();
                            }
                        },

                        "dataType": "jsonp",
                        "columns": [
                            {"data": "created_at", "name": "tbl_porting.created_at"},
                            {"data": "name", "name": "u.name"},
                            {"data": "email", "name": "u.email"},
                            {"data" : "temporary_no", "name": "s.phone_number"},
                            {"data" : "reference_id","name":"reference_id"},
                            {"data" : "pac_number","name":"pac_number"},
                            {"data" : "porting_to","name":"porting_to"},
                            {"data" : "expected_date","name" : "expected_date"},
                            {"data" : function (data) {
                                switch(data.status){
                                    case 0:
                                        return '<span class="badge badge-secondary">Received</span>';
                                    break
                                    case '1':
                                        return '<span class="badge badge-warning">Initiated</span>';
                                    break
                                    case '2':
                                        return '<span class="badge badge-warning">Confirmed</span>';
                                    break
                                    case '3':
                                       return '<span class="badge badge-info">Processed</span>';
                                    break
                                    case '4':
                                        return '<span class="badge badge-success">Completed</span>';
                                    break
                                    case '5':
                                        return '<span class="badge badge-danger">Canceled</span>';
                                    break
                                }
                            }, "name": "tbl_porting.status"},
                            {"data": "action", "name": "action","orderable": false, "searchable": false},
                        ],
                        'order':[[0, 'desc']],
                        "columnDefs": [
                            { "targets": 1,"width": '80px'},
                            {"defaultContent": "-","targets": "_all"}
                        ],
                    });

                    $('.dataTables_filter input').attr('placeholder', 'Search');

                    $('#searchBtn').on('click', function(e) {
                        $('#portlist').DataTable().draw();
                        e.preventDefault();
                    });

                    $('.datepicker').datepicker({
                        autoclose: true,
                        orientation:'bottom left',
                        format: 'yyyy-mm-dd',
                        todayHighlight: true
                    });

                    $('#resetBtn').on('click', function(e) {
                       $('#port-search-form')[0].reset();
                       $('#portlist').DataTable().draw();
                    });

                    $(document).on('click', '.view_port_details', function(e) {
                        var port_id = $(this).data('id');
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: base_url+'/port-details',
                            data: {port_id:port_id},
                            success:function(data){
                                $('#preloader').hide();
                                $('#orderCustomLabel').text('Porting Request Details');
                                if (data.error) {
                                    $('#orderCustombody').html('<div class="text-danger">'+data.message+'</div>');
                                } else {
                                    $('#orderCustombody').html(data.html);
                                }
                                $('#orderCustomModal').modal('show');
                            }
                        });
                    });
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection
