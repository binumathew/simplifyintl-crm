@extends('layouts.home')
@section('content')
<!-- page wrapper start -->
<div class="wrapper">
    <div class="container-fluid">
        <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="btn-group pull-right">
                        <ol class="breadcrumb hide-phone p-0 m-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item active">Scheduled Tasks</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Scheduled Tasks</h4>
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
                            <li><a href="{{ url('/coupons') }}">Coupon</a></li>
                            <li><a href="{{ url('/switch') }}">Switch</a></li>
                            <li><a href="{{ url('/did-pool') }}">DID Pool</a></li>
                            <li><a href="{{ url('/throttles') }}">Throttles</a></li>
                                <li><a href="{{ url('/firewall') }}">Firewall</a></li>
                            <li><a href="{{ url('/scheduled-tasks') }}" class="selected">Cron Jobs</a></li>
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
                        <div class="row">
                            <div class="col-md-12 m-b-20">
                                <div class=" text-right">
                                    <button class="btn btn-success pull-right" id="add_task">ADD TASK</button>
                                </div>
                            </div>
                        </div>
                        <table id="cron-jobs" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Last Run</th>
                                    <th>Next Run</th>
                                    <th>Time Taken</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                <tr id="task_{{$task->id}}">
                                    <td>{{ $task->description}}</td>
                                    <td>{{ Helper::date_format($task->updated_at) }}</td>
                                    <td>{{ Helper::date_format($task->next_run) }}</td>
                                    <td>{{ gmdate("H:i:s", $task->run_time) }}</td>
                                    <td>
                                        @if($task->status)
                                        <span class="badge badge-success">Enabled</span>
                                        @else
                                        <span class="badge badge-danger">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(Helper::has_permission('settings','edit') && $task->status)
                                            <a data-toggle="tooltip" title="Execute" href="#" data-task_id="{{$task->id}}" href="javascript:void(0);" class="text-muted execute_task"><i class="mdi mdi-play mdi-24px"></i></a>
                                        @endif
                                        @if (Helper::has_permission('users','edit'))
                                            <a data-toggle="tooltip" title="Edit" href="javascript:void(0);" data-task_id="{{$task->id}}" class="text-muted edit_task"><i class="mdi mdi-pencil mdi-24px"></i></a>
                                        @endif
                                        @if(Helper::has_permission('settings','delete'))
                                            <a data-toggle="tooltip" title="Delete" href="javascript:void(0);" data-task_id="{{$task->id}}" class="text-danger delete_task" ><i class="mdi mdi-close mdi-24px"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div id="popup_wrapp"></div>
        <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
    </div>
</div>
<!-- page wrapper end -->

<script type="text/javascript">
    $(document).ready(function(){
        $('#cron-jobs').DataTable({responsive: true, pageLength: 25, bSort : true, language: { search: "" }});

        $('.dataTables_filter input').attr("placeholder", "Search");

        $(document).on("click", '.edit_task, #add_task', function () {
            var task_id = $(this).data('task_id');
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type:"GET",
                url:base_url+'/manage-task/'+task_id,
                success:function(data){
                    $('#popup_wrapp').html(data);
                    $('#scheduledTaskModal').modal('show');
                }
            })
        });

        $(document).on("click", '.delete_task', function () {
            if(confirm("Do you really want to delete this Scheduled Task?")){
                var task_id = $(this).data('task_id');
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type:"POST",
                    url:base_url+'/delete-task',
                    data:{task_id:task_id},
                    success:function(){
                        $('#task_'+task_id).fadeOut(1000);
                        $('#alert-status').html('<div class="alert alert-success" id="success">Deleted successfully</div>');
                    }
                });
            }
        });

        $(document).on("click", '.execute_task', function () {
            if(confirm("Do you really want to execute this task?")){
                var task_id = $(this).data('task_id');
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type:"POST",
                    url:base_url+'/execute-task',
                    headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
                    data:{task_id:task_id},
                    success:function(){
                        location.reload();
                    }
                });
            }
        });
    });
</script>
@endsection
