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
                                <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/new-order')}}">Order</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/select-plan')}}">Plans</a></li>
                                <li class="breadcrumb-item active">Select Bolt-ons</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Select Bolt-ons</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-9">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <p class="text-muted font-14 m-b-30 notice-board-top">
                                <i class="mdi mdi-fullscreen noti-icon"></i>
                                Please click add on the bolt-ons you wish to purchase or continue to skip
                            </p>
                            <h4 class="mt-0 header-title">Additional Bundle</h4>

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
                                @if($mobileapp)
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#app" role="tab">
                                            <span class="d-none d-md-block">App Plan</span>
                                            <span class="d-block d-md-none">
                                                <i class="mdi mdi-home-variant h5"></i>
                                            </span>
                                        </a>
                                    </li>
                                @endif
                            </ul>

                            <div class="tab-content">
                                @foreach($providers as $key => $provider)
                                <div class="tab-pane {{($key == '0')?'active':''}} p-3" id="{{strtolower($provider->provider)}}" role="tabpanel">
                                    <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                        <h6><b>Additional Bolt-ons</b></h6>
                                        <table id="datatable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="text-right">Charge</th>
                                                    <th>Add</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($plans[$provider->id] as $plan)
                                                <tr>
                                                    <td>{{ $plan->plan_name }}</td>
                                                    <td class="text-right">£{{ Helper::number_format($plan->sell_price) }}</td>
                                                    <td style="width: 1px">
                                                        <a href="javascript:void(0);" class="text-muted add_bolt_ons" data-provider="{{$provider->id}}" data-toggle="tooltip" data-bolt="{{$plan->id}}" data-placement="top" title="" data-original-title="Add"><i class="mdi mdi-plus mdi-18px"></i></a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endforeach
                                @if($mobileapp)
                                <div class="tab-pane  p-3" id="app" role="tabpanel">
                                    <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                        <h6><b>Additional Bolt-ons</b></h6>
                                        <table id="datatable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="text-right">Charge</th>
                                                    <th>Add</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($plans['app'] as $plan)
                                                <tr class="odd">
                                                    <td>{{ $plan->plan_name }}</td>
                                                    <td class="text-right">£{{ Helper::number_format($plan->sell_price) }}</td>
                                                    <td style="width: 1px">
                                                        <a href="javascript:void(0);" class="text-muted add_bolt_ons" data-provider="app" data-toggle="tooltip" data-placement="top" data-bolt="{{$plan->id}}" title="" data-original-title="Add"><i class="mdi mdi-plus mdi-18px"></i></a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div>
                                <a href="{{ url('/select-plan') }}" class="btn btn-secondary waves-effect waves-light"><strong>Back</strong></a>
                                <a href="{{ url('/provision') }}" class="btn btn-success waves-effect waves-light pull-right"><strong>Continue</strong></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card m-b-20">
                        <div class="card-body right-nav">
                            <ul>
                                <li><a href="#"><b>Step 1</b><br/>Select voice and data</a></li>
                                <li><a href="#"><b>Step 2</b><br/>Configure voice and data</a></li>
                                <li><a href="#" class="selected"><b>Step 3</b><br />Select bolt-ons</a></li>
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

            <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

            <script type="text/javascript">
                $(document).ready(function () {
                    $('.datatable').DataTable();

                    $(document).on('click', '.add_bolt_ons', function(e){
                        var provider = $(this).data('provider');
                        var bolt = $(this).data('bolt');
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: base_url+'/selected-plan',
                            data: {bolt:bolt, provider:provider},
                            success:function(data){
                                $('#orderCustomLabel').text('Additional Bundle');
                                if (data.error) {
                                    $('#orderCustombody').html('<div class="text-danger">'+data.message+'</div>');
                                    $('#orderCustomModal').modal('show');
                                } else {
                                    $('#orderCustombody').html(data.html);
                                    $('#orderCustomModal').modal('show');
                                }
                            }
                        });
                    });

                    $(document).on('click', '#manage_bolt_ons', function(e){
                        $('#orderCustomModal').modal('hide');
                        $('.add_to_all').prop('disabled',false);
                        var formData = new FormData($('#bolt-form')[0]);
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: base_url+'/manage-bolt-ons',
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success:function(data){
                                $('#orderCustomLabel').text('Additional Bundle');
                                if (data.error) {
                                    $('#orderCustombody').html('<div class="text-danger">'+data.message+'</div>');
                                    $('#orderCustomModal').modal('show');
                                } else {
                                    $('#orderCustombody').html(data.html);
                                    $('#orderCustomModal').modal('hide');
                                }
                            }
                        });
                    });

                    $(document).on('click', '#add_to_all', function(e){
                        if (!$(this).prop('checked')) {
                            $('.add_to_all').prop('checked', false);
                        }else{
                            $('.add_to_all').prop('checked', true);
                        }
                    });

                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection
