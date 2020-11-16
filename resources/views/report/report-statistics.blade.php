@extends('layouts.home')
@section('content')
        <div class="wrapper">
            <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('report-statistics') }}" id="statistics-search-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Time Period</label>
                                                <select name="time_period" id="time_period" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                    <option value="1">This Week</option>
                                                    <option value="2">This Month</option>
                                                    <option value="3">Previous Month</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class=" col-md-12">
                                            <button type="button" id="statisticSearch" class="btn btn-primary ">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>

                                        </div>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card m-b-20" style="height: 700px;">
                            <div class="card-body">
                                @php $userdata = json_decode($user_data); @endphp
                                <h4 class="mt-0 header-title">Users</h4>

                                <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                    @if(isset($userdata->all))
                                    @foreach($userdata->all as $ukey =>$list)

                                    @if(isset($list->status))
                                    @php $status = ($list->status == 1) ? "Active" : "InActive";
                                    @endphp
                                    <li class="list-inline-item" style="width: 15% !important">
                                        <h5 class="mb-0 us_cnt" id="{{ $status }}">{{ $list->usercount }}</h5>
                                        <p class="text-muted font-14">{{ $status }} Users</p>
                                    </li>
                                    @endif
                                    @if(isset($list->user_platform))
                                    @php $platform = ($list->user_platform != "") ? ucfirst(strtolower($list->user_platform)) : "Others";
                                    @endphp
                                    <li class="list-inline-item" style="width: 15% !important">
                                        <h5 class="mb-0 us_cnt" id="{{ $platform }}">{{ $list->usercount }}</h5>
                                        <p class="text-muted font-14">{{ $platform }}</p>
                                    </li>
                                    @endif
                                    @endforeach
                                    @endif
                                </ul>

                                <div id="userChart" class="morris-charts" style="height: 300px"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->

                    <div class="col-lg-6">
                        <div class="card m-b-20" style="height: 700px;">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Payment</h4>

                                <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                @php $userpay = json_decode($user_pay); @endphp
                                @foreach($userpay as $ukey => $plist)
                                @php
                                $total  = 0;
                                $su     = (isset($plist->succ)) ? $plist->succ: 0;
                                $fail   = (isset($plist->fail)) ? $plist->fail: 0;
                                $suwerr = (isset($plist->suwerr)) ? $plist->suwerr: 0;
                                $refund = (isset($plist->refund)) ? $plist->refund: 0;
                                $total  = $su + $fail + $suwerr + $refund;
                                @endphp
                                    <li class="list-inline-item" style="width: 15% !important">
                                        <h5 class="mb-0 up_cnt" id="{{ $plist->y }}">{{ $total }}</h5>
                                        <p class="text-muted font-14">{{ $plist->y }}</p>
                                    </li>
                                    @endforeach
                                </ul>

                                <div id="paymentChart" class="morris-charts" style="height: 300px;width: 100%;"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card m-b-20" style="height: 500px;">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Auto Plan (Plan Type) Plans</h4>

                                <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                @php $plantype = json_decode($plan_type); @endphp
                                @foreach($plantype as $pkey => $plist)
                                    <li class="list-inline-item">
                                        <h5 class="mb-0 pl_cnt" id="{{ $plist->label }}">{{ $plist->value }}</h5>
                                        <p class="text-muted font-14">{{ $plist->label }}</p>
                                    </li>
                                @endforeach
                                </ul>

                                <div id="plantypeChart" class="morris-charts" style="height: 300px"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->
                    <div class="col-lg-6">
                        <div class="card m-b-20" style="height: 500px;">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Order Based on Promocode</h4>
                                <div id="orderpromoChart" class="ct-chart ct-golden-section"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->


                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Order Status</h4>

                                <div id="orderdelvChart" class="morris-charts" style="height: 300px"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->

                </div>

                <div class="row">
                    <div class="col-lg-12" >
                        <div class="card m-b-20" style="height: 400px;">
                            <div class="card-body">
                                <h4 class="mt-0 header-title">Auto Plan Status</h4>

                                <div id="autoplanstatChart" class="morris-charts" style="height: 300px"></div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card m-b-20" style="height: 1080px;">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Porting Based</h4>
                                @php $portingvals = json_decode($porting_vals); @endphp
                                @foreach($portingvals as $skey => $slist)
                                    <li class="list-inline-item">
                                        <h5 class="mb-0 pb_cnt" id="{{ str_replace(" ","",$slist->label) }}">{{ $slist->data }}</h5>
                                        <p class="text-muted font-14">{{ $slist->label }}</p>
                                    </li>
                                @endforeach

                                <div id="portingChart" class="ct-chart ct-golden-section" style="height: 320px"></div>
                            </div>
                        </div>
                    </div> <!-- end col -->

                    <div class="col-lg-6">
                        <div class="card m-b-20" style="height: 1080px;">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Sim Plan Renewed</h4>

                                <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                @php $simplanrenw = json_decode($simplan_renew); @endphp
                                @foreach($simplanrenw as $skey => $slist)
                                    <li class="list-inline-item">
                                        <h5 class="mb-0 sm_cnt" id="{{ preg_replace('/[^a-zA-Z0-9-_\.]/','', $slist->label) }}">{{ $slist->data }}</h5>
                                        <p class="text-muted font-14">{{ $slist->label }}</p>
                                    </li>
                                @endforeach
                                </ul>

                                <div  id="simplan-chart">
                                    <div id="simplan-chart-container" class="flot-chart" style="height: 320px">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div>
        </div>
<script>
    var uData          = JSON.parse('<?php echo $user_data; ?>');
    var payData        = JSON.parse('<?php echo $user_pay; ?>');
    var plantypeData   = JSON.parse('<?php echo $plan_type; ?>');
    var orderstatData  = JSON.parse('<?php echo $order_status; ?>');
    var orderstatSeries = JSON.parse('<?php echo $order_status_series; ?>');
    var orderpromo     = JSON.parse('<?php echo $order_promo; ?>');
    var simplanrenew   = JSON.parse('<?php echo $simplan_renew; ?>');
    var portingstatus  = JSON.parse('<?php echo $porting_status; ?>');
    var planstatus     = JSON.parse('<?php echo $plan_status; ?>');
    var planstatusSeries     = JSON.parse('<?php echo $plan_status_series; ?>');
</script>
<script src="{{ asset('js/reportchart.js')}}"></script>
@endsection
