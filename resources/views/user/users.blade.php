@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Users</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Users</h4>
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
                        <p class=" mb-0 m-t-10 text-muted">Not Active Users <span class="pull-right">
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
                            <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <table id="userlist" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>                                            
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone No</th>
                                            <th>Platform</th>                                            
                                            <th>Created On</th>
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

            <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

            
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#userlist').DataTable({
                        responsive: true,
                        bSort : true,
                        pageLength: 25,
                        language: { search: '' },
                        processing: true,
                        serverSide: true,
                        ajax: {
                            'url': 'list-users' 
                            // data: function (d) {
                            //     d.phone = $('input[name=user_cli]').val();
                            // }              
                        },
                        drawCallback:function(settings)
                        {
                            $('#total_user').html(settings.json.total);
                            $('#active_user').html(settings.json.active);
                            $('#inactive_user').html(settings.json.inactive);
                            $('#weekly_user').html(settings.json.weekly);
                        },
                        'dataType': 'json',
                        'columns': [
                            {'data': 'name', 'name': 'name'},
                            {'data': 'email', 'name': 'email'},                 
                            {'data' : 'phone', 'name': 'phone'},            
                            {'data' : 'user_platform','name' : 'ud.user_platform'},
                            {'data' : 'created_at', 'name' : 'created_at'},
                            {'data': function(data){
                                switch(parseInt(data.status)){
                                    case 0:
                                        return '<span class="badge badge-warning">Not Active</span>';
                                    break;
                                    case 1:
                                        return '<span class="badge badge-success">Active</span>';
                                    break;
                                    case 2:
                                        return '<span class="badge badge-danger">Blocked</span>';
                                    break;
                                }
                            },'name':'status'},
                            {'data': 'action', 'name': 'action','orderable': false, 'searchable': false},
                        ],
                        'order':[[4, 'desc']],
                        'columnDefs': [
                        { 'targets': 1,'width': '80px'},
                        {'defaultContent': '-','targets': '_all'}
                        ],
                        // 'buttons' : ['print', 'csv', 'excel', 'pdf'],
                    });

                    $('.dataTables_filter input').attr('placeholder', 'Search');

                    $(document).on('click','.delete_user',function(){
                        var id = $(this).attr('user-id'); 
                        $('#orderCustomLabel').text('Delete Account');
                        $('#orderCustombody').html('<div class="form-group">Do you really want to delete this contact? <div id="custom_status"> </div> </div> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="confirm_delete_user" data-id="'+ id +'" class="btn btn-danger pull-right">Delete</button>'); 
                        $('#orderCustomModal').modal('show');            
                    });

                    $(document).on('click','#confirm_delete_user',function(){      
                        var id = $(this).data('id');                   
                        $('#delete_user_'+id).submit();
                    });

                    $('#searchBtn').on('click', function(e) {
                        table.draw();
                        e.preventDefault();
                    });

                    $(document).on('click', '.show_user_data', function(e) {              
                        e.preventDefault();   
                        var id = $(this).attr('user-id');                
                        $('#show_user_'+id).submit();
                    });
                    
                    // $('.customdate').datetimepicker({
                    //     format: 'DD-MM-YYYY'
                    // });
                    
                    $('#resetBtn').on('click', function(e) {
                       $('#payment-search-form')[0].reset();
                    });
                    $('#payment-search-form').on('submit', function(e) {
                       $(this).submit();
                    });

                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection