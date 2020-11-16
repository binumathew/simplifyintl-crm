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
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Throttle List</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Throttle List</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <div class="card m-b-20">

                    <div class="card-body right-nav">
                        <ul>
                            <li><a href="{{ url('/settings') }}">General</a></li>
                            <li><a href="{{ url('/template') }}">Email Template</a></li>
                            <li><a href="{{ url('/roles') }}">Roles</a></li>
                            <li><a href="{{ url('/countries') }}">Countries</a></li>
                            <li><a href="{{ url('/credits') }}">Credit</a></li>
                            <li><a href="{{ url('/coupons') }}">Coupons</a></li>
                            <li><a href="{{ url('/switch') }}">Switch</a></li>
                            <li><a href="{{ url('/did-pool') }}">DID Pool</a></li>
                            <li><a href="{{ url('/throttles') }}" class="selected">Throttles</a></li>
                            <li><a href="{{ url('/firewall') }}">Firewall</a></li>
                            <li><a href="{{ url('/scheduled-tasks') }}">Cron Jobs</a></li>
                            <li><a href="{{ url('/payment-gateway') }}">Payment Gateways</a></li>
                            <li><a href="{{ url('/stock-list') }}">Stock</a></li>
                            <li><a href="{{ url('/api-logger') }}">API Log</a></li>

                            <!-- <li><a href="#">Leads</a></li>
                            <li><a href="#">SMS</a></li>
                            <li><a href="#">Calendar</a></li>
                            <li><a href="#">PDF</a></li>
                            <li><a href="#">E-Sign</a></li>
                            <li><a href="#">Cron Job</a></li>
                            <li><a href="#">Tags</a></li>
                            <li><a href="#">Pusher.com</a></li>
                            <li><a href="#">Google</a></li>
                            <li><a href="#">Misc</a></li> -->
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-10">
                <div class="card m-b-20">
                    <div class="card-body">
                        <div class="order-search">
                            <form action="{{ url('throttle-list') }}" id="throttle-form" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Email/Phone</label>
                                            <input class="form-control" id="identifier" name="identifier" type="text" placeholder="Email / Phone">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>IP Address</label>
                                            <input class="form-control" id="ip_address" name="ip_address" type="text" placeholder="IP Address">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Attempted On</label>
                                            <input type="text" class="form-control datepicker" id="attempted" name="attempted" placeholder="Attempted On">
                                        </div>
                                    </div>
                                    <div class=" col-md-12">
                                        <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                        <button type="button" id="searchBtn" class="btn btn-primary pull-right">Search</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <table id="throttleList" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="tbl_tick_bx">#</th>
                                    <th>Email</th>
                                    <th>IP Address</th>
                                    <th>Attempted On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <div class="pull-right">
                            <button type="button" class="btn btn-warning delete_throttle">Delete</button>
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
    </div>
</div>
<!-- page wrapper end -->

<script type="text/javascript">
    $(document).ready(function(){
        $('#throttleList').DataTable({
            responsive: true,
            bSort : true,
            pageLength: 25,
            language: { search: '' },
            processing: true,
            serverSide: true,
            ajax: {
                'url': 'throttle-list',

                'data': function ( d ) {
                    d.identifier = $('#identifier').val();
                    d.ip_address = $('#ip_address').val();
                    d.attempted = $('#attempted').val();
                }
            },

            'dataType': 'jsonp',
            'columns': [
            {'data': function(data){
                return '<input type="checkbox" name="request_id[]" value="'+ data.id +'" class="request_list_chkbx">';
            }, 'orderable': false, 'searchable': false, 'name':'id' },
            {'data': 'identifier', 'name': 'identifier'},
            {'data': 'ip_address', 'name': 'ip_address'},
            {'data': 'attempted_at', 'name': 'attempted_at'},
            {'data': function(data){
                return '<a data-toggle="tooltip" title="Delete" href="#" class="text-danger delete_throttle" data-id="'+data.id+'" ><i class="mdi mdi-delete mdi-24px"></i></a>';
            }, 'name': 'action','orderable': false, 'searchable': false},
            ],
            "order":[[3, 'desc']],
        });

        $('.dataTables_filter input').attr('placeholder', 'Search');

        $(document).on('keyup','#identifier, #ip_address',function () {
            $('#throttleList').DataTable().draw();
        });

        $('.datepicker').change(function () {
            $('#throttleList').DataTable().draw();
        });

        $('#searchBtn').on('click', function(e) {
            e.preventDefault();
            $('#throttleList').DataTable().draw();
        });

        $('#resetBtn').on('click', function(e) {
            $('#throttle-form')[0].reset();
        });

        $('.datepicker').datepicker({
            autoclose: true,
            orientation:'bottom left',
            format: 'yyyy-mm-dd',
            todayHighlight: true
        });

        var checked = [];
        $(document).on('click','.delete_throttle',function (e) {
            checked = [];
            var data_id = $(this).attr('data-id');
            if (typeof data_id !== typeof undefined && data_id !== false) {
                checked.push($(this).attr('data-id'));
            }else{
                $(".request_list_chkbx:checked").each(function() {
                    checked.push($(this).val());
                });
            }

            $('#orderCustomLabel').text('Confirm Delete Throttle');
            if (checked == '') {
                $('#orderCustombody').html('<div class="text-danger">Please select atleast one entry to delete</div>');
            } else {
                $('#orderCustombody').html('<div class="form-group">Are you sure you want to delete this item(s)? <div id="custom_status"></div> </div> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="action_throttle_delete" class="btn btn-danger pull-right">Delete</button>');
            }
            $('#orderCustomModal').modal('show');
        });

        $(document).on('click','#action_throttle_delete',function (e) {
            if (checked == '') {
                $('#orderCustombody').html('<div class="text-danger">Please select atleast one entry to delete</div>');
                $('#orderCustomModal').modal('show');
            } else {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: 'delete-throttle',
                    data: {selected:checked},
                    success:function(data){
                        if (data.error) {
                            $('#custom_status').html('<div class="text-danger">'+data.Message+'</div>');
                        } else {
                            $('#orderCustombody').html('<div class="text-success">Deleted Successfully</div>');
                            $('#throttleList').DataTable().draw();
                        }
                    }
                });
            }
        });
    });

</script>
@endsection
