@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/new-order')}}">Order</a></li>
                                <li class="breadcrumb-item active">Select voice and data</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Select voice and data</h4>
                    </div>
                </div>
            </div>
      
            <div class="row">
                <div class="col-md-9">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <p class="text-muted font-14 m-b-30 notice-board-top">
                                <i class="mdi mdi-fullscreen noti-icon"></i>
                                Please enter the quantity of each product you wish to place an order for and click add to continue</p>
                            <h4 class="mt-0 header-title">One and only Voice</h4>
                            <p class="text-muted m-b-30 font-14">Please enter the quantity of each product you wish to place an order for and click add to continue</p>

                            <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                @foreach($providers as $key => $provider)
                                <li class="nav-item">
                                    <a class="nav-link {{($key == '0')?'active':''}}" data-toggle="tab" href="#{{strtolower($provider->provider)}}" role="tab">
                                        <span class="d-none d-md-block">{{$provider->provider}}</span>
                                        <span class="d-block d-md-none">
                                            <i class="mdi mdi-home-variant h5"></i>
                                        </span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>

                            <form id="plan-form"> 
                                <div class="tab-content">                                
                                    @foreach($providers as $key => $provider)
                                    <div class="tab-pane {{($key == '0')?'active':''}} p-3" id="{{strtolower($provider->provider)}}" role="tabpanel">
                                        <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                            <table class="table table-striped dt-responsive nowrap table-vertical datatable" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Charge</th>
                                                        <th>Qty*</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($plans[$provider->short_code] as $plan)
                                                    @php $value = (isset($cart[$plan->id]))? $cart[$plan->id]:''; @endphp
                                                    <tr class="odd">
                                                        <td tabindex="0">{{ $plan->plan_name }}</td>
                                                        <td style="width: 10%;" class="text-right">£{{ Helper::number_format($plan->sell_price) }}</td>
                                                        <td style="width: 5%;">
                                                            <input type="number" class="form-control form-control-sm product_qty"  placeholder="" aria-controls="datatable" name="product[{{$plan->id}}]" min="0" value="{{$value}}">
                                                        </td>
                                                    </tr>
                                                    @endforeach                                 
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endforeach   
                                    <p id="plan_error"></p>                                 
                                    <div class="pull-right">                                    
                                        <button type="button" class="btn btn-success" id="select_plan"><strong>Continue</strong></button> <!-- waves-effect waves-light -->
                                    </div>                                
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card m-b-20">
                        <div class="card-body right-nav">
                            <ul>
                                <li><a href="#" class="selected"><b>Step 1</b><br/>Select voice and data</a></li>
                                <li><a href="#"><b>Step 2</b><br/>Configure voice and data</a></li>
                                <li><a href="#"><b>Step 3</b><br />Select bolt-ons</a></li>
                                <li><a href="#"><b>Step 4</b><br/>Provisioning information</a></li>
                                <li><a href="#"><b>Step 5</b><br/>Bill limits</a></li>
                                <li><a href="#"><b>Step 6</b><br/>Summary</a></li>
                                <li><a href="#"><b>Step 7</b><br/>Add Customer</a></li>
                                <li><a href="#"><b>Step 8</b><br/>Payment</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

            <script type="text/javascript">
                $(document).ready(function () {
                    $('.datatable').DataTable({'order': [[ 1, 'asc' ]]});

                    $('#select_plan').on('click', function(e){
                        var valid = false;
                        $('.product_qty').each(function(index, value) {
                            if($(this).val() > 0){
                                valid = true;
                            }
                        });

                        if(valid){
                            var formData = new FormData($('#plan-form')[0]);
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',                                                
                                url: base_url+'/select-plan',
                                data: formData,
                                cache: false,
                                contentType: false,
                                processData: false,
                                success:function(data){ 
                                    if (data.error) {
                                        $('#plan_error').html('<div class="alert alert-danger alert-colored mb-0" role="alert">'+data.message+'</div>');
                                    } else {
                                        location.href = base_url+'/bolt-ons';   
                                    }
                                }
                            });
                            
                        }else{
                            $('#plan_error').html('<div class="alert alert-danger alert-colored mb-0" role="alert">Please select atleast one product!</div>'); 
                        }
                    });
                });
            </script>   
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection