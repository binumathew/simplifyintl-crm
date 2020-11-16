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
                                <li class="breadcrumb-item active">Blocked Users</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Blocked Users</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <table id="userList" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone No</th>
                                            <th>Blocked On</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i=0; @endphp
                                        @foreach ($fraudsters as $user)
                                            <tr id="user_{{$user->id}}">
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->phone }}</td>
                                                <td>{{ Helper::date_format($user->created_at) }}</td>
                                                <td>
                                                    @if(Helper::has_permission('fraudsters','delete'))
                                                        <a title="" href="javascript:void(0);" data-original-title="Delete" data-toggle="tooltip" class="delete_fraudster text-danger" data-list_id={{$user->id}}><i class="mdi mdi-delete mdi-24px"></i></a>
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
            </div>

            <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>


            <script type="text/javascript">
                $(document).ready(function(){
                    $('#userList').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search: '' },});

                    $('.dataTables_filter input').attr('placeholder', 'Search');

                    $(document).on("click", ".delete_fraudster", function () {
                        var $this = $(this);
                        if(confirm("Do you really want to delete this contact ?")){
                            var id = $this.data('list_id');
                            $.ajax({
                                type:"POST",
                                url:base_url+'/delete-fraudster',
                                headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
                                data:{id:id},
                                success:function(){
                                    $('#user_'+id).fadeOut(1000);
                                }
                            })
                        }
                    });
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection
