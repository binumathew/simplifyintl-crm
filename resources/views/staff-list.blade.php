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
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Portal Users</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Portal Users List</h4>
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
                        <p class=" mb-0 m-t-10 text-muted">Total Portal Users<span class="pull-right"></span></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-success mr-0 float-right"><i class="mdi mdi-account-check"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0" id="active_user">0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Active Portal Users<span class="pull-right"></span></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-danger mr-0 float-right"><i class="mdi mdi-account-alert"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0" id="inactive_user">0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Not Active Portal Users <span class="pull-right">
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
                        <p class=" mb-0 m-t-10 text-muted">-<span class="pull-right"></span></p>
                    </div>
                </div>
            </div>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 m-b-20">
                                    @if(Helper::has_permission('staff','create'))
                                    <div class=" text-right">
                                        <a href="{{ url('/create-staff') }}" class="btn btn-primary ">Create New</a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <table id="staffList" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th class="d-none">#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone No</th>
                                            <th>Promo Code</th>
                                            <th>Role</th>
                                            <th>Created Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i=0; @endphp
                                            @foreach ($staff as $user)
                                            <tr id="{{ ++$i }}">
                                                <td class="d-none">{{ $i }}</td>
                                                <td>{{ $user->first_name .' '. $user->last_name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->phone }}</td>
                                                <td>{{ $user->promocode }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ Helper::date_format($user->created_at) }}</td>
                                                <td>
                                                    @if($user->status)
                                                    <span class="badge badge-success">Active</span>
                                                    @else
                                                    <span class="badge badge-danger">In-Active</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(Helper::has_permission('staff','edit'))
                                                    <a title="" href="{{ url('/edit-staff', Crypt::encrypt($user->id)) }}" data-original-title="Edit" data-toggle="tooltip" class="text-muted"><i class="mdi mdi-pencil mdi-24px"></i></a>
                                                    @endif
                                                    @if(Helper::has_permission('staff','delete'))
                                                    <!-- <a data-toggle="tooltip" title="Delete" href="{{ url('/delete-staff', Crypt::encrypt($user->id)) }}" class="fa fa-trash-o"></a> -->
                                                    @endif
                                                    @if(auth::user()->role == 1)
                                                    &nbsp;&nbsp;&nbsp;<a title="" href="{{ url('/authenticate', Crypt::encrypt($user->id)) }}" data-original-title="Login" data-toggle="tooltip"><i class="mdi mdi-login mdi-24px"></i></a>
                                                    @endif
                                                    &nbsp;&nbsp;&nbsp;<a data-toggle="tooltip" href="javascript:void(0);" data-original-title="Send Credentials" class="resend_credentials text-muted" data-id="{{ Crypt::encrypt($user->id) }}"><i class="mdi mdi-email mdi-24px"></i></a>
                                                </td>
                                            </tr>
                                            @endforeach
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
                    $('#staffList').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search: '' },});

                    $('.dataTables_filter input').attr('placeholder', 'Search');

                    $(document).on('click','.resend_credentials',function (e) {
                        var $this = $(this);
                        var id =  $this.attr('data-id');
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: base_url+'/send-password',
                            data: {id:id},
                            success:function(data){
                                if (data.error) {
                                    alert(data.message);
                                } else {
                                    alert(data.message);
                                    $this.hide();
                                }
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
