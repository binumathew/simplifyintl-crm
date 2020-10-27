@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
        <div class="wrapper">
            <div class="container-fluid">
                <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
                <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="btn-group pull-right">
                                <ol class="breadcrumb hide-phone p-0 m-0">
                                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">API Log</li>
                                </ol>
                            </div>
                            <h4 class="page-title">API Log</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">                             
                                <table id="api-log" class="table table-striped dt-responsive table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Path</th>
                                            <th>Method</th>
                                            <th>Execution</th>
                                            <th>Created At</th>
                                            <th>Request</th>
                                            <th>Response</th>
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
            </div>
        </div>
        <!-- page wrapper end -->

        <script type="text/javascript">
            $(document).ready(function(){
                var notificationTable = $('#api-log').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    responsive: true,
                    searching: true,
                    "bSort" : false,
                    autoWidth: false,
                    ajax: {
                        url: 'list-api-log',
                        data: function (d) {
                        }
                    },
                    columns: [
                        {data: 'name', name: 'usr.name'},
                        {data: 'path', name: 'al.path'},
                        {data: 'method', name: 'al.method'},
                        {data: 'exec_time', name: 'al.exec_time'},
                        {data: 'created_at', name: 'al.created_at'},
                        {data: 'request', name: 'al.request'},
                        {data: 'response', name: 'al.response'},            
                    ],
                    columnDefs: [
                       { width: '100px', targets: 2 } 
                    ]
                });

                $('.dataTables_filter input').attr("placeholder", "Search");                
            });
        </script>
@endsection
