@extends('layouts.home')
@section('content')
<style type="text/css">
    #credit_amount-error{
        position: absolute;
        top: 100%;
    }
    #custom_message-error{
        position: absolute;
        top: 100%;
    }
</style>
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('public/plugins/smartwizard/smart_wizard.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ URL('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Customer</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Customer</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                             <div id="user-accordion" class="parent-accor">
                                <div class="card">
                                    <a href="#collapseSearch" class="text-dark" data-toggle="collapse" aria-expanded="true" aria-controls="collapseOne">
                                        <div class="card-header p-3" id="headingOne"><h5 class="m-0">Search User <i class="pull-right mdi mdi-chevron-down"></i></h5></div>
                                    </a>
                                    <div id="collapseSearch" class="collapse {{ (!$user)?'show':'' }}"
                                        aria-labelledby="headingOne" data-parent="#user-accordion">
                                        <form action="{{ url('/user-details') }}" id="user-form" method="POST">
                                            @csrf
                                            <div class="row p-3">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>User CLI / Email</label>
                                                        <input type="text" class="form-control" name="identifier" value="{{isset($search['identifier'])?$search['identifier']:''}}" placeholder="CLI / Email">
                                                    </div>
                                                </div>
                                                @if(Helper::get_option('enable_switch_support'))
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Username</label>
                                                        <input type="text" class="form-control" name="user_name" value="{{isset($search['user_name'])?$search['user_name']:''}}" placeholder="Username">
                                                    </div>
                                                </div>
                                                @endif
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Order ID</label>
                                                        <input type="text" class="form-control text-uppercase" name="order_id" id="order_id" placeholder="Order ID" value="{{isset($search['order_id'])?$search['order_id']:''}}">
                                                    </div>
                                                </div>                                                                                                            
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Customer ID</label>
                                                        <input type="text" class="form-control text-uppercase" name="customer_id" placeholder="Customer ID" value="{{isset($search['customer_id'])?$search['customer_id']:''}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">                                           
                                                    <button type="submit" class="btn btn-success waves-effect waves-light pull-right">Search</button>
                                                    <button type="button" id="resetBtn" class="btn btn-secondary" style="float: right; margin-right:10px;">Reset</button>
                                                </div>                                    
                                            </div>
                                        </form>
                                    </div>
                                </div>                                
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>

            @if($user)
            <div class="row">
                <div class="col-md-2">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div class="profile-widget text-center customer-nav">
                                <h5>{{ $user->name }}</h5>
                                <p><b>{{ $user->userDetail->user_platform.$user->id }}</b></p>
                               <!--  <p><b>{{ $user->phone }}</b></p>
                                <p><b>{{ $user->email }}</b></p> -->                                
                                <a data-toggle="tooltip" title="" href="{{config('app.liveurl').'admin-authenticate/'.Crypt::encrypt($user->id) }}" target="_blank" data-original-title="Login" class="btn btn-sm btn-grey m-t-20">Login</a>
                                <a href="#" class="btn btn-sm btn-grey m-t-20" id="change_user_status">{{ ($user->status)?'Suspend':'Resume'}}</a>
                                @if(Helper::has_permission('users','delete'))
                                    <a href="#" class="btn btn-sm btn-red m-t-20 delete_user" user-id="{{$user->id}}">Delete</a> 
                                    <form id="delete_user_{{$user->id}}" method="post" action="{{ url('/delete-user') }}">
                                        @csrf
                                        <input type="hidden" name="user_key" value="{{ Crypt::encrypt($user->id) }}">                                    
                                    </form>
                                @endif
                                <input type="hidden" id="user_id" value="{{ Crypt::encrypt($user->id) }}">
                            </div>

                            <div class="customer-nav-1">
                                <ul class="customer-details">
                                    <li><a href="#" class="user-details selected " data-view="overview"><i class="mdi mdi-home"></i> Overview </a></li>
                                    <li><a href="#" class="user-details" data-view="basic_details"><i class="mdi mdi-table-edit"></i> Edit Details </a></li>
                                    <li><a href="#" class="user-details" data-view="auto_subscription"><i class="mdi mdi-autorenew"></i> Subscription </a></li>
                                    <li><a href="#" class="user-details" data-view="credit_debit"><i class="mdi mdi-wallet"></i> Credit/Debit </a></li>
                                    <li><a href="{{ url('/direct-debit/'.Crypt::encrypt($user->id))}}" ><i class="mdi mdi-bank"></i> Direct Debit </a></li>
                                    <li><a href="#" class="user-details" data-view="plan_history"><i class="mdi mdi-chart-pie"></i> Plan History </a></li>
                                    <li><a href="#" class="user-details" data-view="call_history"><i class="mdi mdi-phone-outgoing"></i> CDRs </a></li>
                                    <li><a href="#" class="user-details" data-view="transaction"><i class="mdi mdi-credit-card"></i> Transaction </a></li>
                                    <li><a href="#" class="user-details d-none" data-view="settings"><i class="mdi mdi-settings"></i> Settings </a></li>
                                    @if($user->stock_id)
                                    <li><a href="#" class="user-details sim-services" data-view="services"><i class="mdi mdi-wrench"></i> Sim/Services </a></li>
                                    <!-- <li><a href="#" class="user-details sim-info" data-view="sim_info"><i class="mdi mdi-sim"></i> Sim Info </a></li> -->
                                    @endif
                                    <li><a href="#" class="user-details invoice" data-view="invoice"><i class="mdi mdi-receipt"></i> Invoice </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-10" id="detail-view">
                    <div class="row">
                        <div class="col-md-6">
                            @if(Helper::has_permission('report'))
                            <div class="card m-b-20">
                                <div class="card-body revenue-overview-details">
                                    <h4 class="mt-0 header-title">Revenue Overview</h4>
                                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                        @php $active = 'active';  @endphp
                                        @foreach($reports as $key => $report)
                                        <li class="nav-item">
                                            <a class="nav-link {{$active}}" data-toggle="tab" href="#months{{$key}}" role="tab">
                                                <span class="d-none d-md-block">{{$key}} Months</span>
                                                <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                                            </a>
                                        </li>
                                        @php $active = ''; @endphp
                                        @endforeach
                                    </ul>

                                    <div class="tab-content">
                                        @php $active = 'active';  @endphp
                                        @foreach($reports as $key => $report)
                                        <div class="tab-pane {{$active}} p-3" id="months{{$key}}" role="tabpanel">
                                            <div class="row p-3">
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-purple">{{$currency.Helper::number_format($report['revenue'])}}</span>Revennue
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-blue-grey">{{$currency.Helper::number_format($report['expense'])}}</span>Expense
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-blue-grey">{{$currency.Helper::number_format($report['refund'])}}</span>Refund
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-blue-grey">{{$currency.Helper::number_format($report['fee'])}}</span>Fee
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-brown">{{$currency.Helper::number_format($report['vat'])}}</span>Vat
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-teal">{{$currency.Helper::number_format($report['profit'])}}</span>Profit
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php $active = ''; @endphp
                                        @endforeach                                         
                                    </div>
                                </div>
                            </div>
                            @endif
                            <!-- <div class="card m-b-20">
                                <div class="card-body parent-account-css">
                                    <h4 class="mt-0 header-title">Balance</h4>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mini-stat-info"><span class="counter text-purple">1.5 GB</span>remaining of 1.5 GB<br />renew in 48 mins</div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <button type="button" class="btn btn-outline-secondary waves-effect">Check Balance</button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->

                            <div class="card m-b-20">
                                <div class="card-body parent-account-css">
                                    <h4 class="mt-0 header-title">Plan Details</h4>
                                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#sim-plan" role="tab">
                                                <span class="d-none d-md-block">SIM Plan</span><span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                                            </a>
                                        </li>
                                        @if(Helper::get_option('enable_switch_support'))
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#mobile-plan" role="tab">
                                                <span class="d-none d-md-block">Mobile APP Plan</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active p-3" id="sim-plan" role="tabpanel">
                                            <!-- <div class="row m-b-20">
                                                <div class="col-md-8"><div class="mini-stat-info"><span class="counter text-purple">20</span>calling minutes remaining</div></div>
                                                <div class="col-md-4 text-right"><button type="button" class="btn btn-outline-secondary waves-effect">Recharge</button></div>
                                            </div> -->
                                            <div class="row plan-data-listing">
                                                <div class="col-md-6">Data</div>
                                                @if($plan && $plan->prorata)
                                                    @php
                                                      $planstart   = $plan->created_at;
                                                      $data_limit = $plan->plan->data_limit;
                                                      $planend     = Carbon::parse($planstart)->endOfMonth();
                                                      $noofdays    = Carbon::parse($planstart)->daysInMonth;
                                                      $daysbetween = Carbon::parse($planstart)->diffInDays($planend) + 1;
                                                      $usagelimit  = round((($data_limit/$noofdays) * $daysbetween),2);
                                                    @endphp
                                                    <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{($plan)?round($plan->data_usage / pow(1024, 3),2):'0'}} GB</span>Out of {{($plan)?(($plan->plan->data_limit != 0) ? $usagelimit:'Unlimited'):'0'}} GB</div></div>
                                                @elseif($plan)
                                                    <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{($plan)?round($plan->data_usage / pow(1024, 3),2):'0'}} GB</span>Out of {{($plan)?(($plan->plan->data_limit != 0) ? $plan->plan->data_limit:'Unlimited'):'0'}} GB</div></div>
                                                @else
                                                    <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">0</span>Out of 0</div></div>
                                                @endif
                                            </div>
                                            <div class="row plan-data-listing">
                                                <div class="col-md-6">Voice</div>
                                                <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{($plan)? Helper::secondsToTime($plan->call_usage):'00:00:00'}} </span>Out of {{($plan)?$plan->plan->call_limit:0}} Min</div></div>
                                            </div>                                           
                                            <div class="row plan-data-listing">
                                                <div class="col-md-6">SMS</div>
                                                <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{($plan)? $plan->sms_usage / 60:'0'}} SMS</span>Out of {{($plan)?$plan->plan->msg_limit:0}} SMS</div></div>
                                            </div>
                                        </div>
                                        @if(Helper::get_option('enable_switch_support'))
                                        <div class="tab-pane p-3" id="mobile-plan" role="tabpanel">
                                            <div class="row plan-data-listing">
                                                <div class="col-md-6">Balance Credit</div>
                                                <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{isset($user->balance->balance_amount) ? $user->balance->balance_amount : 0}}</span></div></div>
                                            </div>                                             
                                            <div class="row plan-data-listing">
                                                <div class="col-md-6">Balance Minutes</div>
                                                <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{isset($user->balance->balance_minutes) ? $user->balance->balance_minutes : 0 }} min</span>Out of {{ '-' }} min</div></div>
                                            </div>                                                                                       
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card m-b-20">
                                <div class="card-body">
                                    <h4 class="mt-0 header-title">Account</h4>
                                    <div class="cus-right-box"><h6>Parent Account</h6><br />
                                        <div class="row">
                                            <div class="col-md-1 col-xs-1"><i class="mdi mdi-sim sim-color"></i></div>
                                            <div class="col-md-10 col-xs-10"><span><b>{{$parent->name }}</b> </span> {{'0'.ltrim($parent->phone,'+44')}}</div>
                                            <div class="col-md-1 col-xs-1">
                                                <form id="show_user_{{$parent->id}}" method="post" action="{{url('/user-details')}}">
                                                    @csrf<input type="hidden" name="identifier" value="{{$parent->phone}}">
                                                    <a class="show_user_data" user-id="{{$parent->id}}"><i class="mdi mdi-eye"></i></a>
                                                </form>
                                                <!-- <a href="parent-account.html"><i class="mdi mdi-eye"></i></a></div> -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="cus-right-box"><h6>Child Account</h6><br />
                                        @foreach ($children as $child)
                                        @php $plan_data = $child->userPlan(); @endphp
                                        <div class="row cus-bor-btm">
                                            <div class="col-md-1 col-xs-1"><i class="mdi mdi-sim sim-color"></i></div>
                                            <div class="col-md-10 col-xs-10"><span><b>{{$child->name}}</b> </span> 0{{ltrim($child->phone,'+44') }}</div>
                                            <div class="col-md-1 col-xs-1">
                                                <form id="show_user_{{$child->id}}" method="post" action="{{url('/user-details')}}">
                                                    @csrf<input type="hidden" name="identifier" value="{{$child->phone}}">
                                                    <a class="show_user_data" user-id="{{$child->id}}"><i class="mdi mdi-eye"></i></a>
                                                </form>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card m-b-20">
                                <div class="card-body note-body">
                                    <h4 class="mt-0 header-title">Notes</h4>
                                    <div class="card m-b-20">
                                        @foreach($notes as $note)

                                        <div class="card-header">
                                            <!-- <a href="javascript:void(0);" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-close font-18"></i></a> -->
                                            <p class="font-16">{{ $note->note }}</p>
                                            <footer class="blockquote-footer text-muted">
                                            {{ $note->handled_by }} <cite>{{ Helper::date_format($note->created_at) }}</cite>
                                            </footer>
                                        </div>
                                        @endforeach                                                                            
                                    </div>
                                    <div class="m-t-20">
                                        <label class="text-muted">Add Note</label>
                                        <textarea id="note" class="form-control" maxlength="225" rows="3" placeholder=""></textarea>
                                    </div>
                                    <div class="m-t-20">
                                        <span id="error-note" class="text-danger"></span>
                                        <button type="button" class="btn btn-success waves-effect waves-light add_note pull-right"><strong>Add</strong></button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card m-b-20">
                                <div class="card-body">
                                    <div id="accordion" class="parent-accor">
                                        <div class="card">
                                            <div class="card-header p-3" id="headingOne">
                                                <h6 class="m-0"><a href="#collapseOne" class="text-dark" data-toggle="collapse" aria-expanded="true" aria-controls="collapseOne">
                                                Recharge</a></h6>
                                            </div>
                                            <div id="collapseOne" class="collapse show"
                                                aria-labelledby="headingOne" data-parent="#accordion">
                                                <div class="card-body">
                                                    <label>Vodafone Number</label>
                                                    <input type="search" class="form-control form-control-sm m-b-20" placeholder="" aria-controls="datatable">
                                                    <button type="button" class="btn btn-success waves-effect waves-light">Recharge Now</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header p-3" id="headingTwo">
                                                <h6 class="m-0"><a href="#collapseTwo" class="text-dark collapsed" data-toggle="collapse" aria-expanded="false" aria-controls="collapseTwo"> My Statement </a>
                                                </h6>
                                            </div>
                                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                                                <div class="card-body">My Statement</div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header p-3" id="headingThree">
                                                <h6 class="m-0"> <a href="#collapseThree" class="text-dark collapsed" data-toggle="collapse" aria-expanded="false" aria-controls="collapseThree"> Recharge History </a> </h6>
                                            </div>
                                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                                                <div class="card-body">Recharge History</div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header p-3" id="headingFourth">
                                                <h6 class="m-0"> <a href="#collapseFourth" class="text-dark collapsed" data-toggle="collapse" aria-expanded="false" aria-controls="collapseFourth"> Invoice History </a> </h6>
                                            </div>
                                            <div id="collapseFourth" class="collapse" aria-labelledby="headingFourth" data-parent="#accordion">
                                                <div class="card-body"> Invoice History </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="card m-b-20">
                        <div class="card-body parent-account-css">
                            <h4 class="mt-0 header-title">Balance</h4>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mini-stat-info"><span class="counter text-purple">1.5 GB</span>remaining of 1.5 GB<br />renew in 48 mins</div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button type="button" class="btn btn-outline-secondary waves-effect">Check Balance</button>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="row m-b-20">
                        <div class="col-md-8"><div class="mini-stat-info"><span class="counter text-purple">Balance Credit</span></div></div>
                        <div class="col-md-4 text-right">{{isset($user->balance->balance_amount) ? $user->balance->balance_amount : 0}}</div>
                        <! -- <button type="button" class="btn btn-outline-secondary waves-effect">Recharge</button> - - >
                    </div>  -->                                                           
                </div>
            </div>
            <script type="text/javascript">
                $(document).ready(function(){
                    $(document).on('click','#reset_otp_limit',function () {
                        var $this = $(this);
                        $this.prop('disabled', true);
                        var user_id = "{{ $user->id }}";
                        var phone = "{{ $user->phone }}";
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            data: {phone:phone,user_id:user_id},
                            url: base_url+'/reset-otp-try',
                            success: function(response){ 
                                $this.addClass('d-none');                   
                            }
                        });
                    });
                });

            </script>
            @endif
            
            <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

            <script src="{{ asset('public/js/jquery.creditCardValidator.js') }}"></script>  
            <script src="{{ asset('public/js/jquery.mask.js') }}"></script> 
            <script src="{{ asset('public/plugins/smartwizard/smart_wizard.js') }}"></script>

            <script type="text/javascript">
                $(document).ready(function(){
                    
                    // $('#dataTable').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search: '' },});
                    var stripe   = Stripe("{{ config('app.stripe_api_key') }}");
                      var card;
                      var elements = stripe.elements({
                        fonts: [
                          {
                            family: 'Open Sans',
                            weight: 400,
                            src: 'local("Open Sans"), local("OpenSans"), url(https://fonts.gstatic.com/s/opensans/v13/cJZKeOuBrn4kERxqtaUH3ZBw1xU1rKptJj_0jans920.woff2) format("woff2")',
                            unicodeRange: 'U+0000-00FF, U+0131, U+0152-0153, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2212, U+2215',
                          },
                        ]
                      });
                      function stripeElements(){
                          card = elements.create('card', {
                            hidePostalCode: true,
                            style: {
                              base: {
                                iconColor: '#F99A52',
                                color: '#32315E',
                                lineHeight: '48px',
                                fontWeight: 400,
                                fontFamily: '"Open Sans", "Helvetica Neue", "Helvetica", sans-serif',
                                fontSize: '15px',

                                '::placeholder': {
                                  color: '#CFD7DF',
                                }
                              },
                            }
                          });
                          card.mount('#card-element');

                          card.on('change', function(event) {
                              setOutcome(event);
                              $(".stripe-error").html('');
                          });
                      }
                      if($('#card-element').length){
                        stripeElements();
                      }

                        var btnFinish = $('<button></button>').text('Finish')
                                      .addClass('btn btn-info finishBtn')
                                      .css('display','none');

                        $(document).on('click','.finishBtn',function(e){
                            e.preventDefault();
                            finishPayment();
                        });

                        function init_creditdebit_wizard(){
                            $('#creditdebitWizard').smartWizard({
                                selected: 0,
                                theme: 'arrows',
                                transitionEffect:'fade',
                                autoAdjustHeight: true,
                                enableFinishButton: true,
                                enableURLhash:true,
                                toolbarSettings: {
                                    toolbarPosition: 'bottom', // both bottom
                                    toolbarExtraButtons: [btnFinish]
                                },
                                keyboardSettings: {
                                  keyNavigation: false
                              },
                            });
                            $("#creditdebitWizard").on("showStep", function(e, anchorObject, stepNumber, stepDirection, stepPosition) {
                                $('.finishBtn').hide();
                                $('.btn-next').show();
                                if(stepPosition == 'last'){
                                  $('.finishBtn').show();
                                  $('.btn-next').hide();
                                }
                            });
                      
                            $("#creditdebitWizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) {

                              var payment_for = $('input[name="payment_for"]:checked').val();

                              if(stepNumber == 0){
                                $(".debit_div,.debit_msg_div,.calculated_div").hide();
                                $('.tax_div,.amount_div').show();
                                if(payment_for == 'debit'){
                                  $(".debit_div").show();
                                }else if(payment_for == 'addcard'){
                                  getcalculated();
                                  $('.tax_div,.amount_div').hide();
                                }
                              }else if(stepNumber == 1){
                                if(stepDirection == 'forward'){

                                    if(payment_for != 'addcard'){
                                        $validate = step2validate();
                                        if($validate){
                                            if(payment_for == 'deduct'){
                                                creditDebit();
                                                return false;
                                            }
                                        }else{
                                            return false;
                                        }
                                    }
                                }
                              }
                              return true;
                            });
                        }
                        function step2validate(){
                           if($("#custom_amount").val() == "" && $( "#credit_amount option:selected" ).val() == ""){
                            $('.amt_error').html('This field is required');
                            return false;
                           }else if($('.notify').val() == 1){
                            $('.msg_error').html('This field is required');
                            return false
                           }
                           return true;
                        }
                      function finishPayment(event){
                        $('.finishBtn').prop('disabled',true);
                        var card_type   = $('input[name="credit_card"]').val();
                        var gateway     = $('input[name="gateway"]').val();
                        
                        if($("#pay-form").valid()){
                          if(gateway == 'Stripe' && card_type == 'new'){
                              stripe.createToken(card).then(setOutcome);
                          }else{
                             creditDebit(); 
                          }
                        }
                      }
                      function creditDebit(stripeToken = ''){
                        var $this       = $('.finishBtn');
                        var formData    = new FormData($('#pay-form')[0]);
                        var user_id     = $("#user_id").val();
                        formData.append('user_id', user_id);
                        if(stripeToken != ""){
                          formData.append('stripeToken', stripeToken);
                        }
                        $.ajax({
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          type: 'POST',
                          url: base_url+'/credit-debit-manage',
                          data: formData,
                          processData: false,
                          contentType: false,
                          dataType: 'json',
                          beforeSend: function(){
                              $this.html('Processing..');
                              $this.addClass("disabled").prop("disabled", true);
                          },
                          complete: function(){
                              $this.html('Finish');
                              $this.removeClass("disabled").prop("disabled", false);
                          },
                          success:function(data){                     
                              if(data.status == 200) {
                                  alertify.success(data.message);
                                  $("ul.customer-details li:first-child a").click();
                              }else{
                                  $("ul.customer-details li:first-child a").click();
                                  alertify.error(data.message);
                              }
                          }
                        });
                      }
                      function setOutcome(result) {
                        var errorElement = document.querySelector('.stripe-error');
                        errorElement.classList.remove('visible');
                        if (result.token) {
                          // Use the token to create a charge or a customer
                          // https://stripe.com/docs/charges
                          //successElement.querySelector('.token').textContent = result.token.id;
                          var stripeToken = result.token.id;
                          creditDebit(stripeToken);

                        } else if (result.error) {
                          errorElement.textContent = result.error.message;
                          errorElement.classList.add('visible');
                        }
                      }
                        $(document).on( 'click', '#custom_amount_check', function(){
                            if($(this).is(':checked')){
                                $(".cust_amt").show();
                                $(".dropdwn_amt").hide();
                                $("input[name='custom_amount']").prop('required',true);
                                $(this).val(1);
                                $("#credit_amount option:selected").prop("selected", false)
                            }else{
                                $(".cust_amt").hide();
                                $(".dropdwn_amt").show();
                                $("input[name='custom_amount']").prop('required',false);
                                $(this).val(0);
                            }
                        });
                        $(document).on('click', '.notify', function(){
                           if($(this).is(':checked')){
                            $(".debit_msg_div").show();
                            $(this).val(1);
                            $("input[name='custom_message']").prop('required',true);
                           }else{
                            $(".debit_msg_div").hide();
                            $(this).val(0);
                            $("input[name='custom_message']").prop('required',false);
                           } 
                        });
                        $(document).on('click', '.radio_card_list', function(){
                            var creditcard = $(this).val();
                            $('input[name="credit_card"]').val(creditcard);
                            card.clear(); 
                            $('.card_form').addClass('d-none');          
                        });
                        $(document).on('click', '.gateway', function(e){
                            $('.gateway').removeClass('active');
                            $(this).addClass('active'); 
                            var gateway = $(this).data('gateway');
                            var user_id = $('#user_id').val();
                            var gatewayname = $(this).attr('gateway-name');

                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',                                                
                                url: base_url+'/credit-debit-gateway',
                                data: {gateway:gateway,user_id:user_id},                            
                                success:function(data){
                                    $('.payform_div').html(data.html);
                                    if(gatewayname == 'Stripe'){
                                      if(card != undefined){
                                        card.destroy();
                                      }
                                      stripeElements();
                                    }                                  
                                }
                            }); 
                        });

                        $(document).on( 'change', '.collect_amount,.payment_for,.custom_amount_check', function(){
                          if($("#custom_amount").val() != "" || $( "#credit_amount option:selected" ).val() != ""){
                            getcalculated();
                          }
                          $('.amt_error').html('');
                        });
                        function getcalculated(){
                          var user_id     = $("#user_id").val();
                          var formData    = new FormData($('#pay-form')[0]);
                          formData.append('user_id', user_id); 
                          $.ajax({
                              headers: {
                                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                              },
                              type: 'POST',
                              url: base_url+'/cal-credit-debit',
                              data: formData,
                              processData: false,
                              contentType: false,
                              dataType: 'json',
                              success:function(data){
                                  $(".calculated_div").html('');                     
                                  if(data.status == 200) {
                                   $(".calculated_div").html(data.page);
                                   $('.calculated_div').show();     
                                  }
                              }
                          });
                        }

                        $(document).on('click', '.btn_add_new_card', function(e){ 
                            $('.card_form').removeClass('d-none');
                            $("input[name='card_list']").prop('checked',false);
                            if(!$('.card_form').hasClass('d-none')){                                                    
                                $("input[name='credit_card']").val('new');
                                $("input[name='card_list']").prop('required',false);
                            }else{
                                $("input[name='card_list']").prop('required',true);
                            }
                        });

                    $("#pay-form").validate({
                        // errorClass: "invalid form-error",
                        // errorElement: 'div',
                        errorPlacement: function(error, element) {                       
                            element.addClass('border border-danger');
                            error.insertAfter(element);
                        },
                        ignore: ":hidden",
                        rules: {
                            card_number:{
                                required: {
                                    depends: function () { 
                                      return ($("input[name='credit_card']").val() == 'new')?true:false; 
                                    }
                                },
                                regex:/^[0-9-]{19}$/,
                            },
                            card_holder:{
                                required: {
                                    depends: function () { 
                                      return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                                    }
                                },
                                lettersonly: true
                            },
                            expiry_year: {
                                required: {
                                    depends: function () { 
                                      return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                                    }
                                },
                                maxlength: 2,
                                minlength: 2,
                                min: 20
                            },
                            expiry_month: {
                                required: {
                                    depends: function () { 
                                      return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                                    }
                                },
                                maxlength: 2,
                                minlength: 1,
                                max: 12,min: 1
                            },
                            card_cvv: {
                                required: {
                                    depends: function () { 
                                      return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                                    }
                                },
                                maxlength: 4,
                                minlength: 3,
                                number:true,
                            },
                            card_postcode: {
                                required: {
                                    depends: function () { 
                                      return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                                    }
                                },
                                regex:/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9][A-Za-z]?))))\s?[0-9][A-Za-z]{2})$/
                            },
                            card_street: {
                                required: {
                                    depends: function () { 
                                      return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                                    }
                                },
                            },
                            credit_card: 'required',
                        },
                        messages: {
                            card_holder:{
                                lettersonly:"Enter a valid card holder name!",
                            }, 
                            card_postcode: {
                                required:"Enter your postal code!",
                                regex:"Invalid Postalcode",               
                            },
                            expiry_year: "Enter card expiry year!", 
                            expiry_month: "Enter card expiry month!",                  
                            terms_cond: 'Agree the Terms and Conditions to proceed!.', 
                            credit_card: 'Please choose a credit card or add a new one'                
                        }
                    });

                    $.validator.addMethod(
                        "regex",
                        function(value, element, regexp) {
                            var re = new RegExp(regexp);
                            return this.optional(element) || re.test(value);
                        },
                        "Please check your input."
                    );

                    $('.card_number').mask('0000-0000-0000-0000');

                    $(document).on( 'change', '#card_number', function(){                  
                        var result = $("#card_number").validateCreditCard();
                        if(result.card_type !== null){
                            $('#card_type').val(result.card_type.name);
                        }else{
                            $('#card_type').val('Card');
                        }
                    });

                    $(document).on('click', '#resetBtn', function(e){                        
                        $('#user-form input').val('');
                    });
                    
                    $(document).on('click', '.user-details', function(e) {
                        $('.user-details').removeClass('selected');
                        $(this).addClass('selected');
                        var view = $(this).data('view');
                        var user_id = $('#user_id').val();
                        // $('#preloader').show();
                        if(view == 'services'){
                            servicelist = [];
                            networklist = [];
                        }
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',                                                
                            url: base_url+'/user-data',
                            data: {user_id:user_id,page:view},
                            beforeSend: function(){
                                $("#preloader,#status").show();
                            },
                            complete: function(){
                                $("#preloader,#status").hide();
                            },
                            success:function(data){ 
                                // $('#preloader').hide();
                                if (data.error) {
                                    $('#detail-view').html('<div class="text-danger">'+data.message+'</div>');
                                } else {
                                    $('#detail-view').html(data.html);
                                    if(view == 'credit_debit'){
                                        init_creditdebit_wizard();
                                    }
                                    // if(view == 'invoice'){
                                    //     invoiceDatatable();
                                    // }                                  
                                    $('.dataTables_filter input').attr('placeholder', 'Search');
                                }
                            }
                        });
                    });
 
                    $(document).on('click', '.show_user_data', function(e) {              
                        e.preventDefault();   
                        var id = $(this).attr('user-id');                
                        $('#show_user_'+id).submit();
                    });

                    $(document).on('click', '.add_note', function(e) {
                        var $this = $(this);
                        var note = $('#note').val();
                        var user_id = $('#user_id').val();                        
                        if(note != ''){
                            $this.prop('disabled', true);
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',                                                
                                url: base_url+'/save-note',
                                data: {user_id:user_id,note:note},
                                success:function(data){ 
                                    $this.prop('disabled', false);
                                    if (data.success) {
                                        $('#note').val('');
                                    } else {
                                        $('#error-note').html(data.message);
                                    }
                                }
                            });
                        }
                    });

                    $(document).on('click','.delete_user',function(){
                        var id = $(this).attr('user-id'); 
                        $('#orderCustomLabel').text('Delete Account');
                        $('#orderCustombody').html('<div class="form-group">Do you really want to delete this contact? <div id="custom_status"> </div> </div> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="confirm_delete_user" data-id="'+ id +'" class="btn btn-danger pull-right">Delete</button>'); 
                        $('#orderCustomModal').modal('show');            
                    });

                    $(document).on('click','#confirm_delete_user',function(){      
                        var id = $(this).data('id');                   
                        $('#delete_user_'+id).submit();
                    });

                    $(document).on('click','#change_user_status',function(){
                        var $this = $(this);
                        var user_id = $('#user_id').val();
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            data: {'user_id' :user_id},
                            url: base_url+'/manage-status',
                            success: function(response){ 
                                if(response.success){
                                    if(response.status){
                                        $this.text('Suspend');                                    
                                    }else{
                                        $this.text('Resume');                                    
                                    }
                                }else{
                                    alert(response.message);
                                }
                            }
                        });
                    });

                    $(document).on('click','#confirm_delete_card',function(){ 
                        var $this = $(this); 
                        var card_id = $this.data('id');                        
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: base_url+'/remove-card',
                            data: {card_id:card_id},
                            dataType: 'json',
                            success: function(response){                   
                                if(response.success){
                                    $('#card_item_'+response.card_id).addClass('d-none');
                                    $('#custom_status').html('<small class="text-success">'+response.message+'</small>');                                               
                                }else{                                     
                                    $('#custom_status').html('<small class="text-danger">'+response.message+'</small>');
                                }
                            }
                        });
                    });
   
                    $(document).on('click', '.change_subsciption', function(e) {
                        e.preventDefault();
                        var $this = $(this);
                        var status = $this.data('status');    
                        var renew_id = $this.data('renew_id');                                    
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',                                                
                            url: base_url+'/manage-subscription',
                            data: { renew_id:renew_id,action:status },
                            success:function(data){                                         
                                if (data.success) {
                                    if(data.status == 1){
                                        $('#orderCustomLabel').text('Manage Subscription');
                                        $('#orderCustombody').html(data.html); 
                                        $('#orderCustomModal').modal('show');
                                    }else{
                                        $this.prop('checked',true);
                                        alert(data.message);
                                    } 
                                } else {
                                    $this.prop('checked',false);
                                    alert(data.message);
                                }
                            }
                        });
                    });

                    $(document).on('click', '.confirm_subscription_cancel', function(e) {
                        // $.ajax({
                        //     headers: {
                        //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        //     },
                        //     type: 'POST',
                        //     data: {'autoplan_id' :autoplan_id,'status_type':result,'user_id':userid,'amount':amount},
                        //     url: '<?php //echo url('/'); ?>/update-autoplan-status',
                        //     success: function(response){ 
                        //         $('#loadingsign').hide();
                        //         if(response.status == 'success'){
                        //             thiselem.attr('data-status',0);
                        //         }
                        //         else if(response.status == 'failure'){
                        //             thiselem.prop('checked',true);
                        //         }
                        //         alert(response.message);
                        //     }
                        // });  
                    });

                    $(document).on('click', '.change_subscription_card', function(e) {  
                        var card_id = $("input[name='user_credit_card']:checked"). val();
                        if(card_id){
                            var renew_id = $('#subscription_renew_id').val();
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',                                                
                                url: base_url+'/change-subscription-card',
                                data: { renew_id:renew_id,card_id:card_id },
                                success:function(data){                                         
                                    if (data.success) {
                                        $('#orderCustomModal').modal('hide');                                   
                                    } else {
                                        $('#sub_status').text(data.message);
                                    }
                                }
                            });
                        }else{
                            alert('Please select a card');
                        }
                    });

                    $(document).on('change','input[name="renewal_type"]', function () {
                        if($(this).val() == 2){
                            $('#mode_select').addClass('d-none');
                            $('#transaction_details').addClass('d-none');
                        }else{
                            $('#mode_select').removeClass('d-none');
                            if($('input[name="payment_mode"]:checked').val() == 2){             
                                $('#transaction_details').removeClass('d-none');
                            }
                        }       
                    });

                    $(document).on('change','input[name="payment_mode"]', function () {
                        if($(this).val() == 2){
                            $('#transaction_details').removeClass('d-none');
                        }else{
                            $('#transaction_details').addClass('d-none');
                        }
                    });

                    $(document).on('click','#subscriptionRenewal', function () {    
                        $('#subscriptionRenewal').prop('disabled',true);            
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            data: $('#renewal-form').serialize(),
                            url: base_url+'/subscription-renewal',
                            success: function(response){                
                                if(response.success){                   
                                    location.reload();
                                }else{
                                    $('#subscription_error').html('<div class="alert alert-danger">'+response.message+'</div>');
                                    // $('#subscriptionRenewal').prop('disabled', false); 
                                    setTimeout(function() {
                                        $('.alert.alert-danger').fadeOut('fast');
                                    }, 5000);
                                }
                            }
                        });
                    });

                    $(document).on('click', '.action_refund', function(e) {              
                        e.preventDefault();                    
                        var tx_id = $(this).data('id');
                        var amount = $(this).data('amount');
                        var currency = $(this).data('currency');
                        $('#orderCustomLabel').text('Refund Transaction');
                        $('#orderCustombody').html('<label class="form-label">Amount</label> <div class="input-group mb-3"> <input type="hidden" name="txn_id" id="txn_id" value="'+ tx_id +'"> <div class="input-group-prepend"><span class="input-group-text">'+ currency +'</span></div> <input type="text" name="refund_amount" id="refund_amount" class="form-control" required placeholder="Amount" value="'+ amount +'" max="'+ amount +'"></div><div class="form-group"><label class="form-label">Description</label> <input type="text" name="description" id="description" class="form-control" required placeholder="Description"> <div id="custom_status"></div> </div> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="action_refund_process" class="btn btn-danger pull-right">Refund</button>'); 
                        $('#orderCustomModal').modal('show');
                    });
                    
                    $(document).on('click', '#action_refund_process', function(e) {              
                        e.preventDefault();
                        $(this).attr('disabled','true');
                        var txn_id = $('#txn_id').val();
                        var amount = $('#refund_amount').val();
                        var description = $('#description').val();
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',                                                
                            url: base_url+'/refund-process',
                            data: {txn_id:txn_id, amount:amount, description:description},
                            success:function(data){ 
                                if (data.error) {
                                    $('#custom_status').html('<div class="text-danger">'+data.message+'</div>');
                                } else {
                                    $('#custom_status').html('<div class="text-success">Refund Processed successfully</div>');
                                    table.draw();
                                }
                                $('#action_refund_process').attr('disabled', false);
                                
                            }
                        });
                    });

                    function invoiceDatatable(){
                        var table = $('#invoicetable').DataTable({
                            dom: 'Bfrltip',

                            responsive: true,
                            "bSort" : false,
                            language: { search: "" },
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: 'invoice-list',
                                data: function (d) {
                                    d.from    = $('input[name=from]').val();
                                    d.to      = $('input[name=to]').val();
                                    d.user_id  = $('#user_id').val();
                                }
                            },
                            // "createdRow": function (row, data, rowIndex) {
                            //     $.each($('td', row), function (colIndex) {
                            //         $(this).attr('data-th', theaddata[colIndex]);
                            //     });
                            // },
                            "dataType": "jsonp",
                            "columns": [
                            {"data": "DT_RowIndex", "name": "DT_RowIndex"},
                            // {"data" : function (data) {
                            //     return moment(data.invoicedate).format('YYYY');
                            // },"name":"year"},
                            // {"data" : function (data) {
                            //     return moment(data.invoicedate).format('MMMM');
                            // },"name":"month"},
                            {"data" : "year","name":"year"}, 
                            {"data" : "month","name":"month"}, 
                            {"data" : "amount","name":"amount"},                       
                            {"data" : "vat","name":"vat"},
                            {"data" : "total","name":"total"},
                            { 
                                "data": "downloadurl",
                                "render": function(data, type, row, meta){
                                    // data = '<button class="single_option btn_small btn_br_20" id="searchBtn">Generate</button>'
                                    data = '<a href="'+base_url+'/print-pdf/'+data+'"><button class="single_option btn_small btn_br_20" >Generate</button></a>'
                                    return data;
                                }
                            } 
                            ],
                            "columnDefs": [
                            {"defaultContent": "-","targets": "_all"}
                            ]
                            // "fnDrawCallback": function(oSettings) {                 
                            // if (oSettings._iDisplayLength >= oSettings.fnRecordsDisplay()) {
                            // $(oSettings.nTableWrapper).find('#invoicetable_previous,#invoicetable_next').hide();
                            // }
                            // }

                        });
                    }
                    $(document).on('click', '#createinvoice', function(e) {
                        alertify.dismissAll();
                        if($("#invoice_form").valid()){
                            var selmonth = $("#invoice_month").val();
                            var selyear  = $("#invoice_year").val();
                            var seldate  = new Date(atob(selyear),atob(selmonth));
                            var current  = new Date();
                            var user_id  = $("#user_id").val();
                            if(Date.parse(seldate) > Date.parse(current)){
                               alertify.error('Unable to generate current month and future months invoice');
                               return false;
                            }
                            var URL = base_url+'/generate-invoices/'+user_id+'-'+selmonth+'-'+selyear;
                            window.location = URL;
                        }
                    });
                    var servicelist = [];
                    var networklist = [];
                    $(document).on('click', '.custom_manage_bars', function(e) {
                        var $this     = $(this);
                        var status = $this.attr('data-status');    
                        var bar_id = $this.attr('data-bar_id');
                        status = (status == 1) ? 0 : 1;
                        $(this).attr('data-status',status);
                        var index = servicelist.findIndex(o => o.bar_id === bar_id);
                        if (index > -1) {
                            servicelist.splice(index, 1);
                        }else{
                            servicelist.push({bar_id, status});
                        }
                    });
                    $(document).on('click', '.custom_bar_apply', function(e) {
                        e.preventDefault();
                        var $this = $(this);
                        var user_id  = $('#user_id').val(); 
                        if(servicelist.length == 0){
                            alertify.error('Choose any bars to apply changes');
                            return;
                        }
                        alertify.confirm('Bars Confirmation', 'Are you sure you want to apply changes?',
                        function(){                                  
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',                                                
                                url: base_url+'/services-change',
                                data: { bars:JSON.stringify(servicelist),user_id:user_id,requesttype:1 },
                                beforeSend: function(){
                                    $("#preloader,#status").show();
                                },
                                complete: function(){
                                    $("#preloader,#status").hide();
                                },
                                success:function(data){                                         
                                    if(data.status == 200){
                                        alertify.success(data.message);
                                        $.each(servicelist,function(index,val){
                                            $("[data-bar_id="+val.bar_id+"]").prop('disabled',true); 
                                        });
                                    }else{
                                        revertOpted(servicelist);
                                        alertify.error(data.message);
                                    }
                                    servicelist = [];
                                }
                            });  
                        },function(){ alertify.error('Option cancelled'); revertOpted(servicelist); servicelist = [];});
                    });
                    $(document).on('click', '.custom_manage_network', function(e) {
                        var $this     = $(this);
                        var status = $this.attr('data-status');    
                        var bar_id = $this.attr('data-bar_id');
                        status = (status == 1) ? 0 : 1;
                        $(this).attr('data-status',status);
                        var index = networklist.findIndex(o => o.bar_id === bar_id);
                        if (index > -1) {
                            networklist.splice(index, 1);
                        }else{
                            networklist.push({bar_id, status});
                        }
                    });
                    $(document).on('click', '.custom_network_apply', function(e) {
                        e.preventDefault();
                        var $this = $(this);
                        var user_id  = $('#user_id').val(); 
                        if(networklist.length == 0){
                            alertify.error('Choose any services to apply changes');
                            return;
                        }
                        alertify.confirm('Services Confirmation', 'Are you sure you want to apply changes?',
                        function(){                                  
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',                                                
                                url: base_url+'/services-change',
                                data: { bars:JSON.stringify(networklist),user_id:user_id,requesttype:2 },
                                beforeSend: function(){
                                    $("#preloader,#status").show();
                                },
                                complete: function(){
                                    $("#preloader,#status").hide();
                                },
                                success:function(data){                                         
                                    if(data.status == 200){
                                        alertify.success(data.message);
                                        $.each(networklist,function(index,val){
                                            $("[data-bar_id="+val.bar_id+"]").prop('disabled',true); 
                                        });
                                    }else{
                                        revertOpted(networklist);
                                        alertify.error(data.message);
                                    }
                                    networklist = [];
                                }
                            });  
                        },function(){ alertify.error('Option cancelled'); revertOpted(networklist); networklist = [];});
                    });
                    function revertOpted(list){
                        $.each(list,function(index,val){
                            if($("[data-bar_id="+val.bar_id+"]").is(':checked')){
                                $("[data-bar_id="+val.bar_id+"]").prop('checked',false);
                            }else{
                                $("[data-bar_id="+val.bar_id+"]").prop('checked',true); 
                            }
                            var status = (val.status == 1) ? 0: 1;
                            $("[data-bar_id="+val.bar_id+"]").attr('data-status',status);
                        });
                    }
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection
