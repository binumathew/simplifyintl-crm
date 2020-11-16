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
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Place New Order</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Place New Order</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <h4 class="mt-0 header-title">All orders are accepted under your standard terms and conditions</h4>
                            <ul>
                                <li>Please Note: Pro-rata billing may apply which could affect allowances during the first billing month following a new connection or amendment.
                                </li>
                                <li>
                                 Please refer to your Mobile Specialist or the FAQ page of your pay plans for more information.
                                <li>New connections will be provisioned immediately, this is an automated process and cannot be reversed should you change your mind.</li>
                            </ul>
                            <div class="p-3" style="padding:0 0 1rem;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="order_terms">
                                    <label class="custom-control-label" for="order_terms">I accept the above exceptions to my standard terms and conditions with {{ config('settings.app_name') }}</label>
                                </div>
                                <span id="terms_error" class="text-danger"></span>
                            </div>

                            <div id="place_new_order" style="cursor: pointer;">
                                <div class="alert alert-success new-orderbutton" role="alert">
                                    <span style="color:#ffffff;">Place a <strong>New Order</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <p class="text-muted font-14 m-b-30 notice-board-top"><i class="mdi mdi-download"></i></i>Results</p>

                            <table id="datatable" class="table table-striped dt-responsive nowrap table-vertical new-order-table" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Agent</th>
                                        <th>Details</th>
                                        <th>Created at</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $cart)
                                    @php continue; //print_R($cart); die(); @endphp
                                    <tr>
                                        <td><a class="font-600 text-muted">#98541201</a></td>
                                        <td class="font-600 text-muted">{{ $cart[0]->user->name }}</td>
                                        <td class="font-600 text-muted">{{ $cart[0]->promocode }}</td>
                                        <td>
                                        @foreach($cart as $item)
                                        <b>{{ $item->product->sim_provider->provider }}</b>
                                        <ul>
                                            <li>{{ $item->sim_count.' x '.$item->product->plan_name }}
                                                <ul>
                                                    <li>1 x DW500LU - Daisy Wholesale 500 Low User (Feb 17)</li>
                                                    <li>3 x VFSOU - O&amp;O Unlimited Special</li>
                                                    <li>3 x VFSOUIC - O&amp;O Unlimited Special with IC</li>
                                                </ul>
                                            </li>
                                        </ul>
                                        @endforeach

                                        </td>
                                        <td><span class="badge badge-success">{{$cart[0]->created_at}}</span></td>
                                        <td><a href="javascript:void(0);" class="m-r-10 text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil mdi-24px"></i></a>
                                        <a href="javascript:void(0);" class="show_user_data text-muted m-r-10" data-cart="{{$cart[0]->user->id}}" data-toggle="tooltip" data-placement="top" title="" data-original-title="View Customer"><i class="mdi mdi-eye mdi-24px"></i></a>
                                        <a href="javascript:void(0);" class="text-danger m-r-10" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-close mdi-24px"></i></a>

                                        <form id="show_user_{{$cart[0]->user->id}}" method="post" action="{{url('/user-details')}}">@csrf<input type="hidden" name="identifier" value="{{$cart[0]->user->phone}}"></form>
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
                $(document).ready(function () {
                    $('#datatable').DataTable({
                        "sDom": "<'top'iti><'clear'>"
                    });

                    $('#place_new_order').on('click', function(e){
                        e.preventDefault();
                        if(!$('#order_terms').is(':checked')){
                            $('#terms_error').text('Please accept the above exceptions!');
                        }else{
                            location.href = base_url+'/select-plan';
                        }
                    });

                    $(document).on('click', '.show_user_data', function(e) {
                        e.preventDefault();
                        var cart_id = $(this).data('cart');
                        $('#show_user_'+cart_id).submit();
                    });
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection
