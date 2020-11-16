@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>

            <link rel="stylesheet" href="assets/plugins/jquery-steps/jquery.steps.css">
            <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
            <link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
            <link href="assets/css/style.css" rel="stylesheet" type="text/css" />

            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                <li class="breadcrumb-item active">Roles</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Roles</h4>
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
                                <li><a href="{{ url('/roles') }}" class="selected">Roles</a></li>
                                <li><a href="{{ url('/countries') }}">Countries</a></li>
                                <li><a href="{{ url('/credits') }}">Credit</a></li>
                                <li><a href="{{ url('/coupons') }}">Coupon</a></li>
                                <li><a href="{{ url('/switch') }}">Switch</a></li>
                                <li><a href="{{ url('/did-pool') }}">DID Pool</a></li>
                                <li><a href="{{ url('/throttles') }}">Throttles</a></li>
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
                            <div class="row">
                                <div class="col-md-12 m-b-20">
                                    <div class=" text-right">
                                        <a href="{{ url('/manage-role') }}" class="btn btn-primary ">Add Role</a>
                                    </div>
                                </div>
                            </div>

                            <table id="roleList" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Role Name</th>
                                        <th>Short Code</th>
                                        <th>Created On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                               <tbody>
                                    @php $i=1; @endphp
                                    @foreach ($roles as $role)
                                    <tr id="{{$i}}" class="odd">
                                        <td>{{$i}}</td>
                                        <td>{{$role->name}}</td>
                                        <td>{{$role->short_code}}</td>
                                        <td>{{ Helper::date_format($role->created_at) }}</td>
                                        @php $parameter = Crypt::encrypt($role->id); @endphp
                                        <td>
                                            @if(Helper::has_permission('roles','edit'))
                                            <a href="{{url('/manage-role',$parameter)}}" class="text-muted" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="mdi mdi-pencil mdi-24px"></i></a>&nbsp;&nbsp;&nbsp;
                                            @endif
                                            @if(Helper::has_permission('roles','delete'))
                                            <!-- <a href="javascript:void(0);" class="text-danger delete_role" data-toggle="tooltip" data-placement="top"  data-original-title="Delete" data-id={{$parameter}}><i class="mdi mdi-delete mdi-24px"></i></a> -->
                                            @endif
                                        </td>
                                    </tr>
                                    @php $i++; @endphp
                                    @endforeach
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


            <script type="text/javascript">
                $(document).ready(function(){
                    $('#roleList').DataTable({responsive: true, pageLength: 25, bSort : false, language: { search: "" }});

                    $('.dataTables_filter input').attr('placeholder', 'Search');

                    $(document).on("click", '.delete_role', function () {
                        if(confirm("Do you really want to delete this role?")){
                            var role_id = $(this).data('id');
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type:"POST",
                                url:base_url+'/delete-role',
                                data:{role_id:role_id},
                                success:function(){
                                    $('#role_'+role_id).fadeOut(1000);
                                }
                            });
                        }
                    });
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection

