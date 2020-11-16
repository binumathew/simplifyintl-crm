@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ url('/users') }}">Home</a></li>
                                <li class="breadcrumb-item active">In Complete Sign Up</li>
                            </ol>
                        </div>
                        <h4 class="page-title">In Complete Users</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <table id="userlist" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Phone No</th>
                                            <th>Country</th>
                                            <th>Created On</th>
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


            <script type="text/javascript">
                $(document).ready(function(){
                    $('#userlist').DataTable({
                        responsive: true,
                        "bSort" : true,
                        language: { search: "" },
                        pageLength:25,
                        processing: true,
                        serverSide: true,
                        "ajax": {
                            "url": "temp-user-list",
                        },

                        "dataType": "jsonp",
                        "columns": [
                            {"data" : "phone", "name": "phone"},
                            {"data" : "country_name","name" : "c.country_name"},
                            {"data" : "created_at","name" : "created_at"},
                            {"data": "action", "name": "action","orderable": false, "searchable": false},
                        ],
                        "columnDefs": [
                            { "targets": 1,"width": '80px'},
                            {"defaultContent": "-","targets": "_all"}
                        ],
                    });

                    $('.dataTables_filter input').attr('placeholder', 'Search');

                    $(document).on('click','.delete_user',function(){
                        var id = $(this).attr('user-id');
                        if(confirm('Do you really want to delete this contact ?')){
                            $('#delete_user_'+id).submit();
                        }
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
