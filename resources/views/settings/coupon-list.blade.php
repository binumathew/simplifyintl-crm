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
                                <li class="breadcrumb-item"><a href="{{ url('users') }}">Home</a></li>
                                <li class="breadcrumb-item active">Coupons</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Coupons</h4>
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
                                <li><a href="{{ url('/coupons') }}" class="selected">Coupons</a></li>
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
                                        <button class="btn btn-success pull-right" id="add_coupon">ADD COUPONS</button>
                                    </div>
                                </div>
                            </div>
                            <table id="couponsList" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                <thead>
                                    <th>Coupon Code</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Expires On</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </thead>
                                <tbody>
                                    @foreach ($coupons as $coupon)
                                    <tr id="coupon_{{$coupon->id}}">
                                        <td>{{ $coupon->coupon_code }}</td>
                                        <td>{{ ($coupon->is_fixed == 1) ? 'Fixed Amount' : 'Percentage' }}</td>
                                        <td>{{ $coupon->discount_value }}</td>
                                        <td>{{ $coupon->expiry_date }}</td>
                                        <td>{{ ($coupon->status == 1) ? 'Active' : 'Inactive' }}</td>
                                        <td>
                                            <a data-toggle="tooltip" title="" data-original-title="Edit"  href="javascrip:void(0);" class="text-muted edit_coupon"  data-id="{{$coupon->id}}"><i class="mdi mdi-pencil mdi-24px"></i></a>
                                            <a data-toggle="tooltip" title="" data-original-title="Delete" href="javascrip:void(0);" class="text-danger delete_coupon" data-id="{{$coupon->id}}"><i class="mdi mdi-delete mdi-24px"></i></a>
                                            <input type="hidden" id="test" value="">
                                        </td>
                                    </tr>
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
                    $('#couponsList').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search: '' },});

                    $('.dataTables_filter input').attr('placeholder', 'Search');

                    $(document).on("click", '.edit_coupon, #add_coupon', function () {
                        var coupon = $(this).data('id');
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type:"POST",
                            url:base_url+'/coupon',
                            data:{coupon_id:coupon},
                            success:function(data){
                                if(coupon){
                                    $('#orderCustomLabel').html('Edit Coupon');
                                } else {
                                    $('#orderCustomLabel').html('Add Coupon');
                                }
                                $('#orderCustombody').html(data.html);
                                $('#orderCustomModal').modal('show');
                            }
                        })
                    });

                    $(document).on("click", '#save_coupon', function () {
                        if($("#coupon-form").valid()){
                            var coupon = $("#coupon-form").serialize();
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type:"POST",
                                url:base_url+'/save-coupon',
                                data:{coupon:coupon},
                                success:function(data){
                                    if(data.error)
                                        $('#coupon_status').html('<span class="text-danger">'+data.message+'</span>');
                                    else
                                        $('#coupon_status').html('<span class="text-success">'+data.message+'</span>');
                                }
                            })
                        }
                    });

                    $(document).on("click", '.delete_coupon', function () {
                        if(confirm("Do you really want to delete this coupon?")){
                            var coupon_id = $(this).data('id');
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type:"POST",
                                url:base_url+'/delete-coupon',
                                data:{coupon_id:coupon_id},
                                success:function(){
                                    $('#coupon_'+coupon_id).fadeOut(1000);
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
