@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <!-- <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/> -->
            <!-- <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/> -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/new-order')}}">Order</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/select-plan')}}">Plans</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/bolt-ons')}}">Bolt-ons</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/billing')}}">Billing</a></li>
                                <li class="breadcrumb-item active">Summary</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Summary</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-9">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <!-- <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#vodafone" role="tab">
                                        <span class="d-none d-md-block">Vodafone</span><span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                                    </a>
                                </li>
                            </ul> -->

                            <!-- <div class="tab-content">
                                <div class="tab-pane active p-3" id="vodafone" role="tabpanel"> -->
                                    <div class="table-responsive b-0 fixed-solution" data-pattern="priority-columns">
                                        <table id="datatable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th nowrap="nowrap">Quantity</th>
                                                    <th nowrap="nowrap" class="text-right">Charge</th>
                                                    <th nowrap="nowrap" class="text-right">Total</th>
                                                    <!-- <th width="150" colspan="2">Bolt-Ons</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @php $net_amount = $vat_amount = $total_amount = 0;
                                            $currency = $user->country->currency_symbol; @endphp
                                            @foreach($cart as $item)
                                              @php
                                                $sell_price = $item->product->sell_price;
                                                $net_sell = Helper::number_format(100/(100 + $user->country->tax) * $sell_price);
                                                $net_price = Helper::number_format($net_sell * $item->item_count);
                                                $net_amount += $net_price;
                                                $vat_amount += (($sell_price * $item->item_count)- $net_price);
                                              @endphp
                                                <tr>
                                                    <td>{{ $item->product->plan_name }}</td>
                                                    <td>{{ $item->item_count }}</td>
                                                    <td class="text-right">{{ $currency.$net_sell }}</td>
                                                    <td class="text-right">{{ $currency.$net_price }}</td>
                                                </tr>
                                                @php $i = $j = $sim_bolt = $app_bolt = 0;
                                                $bolt = $item->selected_bolt(); @endphp
                                                @if(sizeof($bolt['sim_bolt']) >= 1)
                                                <tr>
                                                    <td colspan="4"><small>Bolt On</small></td>
                                                </tr>
                                                @endif
                                                @foreach($bolt['sim_bolt'] as $sim_bolt)
                                                @php
                                                $sim_bolt_price = count($sim_bolt) * $sim_bolt[0]['price'];
                                                $net_amount += $sim_bolt_price;
                                                @endphp
                                                <tr>
                                                    <td>{{ $sim_bolt[0]['plan_name'] }}</td>
                                                    <td>{{ count($sim_bolt) }}</td>
                                                    <td class="text-right">{{ $currency.Helper::number_format($sim_bolt[0]['price']) }}</td>
                                                    <td class="text-right">{{$currency.Helper::number_format($sim_bolt_price) }}</td>
                                                </tr>
                                                @endforeach
                                                @if(sizeof($bolt['app_bolt']) >= 1)
                                                <tr>
                                                    <td colspan="4"><small>Mobile App Bolt On</small></td>
                                                </tr>
                                                @endif
                                                @foreach($bolt['app_bolt'] as $app_bolt)
                                                @php
                                                $app_bolt_price = count($app_bolt) * $app_bolt[0]['price'];
                                                $net_amount += $app_bolt_price;
                                                @endphp
                                                <tr>
                                                    <td>{{ $app_bolt[0]['plan_name'] }}</td>
                                                    <td>{{ count($app_bolt) }}</td>
                                                    <td class="text-right">{{ $currency.Helper::number_format($app_bolt[0]['price'])  }}</td>
                                                    <td class="text-right">{{ $currency.Helper::number_format($app_bolt_price) }}</td>
                                                </tr>
                                                @endforeach
                                            @endforeach
                                                <tr>
                                                    <td class="text-right" colspan="3">Sub Total</td>
                                                    <td class="text-right"><b>{{ $currency.number_format($net_amount,2)}}</b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right" colspan="3">VAT</td>
                                                    <td class="text-right"><b>{{ $currency.number_format($vat_amount,2)}}</b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right" colspan="3">Total</td>
                                                    <td class="text-right"><b>{{ $currency.number_format(($net_amount+$vat_amount),2) }}</b></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <div>
                                            <a href="{{ url('/billing') }}" class="btn btn-secondary waves-effect waves-light"><strong>Back</strong></a>
                                            <a href="{{ url('/payment') }}" class="btn btn-success waves-effect waves-light pull-right"><strong>Continue</strong></a>
                                        </div>
                                    </div>
                               <!--  </div>
                            </div> -->
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card m-b-20">
                        <div class="card-body right-nav">
                            <ul>
                                <li><a href="#"><b>Step 1</b><br/>Select voice and data</a></li>
                                <li><a href="#"><b>Step 2</b><br/>Configure voice and data</a></li>
                                <li><a href="#"><b>Step 3</b><br />Select bolt-ons</a></li>
                                <li><a href="#"><b>Step 4</b><br/>Provisioning information</a></li>
                                <li><a href="#"><b>Step 5</b><br/>Bill limits</a></li>
                                <li><a href="#"><b>Step 6</b><br/>Add Customer</a></li>
                                <li><a href="#" class="selected"><b>Step 7</b><br/>Summary</a></li>
                                <li><a href="#"><b>Step 8</b><br/>Payment</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>



            <!-- <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script> -->

            <script type="text/javascript">
                $(document).ready(function () {
                    $(document).on('click', '#manage_user', function(e){
                        if($("#user-form").valid()){
                            var formData = new FormData($('#user-form')[0]);
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',
                                url: base_url,
                                data: formData,
                                cache: false,
                                contentType: false,
                                processData: false,
                                success:function(data){
                                    if(data.error){
                                        alert(data.message);
                                    }else{
                                        location.href = base_url+'/summary';
                                    }
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
