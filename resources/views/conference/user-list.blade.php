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
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Conference Users</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Conference Users</h4>
                </div>
            </div>
        </div>
          
        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-body">
                    <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">

                        <table id="user_list" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="width:10% !important">Name</th>
                                    <th style="width:10% !important">Email</th>
                                    <th style="width:5% !important">Phone No.</th>
                                    <th style="width:1% !important">Platform</th>
                                    <th style="width:1% !important">Status</th>
                                    <th style="width:5% !important">Created On</th>
                                    <th style="width:5% !important">Action</th>
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
        <script src="{{ asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

        <script type="text/javascript">
            $(document).ready(function(){

                var table = $('#user_list').DataTable({
                    searchDelay: 350,
                    responsive: true,
                    bSort : true,
                    pageLength: 25,
                    language: { search: '' },
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: base_url+'/conference-user-list',
                        data: function (d) {
                        }
                    },
                    'dataType': 'json',
                    'columns': [
                            {'data': 'name', 'name': 'name'},
                            {'data': 'email', 'name': 'email'},                 
                            {'data': 'phone', 'name': 'phone'},
                            {'data': 'platform','name':'platform'},
                            {'data' : 'status','name':'status'},
                            {'data' : 'created_at','name':'created_at'},
                            {'data' : 'actions','name':'actions','orderable': false, 'searchable': false}
                    ],
                    'order':[[5, 'desc']],
                    buttons: [
                    ],
                });
                $('.dataTables_filter input').attr("placeholder", "Search");

                $(document).on('click','.userAction',function(){
                    var userid = $(this).attr('data-user');
                    var tag    = $(this).attr('data-tag');
                    var status = $(this).attr('data-status');
                    alertify.dismissAll();
                    alertify.confirm('Confirmation', 'Are you sure to '+tag+' the user ?',
                    function(){
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type:"POST",
                            data:{ userid:userid,status:status },
                            url:base_url + '/conf-user-status',                            
                            success:function(data){
                                if(data.status == 200) {
                                    // table.draw();
                                    table.ajax.reload();
                                    alertify.success(data.msg);                                    
                                } else {
                                    alertify.error(data.msg);
                                }
                            }
                        }); 
                    },function(){ alertify.error('Option cancelled')}); 
                });

                $(document).on('click', '.show_user_data', function(e) {              
                    e.preventDefault();   
                    var id = $(this).attr('user-id');                
                    $('#show_user_'+id).submit();
                });

                $(document).on('click','.delete_user',function(){
                    var id = $(this).attr('user-id'); 
                    if(confirm('Do you really want to delete this contact ?')){
                        $('#delete_user_'+id).submit();
                    }
                });
            });
        </script>
    </div>
    <!-- end container-fluid -->
</div>
<!-- page wrapper end -->
@endsection