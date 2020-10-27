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
                                    <li class="breadcrumb-item"><a href="{{ url('/roles') }}">Roles</a></li>
                                    <li class="breadcrumb-item active">Edit Role</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Role Permission</h4>
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
                                <form id="permisssion-form">
                                    @csrf   
                                    <div class="col-md-6 pull-left">
                                        <div class="form-group">
                                            <label for="name" class="control-label">Role Name</label>
                                            <input type="hidden" name="role_id" value="{{ ($role)?Crypt::encrypt($role->id):''}}">
                                            <input type="text" name="name" class="form-control" autofocus="1" value="{{($role)?$role->name:''}}" required>
                                        </div>               
                                    </div>
                                    <div class="col-md-6 pull-right">
                                        <div class="form-group">
                                            <label for="name" class="control-label">Short Code</label>
                                            <input type="text" name="short_code" class="form-control" autofocus="1" value="{{($role)?$role->short_code:''}}" required>
                                        </div>               
                                    </div>

                                    <table class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th class="bold">Permission</th>
                                                <th class="text-center bold">View Own</th>  
                                                <th class="text-center bold">View</th>               
                                                <th class="text-center bold">Create</th>
                                                <th class="text-center bold">Edit</th>
                                                <th class="text-center text-danger bold">Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-24">                                            
                                            @foreach($permissions as $permission)   
                                            @php    
                                                $p_id = $permission->permission_id;
                                                $view_own_checked = ($permission->can_view_own)?'checked':'';                                        
                                                $view_checked = ($permission->can_view)?'checked':'';
                                                $create_checked = ($permission->can_create)?'checked':'';
                                                $edit_checked = ($permission->can_edit)?'checked':'';
                                                $delete_checked = ($permission->can_delete)?'checked':'';
                                            @endphp
                                                <tr>                                                
                                                    <td> 
                                                        {{ $permission->name }}                 
                                                        <input type="hidden" name="permission_id[]" value="{{$p_id}}">
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="view_own_{{$p_id}}" name="permission_view_own[{{$permission->permission_id}}]" value="1" {{$view_own_checked}}>
                                                            <label class="custom-control-label" for="view_own_{{$p_id}}"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                       <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="view_{{$p_id}}" name="permission_view[{{$permission->permission_id}}]" value="1" {{$view_checked}}>
                                                            <label class="custom-control-label" for="view_{{$p_id}}"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="create_{{$p_id}}" name="permission_create[{{$permission->permission_id}}]" value="1" {{$create_checked}}>
                                                            <label class="custom-control-label" for="create_{{$p_id}}"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="edit_{{$p_id}}" name="permission_edit[{{$permission->permission_id}}]" value="1" {{$edit_checked}}>
                                                            <label class="custom-control-label" for="edit_{{$p_id}}"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-switch text-danger">
                                                            <input type="checkbox" class="custom-control-input" id="delete_{{$p_id}}" name="permission_delete[{{$permission->permission_id}}]" value="1" {{$delete_checked}}>
                                                            <label class="custom-control-label" for="delete_{{$p_id}}"></label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="row pull-right">
                                        <div class="col-md-12">
                                            <div id="custom_status"></div>
                                            <button type="button" class="btn btn-primary" id="save-permission">Save</button>
                                        </div>
                                    </div>
                                </form>
                                    
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
                $(document).on('click', '#save-permission', function(){
                    var formData = new FormData($('#permisssion-form')[0]);
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',                                                
                        url: base_url+'/save-role',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success:function(data){ 
                            if (data.error) {
                                $('#custom_status').html('<div class="text-danger">'+data.message+'</div>');
                            } else {
                                $('#custom_status').html('<div class="text-success">'+data.message+'</div>');   
                            }
                        }
                    });
                })
            });
        </script>
@endsection
