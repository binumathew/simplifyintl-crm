@extends('layouts.home')
@section('content')

<style type="text/css">
	.box-div {
	    padding: 15px;
	    border: 1px solid #ccc;
	    margin-bottom: 10px;
	}
</style>
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<!-- <div class="Cleftpart">
					<h1>User Details</h1>
					<span>Home  |  My Avoo  | Contacts </span> 
				</div> -->
				<div class="Unavbg">
					<div class="row">
						<div class="alert-status"> 
							@if(session()->has('message'))
							<div class="alert alert-success" id="success">
								{{ session()->get('message') }}
							</div>
							@endif
							@if(Session()->has('error'))
							<div class="alert alert-danger">
								{{ Session()->get('error') }}
							</div>
							@endif
						</div>
						<div class="col-md-12 col-sm-7">
							<ul class="Ulinenav clearfix">
								<li class="active"><a href="">User Details</a></li>
							</ul>
						</div>
						<div class="col-md-2 col-sm-5">
							<ul class="EAbtnbg">
								<li class="expo">
								</li>
								<li></li>
							</ul>
						</div>													
					</div>
				</div>  


				<div class="Paymentsbg ctablebg clearfix">
					<div class="row">									
						<form action="{{ url('/show-user')}}" method="POST" id="search-form">
							@csrf
							<div class="col-md-6">
								<div class="form-group">
									<label>Search</label>
									<input type="text" class="form-control" name="filter" autocomplete="off" placeholder="Enter Phone number / Email" required value="{{($filter)? $filter :'' }}">
								</div>
							</div>
							<div class="col-md-2" style="padding-top:25px;">
								<div class="form-group" >
									<input type="submit"  class="btn btn-primary pull-right" name="searchBtn" value="Search" />
								</div>
							</div>
							<div class="col-md-2" style="padding-top:25px;">
								<div class="form-group">
									<a onclick="(function(){ $('input[name=filter]').val('') })();" class="btn btn-warning">Reset</a>
								</div>
							</div>

						</form>															
					</div>
				</div> 					
				<div class="Paymentsbg ctablebg clearfix">
					@if($user)
					<div class="panel with-nav-tabs">
						<div class="panel-heading">
							<ul class="nav nav-tabs">
								<li class="active"><a href="#basicDetails" data-toggle="tab">General</a></li>
								<li><a href="#autoPlan" data-toggle="tab">Auto Subscription</a></li>
								<li><a href="#autoRecharge" data-toggle="tab">Auto Recharge</a></li>
								<li><a href="#userPlan" data-toggle="tab">Plan History</a></li>
								<li><a href="#callHistory" data-toggle="tab">Call History</a></li>
								<li><a href="#userPayment" data-toggle="tab">Payment History</a></li>
								<li><a href="#simPurchase" data-toggle="tab">Sim Purchased</a></li>
								<li><a href="#childUser" data-toggle="tab">Child/Parent Users</a></li>
							</ul>
						</div>
						<div class="panel-body">
							<div class="tab-content">
								<div class="tab-pane fade in active" id="basicDetails">
									<div id="listUserDetails">
										@if(Helper::has_permission('user_details','edit'))
										<div style="margin-bottom: 10px;">
											<button class="btn btn-info" onclick="editUser()">Edit Basic Details</button>
											@if($provider == 'EE')
											<button class="btn btn-primary" id="inter-plan">International Plan</button>
											@endif
											<button class="btn btn-warning" id="switch-plan">Switch Plans</button>
											<button class="btn btn-danger" id="change-status-btn" onclick="changeUserStatus({{$user->id}})">{{ $user->status == 0 ? 'Enable User':'Disable User' }}</button>
											<button class="btn btn-primary" id="add-credit">Add Credit</button>

											<button class="btn btn-primary" id="custom-debit">Debit</button>
											

											@if($auto_recharge === null)	
											<a class="btn btn-info enable_auto_recharge" title="Create Auto Recharge" data-user_id="{{$user->id}}">Enable AutoRecharge</a>
											@endif
											@if($user->stock_id)
											@if(!$porting || $porting->status == 5)
											<button class="btn btn-secondary" id="port-btn" data-toggle="modal" data-target="#portingModal">Port</button>
											@endif	
											@if($provider == 'EE')							
											<button class="btn btn-warning" id="getSimAccountInfo" onclick="getSimAccountInfo({{$user->id}})">Sim Info</button>	
											@endif										
											@endif
											<a href="{{ url('/plan-purchase/'.Crypt::encrypt($user->id)) }}" class="btn btn-success">Purchase New Sim</a>
											<a href="{{ url('/add-member/'.Crypt::encrypt($user->id)) }}" class="btn btn-success">Add Member</a>
											@php
											$parameter= Crypt::encrypt($user->id);
											@endphp
											<!-- https://avoomobile.com/ -->
											<a data-toggle="tooltip" title="Login"  href="{{config('app.liveurl').'admin-authenticate/'.$parameter}}" target="_blank" class="fa fa-sign-in btn btn-success"></a>

											<a data-toggle="tooltip" title="Download"  href="{{ url('/download-usage',Crypt::encrypt($user->id))}}" target="_blank" class="fa fa-download btn btn-success"></a>
											@if(Auth::id() == 1)
											<!-- <a data-toggle="tooltip" title="Dev Login"  href="{{config('app.liveurl').'dev/admin-authenticate/'.$parameter}}" target="_blank" class="fa fa-sign-in btn btn-success"></a> -->
											@endif
											<a data-toggle="tooltip" title="Resend Credentials"  data-url="{{url('/reset-password')}}" data-user_id="{{ $user->id }}" target="_blank" class="fa fa-paper-plane btn btn-success" id="reset_passowrd"></a>
											@if(Auth::id() == 1)
											<a data-toggle="tooltip" title="Payment Link"  data-url="{{url('/send-payment-link')}}" data-user_id="{{ $user->id }}" target="_blank" class="fa fa-paper-plane btn btn-success" id="send_payment_link"></a>
											@endif
										</div>
										@endif
										@php 
										  $call_settings = json_decode($user->userDetail->call_settings);
										@endphp
										<table id="userBasicDetail" class="basic_table">
											<tr>
												<th>Name</th>
												<td>{{ $user->name }}</td>
												<th>Auth Name</th>
												<td>{{ $user->userDetail->auth_name }}</td>
											</tr>
											<tr>
												<th>Email</th>
												<td>{{ $user->email }}</td>
												<th>I Account</th>
												<td>{{ $user->userDetail->i_account }}</td>
											</tr>
											<tr>
												<th>Phone</th>
												<td>{{ $user->phone }} {{($user->phone!= $user->alt_phone)?'/'.$user->alt_phone:'' }}</td>
												<th>SIM Account ID</th>
												<td>{{ $user->userDetail->sim_account_id }}</td>
											</tr>
											<tr>
												<th>Address</th>
												<td>{{ $user->userDetail->address }}</td>
												<th>SIM Subscription ID</th>
												<td>{{ $user->userDetail->sim_subscription_id }}</td>
											</tr>
											<tr>
												<th>City</th>
												<td>{{ $user->userDetail->city }}</td>
												<th>VM Password</th>
												<td>{{ $user->userDetail->vm_password }}</td>
											</tr>
											<tr>
												<th>Country</th>
												<td>{{ $user->country->country_name }}</td>
												<th>Referral Code</th>
												<td>{{ $user->userDetail->referralcode }}</td>
											</tr>
											<tr>
												<th>Postal Code</th>
												<td>{{ $user->userDetail->postal_code }}</td>
												<th>User Status</th>
												<td id="user-status">@php echo $user->status == 1 ? 'Active' : 'Inactive' @endphp</td>
											</tr>
											<tr>
												<th>Created On</th>
												<td>{{ date('d-m-Y H:i:s',strtotime($user->created_at)) }}</td>
												<th>Email Verified On</th>
												<td>@php echo isset($user->email_verified_at) ? date('d-m-Y H:i:s',strtotime($user->email_verified_at)) : 'Not Verified' @endphp</td>
											</tr>
											<tr>
												<th>Blance</th>
												<td>{{$balance->balance_amount}}</td>
												<th>Int. Minitues</th>
												<td>{{$balance->balance_minutes}}</td>
											</tr>
											<tr>
												<th>Recent OTP</th>
												<td>{{($recent_otp)?$recent_otp->otp:'-'}}</td>
												<th>No of Try</th>
												<td>{{($recent_otp)?$recent_otp->no_try:'-'}}
												@if(Auth::user()->role == 1 || Auth::user()->role == 3)
													<button type="button" id="reset_otp_limit" class="greenbtn">Reset</button>
												@endif
												</td>
											</tr>
											<tr>
												<th>AccessNumber Support</th>
												<td> {{ ($call_settings->accessnumber_support) ? 'Enabled' : 'Disabled' }}</td>
												<th>Callback Support</th>
												<td>{{ ($call_settings->callback_support) ? 'Enabled' : 'Disabled' }}</td>
											</tr>
											<tr>
												<th>WiFi Support</th>
												<td>{{ ($call_settings->wifi_support) ? 'Enabled' : 'Disabled' }}</td>
												<th>Conference Support</th>
												<td>{{ ($call_settings->conference_support) ? 'Enabled' : 'Disabled' }}</td>
											</tr>
											<tr>
												<th>0870 Support</th>
												<td>{{ isset($call_settings->bundle_o) ? 'Enabled' : 'Disabled' }}</td>
												<th></th>
												<td></td>
											</tr>
										</table>
									</div>
									
									<form id="userDetailForm" action="" method="post" style="display: none;">
										{{csrf_field()}}
										<div class="Paymentsbg ctablebg clearfix">
											<div class="row">
												<div class="col-md-6 col-sm-6">
													<div class="popTbox">
														<i>First Name</i>
														<div class="input-group">
															<span class="input-group-addon" id="basic-addon1">
																<em class="fa fa-user-o"></em>
															</span>
															<input type="text" class="form-control" aria-describedby="basic-addon1" name="first_name" value="{{ $user->first_name}}">
														</div>
													</div>
												</div>
												<div class="col-md-6 col-sm-6">
													<div class="popTbox"><i>Last Name</i><input type="text" name="last_name" value="{{ $user->last_name}}"></div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-6 col-sm-6">
													<div class="popTbox">
														<i>Phone No</i>
														<input type="text" name="phone_number" value="{{ $user->phone}}" class="input_disabled" disabled>
													</div>
												</div>

												<div class="col-md-6 col-sm-6">
													<div class="popTbox">
														<i>Email</i>
														<input type="text" name="email" value="{{ $user->email}}">
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-2 col-sm-2">
													<div class="popTbox">
													<i>AccessNumber Support</i>
													<label class="switch">
													<input type="checkbox" name="call_settings[accessnumber_support]" value="1" {{($call_settings->accessnumber_support)?'checked':''}}>
													<span class="slider round"></span>
													</label>
													</div>
												</div>
												<div class="col-md-2 col-sm-2">
													<div class="popTbox">
													<i>Callback Support</i>
													<label class="switch">
													<input type="checkbox" name="call_settings[callback_support]" value="1" {{($call_settings->callback_support)?'checked':''}}>
													<span class="slider round"></span>
													</label>
													</div>
												</div>
												<div class="col-md-2 col-sm-2">
													<div class="popTbox">
													<i>WiFi Support</i>
													<label class="switch">
													<input type="checkbox" name="call_settings[wifi_support]" value="1" {{($call_settings->wifi_support)?'checked':''}}>
													<span class="slider round"></span>
													</label>
													</div>
												</div>
												<div class="col-md-2 col-sm-2">
													<div class="popTbox">
													<i>Conference Support</i>
													<label class="switch">
													<input type="checkbox" name="call_settings[conference_support]" value="1" {{($call_settings->conference_support)?'checked':''}}>
													<span class="slider round"></span>
													</label>
													</div>
												</div>
												<div class="col-md-2 col-sm-2">
													<div class="popTbox">
													<i>0870 Support</i>
													<label class="switch">
													<input type="checkbox" name="call_settings[bundle_o]" value="1" {{ isset($call_settings->bundle_o) && ($call_settings->bundle_o)?'checked':''}}>
													<span class="slider round"></span>
													</label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6 col-sm-6">
													<div class="popTbox">
														<i>Postal Code</i>
														<input type="text" id="user_updt_postal_code" name="postal_code" value="{{ $user->userDetail->postal_code}}">
													</div>
												</div>
												<div class="col-md-3 col-sm-3">
													<div class="popTbox">
														<i>House No</i>
														<input type="text" id="user_updt_house_no" name="house_no" value="">
													</div>
												</div>
												<div class="col-md-3 col-sm-3">
													<div class="popTbox">
														<i>.</i>
														<button type="button" id="user_updt_gt_adrs" class="greenbtn">Get Address</button>
													</div>
												</div>
											</div>
											
											<div class="displayNone" id="usr_updt_adrs_error">
												<div class="invalid form-error" id="usr_updt_adrs_error-msg"></div>
											</div>
											<div class="displayNone row" id="usr_updt_adrs_select_hldr">
												<div class="col-sm-12">
													<div class="popTbox">
														<select id="usr_updt_adrs_select_box"></select>
													</div>
													
												</div>
											</div>
											<div class="row">
												<div class="col-md-6 col-sm-6">
													<div class="popTbox">
														<i>Address</i>
														<input type="text" id="usr_updt_adrs" name="address" value="{{ $user->userDetail->address}}">
													</div>
												</div>

												<div class="col-md-6 col-sm-6">
													<div class="popTbox">
														<i>City</i>
														<input type="text" id="usr_updt_city" name="city" value="{{ $user->userDetail->city}}">
													</div>
												</div>
												
												
											</div>
											<div class="row">
												<div class="col-md-6 col-sm-6">
													<div class="popTbox">
														<i>Country</i>
														<input type="text" name="country" id="usr_updt_country" value="{{ $user->country->country_name}}" class="input_disabled" disabled>
													</div>
												</div>
												<div class="col-md-6 col-sm-6">
													<div class="popTbox">
														<i>User Status</i>
														<select name="user_status" id="user_status">
															<option value="1" @php if($user->status == 1) echo 'selected'; @endphp>Active</option>
															<option value="0" @php if($user->status == 0) echo 'selected'; @endphp>Inactive</option>
														</select>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<div class="addbtnsbg">
														<input type="hidden" name="from_user_detail" value="1">
														<a class="cancel_btn" onclick="editUser()">CANCEL</a>
														<button  type="button" onclick="updateUserDetails({{$user->id}})" class="greenbtn"> SUBMIT</button>
													</div>
												</div>
											</div>									
										</div>
									</form>
								</div>
								@if(Helper::has_permission('user_details','edit'))
								<div class="tab-pane fade" id="autoPlan">
								
									<div class="table-responsive">
										<table id="userDetailPayment" class="display deliveryTbl" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th>#</th>	
													<th>Plan</th>
													<th>Provider</th>
													<th>Phone Number</th>
													<th>Amount</th>
													<th>Next Renewal</th>	
													<th>Card</th>
													<th>Status</th>
													<th>Action</th>
												</tr>
											</thead>
											<tbody>
												@php $i=1; $checked = ''; @endphp
												@if($auto_plan)
												@foreach ($auto_plan as $plan)
												@php $checked = ($plan->status)? 'checked':'' @endphp
												@php $disableclass = ($plan->status_changeon != null)? 'dis_alert':'' @endphp
												<tr class="{{ $disableclass }}">
													<td>{{ $i++ }}</td>
													<td>{{ $plan->plan->plan_name }}</td>
													<td>{{ isset($plan->plan->provider)?$plan->plan->provider:'App' }}</td>
													<td>
														@foreach($plan->sim as $sim)
															{{$sim->stock->phone_number}} <br/>
														@endforeach
													</td>
													<td>{{$user->currency_symbol}}{{ $plan->total_amount }}</td>
													<td>{{($plan->next_renewal)?date('d-M-Y',strtotime($plan->next_renewal)):''}}</td>
													<td id="plan_card_type_{{$plan->id}}">{{ $plan->card_type }}</td>
													<td>
														
														<label class="switch">
														  <input type="checkbox" id="plan_chck_{{$plan->id}}" {{$checked}} class="change_auto_plan_subsciption" title="Change Subscription Status" data-stock_id="{{$plan->id}}" data-amount="{{$plan->total_amount}}" data-status="{{ $plan->status}}" onclick="updateAutoPlanStatus({{$plan->id}},{{$user->id}},'{{Crypt::encrypt($plan->total_amount)}}',this,{{Auth::user()->role}})">
														  <span class="slider round"></span>
														</label>
														
													</td>
													<td>
														<a class="fa fa-credit-card change_auto_plan_card" id="change_auto_plan_card_{{$plan->id}}" title="Change Card" data-ps_id="{{$plan->id}}" data-card_id="{{$plan->card_id}}">
														
														<a class="fa fa-refresh renewsubscription" title="Renew Plan" data-plan_id="{{$plan->id}}">
														
													</a>
													@if($plan->status_changeon != null)
													<p class="fa fa-info-circle" title="Disable on {{ Carbon::parse($plan->status_changeon)->format('d-m-Y') }}">
													</p>
													@endif
													</td>
												</tr>										
												@endforeach
												@endif
											</tbody>
										</table>
									</div>								
								</div>
								<div class="tab-pane fade" id="autoRecharge">
									<div class="table-responsive">
										<table id="userDetailPayment" class="display deliveryTbl" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th>Amount</th>
													<th>Card</th>
													<th>Status</th>
													<th>Action</th>
												</tr>
											</thead>
											<tbody>
												@php $i=1; $checked = ''; @endphp
												@if($auto_recharge)
												
												@php $checked = ($auto_recharge->status)? 'checked':'' @endphp
												<tr>
													<td>{{$user->currency_symbol}}{{ $auto_recharge->total_amount }}</td>
													<td id="recharge_card_type_{{$auto_recharge->id}}">{{ $auto_recharge->card_type }}</td>
													<td>
														<label class="switch">
														  <input type="checkbox" id="ar_chck_{{$auto_recharge->id}}" {{$checked}} class="change_auto_recharge" title="Change Auto Recharge Status" data-stock_id="{{$auto_recharge->id}}" onclick="updateAutoRechargeStatus({{$auto_recharge->id}})">
														  <span class="slider round"></span>
														</label>
													</td>
													<td><a class="fa fa-credit-card change_auto_recharge_card" id="change_auto_recharge_card_{{$auto_recharge->id}}" title="Change Card" data-ar_id="{{$auto_recharge->id}}" data-card_id="{{$auto_recharge->card_id}}"></a>
													</td>
												</tr>
												@endif
											</tbody>
										</table>
									</div>								
								</div>
								@endif
								<div class="tab-pane fade" id="userPlan">
									<div class="table-responsive">
										<table id="userDetailPlan" class="display deliveryTbl" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th>#</th>
													<th>Plan Name</th>	
													<th>Description</th>
													<th>Amount</th>	
													<th>Status</th>	
													<th>Activated On</th>
												</tr>
											</thead>
											<tbody>
												@php
												$i=1;
												@endphp
												@if($user_plan)
												@foreach ($user_plan as $plan)
												<tr>
													<td>{{ $i++ }}</td>
													<td>{{ $plan->plan->plan_name }}</td>
													<td>{{ $plan->plan->plan_name }}</td>
													<td>{{$user->currency_symbol}}{{ $plan->plan->sell_price }}</td>
													<td>{{ $plan->status == 1 ? 'Active' : 'Inactive' }}</td>
													<td>{{ date('d-M-Y H:i:s',strtotime($plan->created_at))}}</td>
												</tr>										
												@endforeach
												@endif
											</tbody>
										</table>
									</div>
								</div>
								@if(Helper::has_permission('user_details','edit'))
								<div class="tab-pane fade" id="callHistory">
									<div class="table-responsive">	
										<div style="margin-bottom: 10px;">
											@if($user->stock_id && $provider == 'EE')
											<a href="#" class="fa fa-refresh btn btn-success" onclick="updateSimHistory({{$user->id}})"> UPDATE</a>
											@endif
											<div class="pull-right">Last two day's call history</div>
										</div>
										<table id="userDetailCalHstry" class="display responsive no-wrap deliveryTbl calhistry_tbl" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th>#</th>
													<th>Called Time</th>	
													<th>Caller Id</th>
													<th>Called Number</th>	
													<th>Duration</th>
													<th>Cost</th>
													<th>Platform</th>
												</tr>
											</thead>
											<tbody>
												@php
												$i=1;
												@endphp
												@if($call_history)
												@foreach ($call_history as $callHistory)
												<tr>
													<td>{{ $i++ }}</td>
													<td>{{ date('d-M-Y H:i:s',strtotime($callHistory->connect_date))}}</td>	
													<td>{{ $callHistory->cli}}</td>	
													<td>{{ $callHistory->cld}}</td>
													<td>{{ gmdate("H:i:s", $callHistory->duration)}}</td>
													<td>{{ $callHistory->cost}}</td>
													<td>{{ ($callHistory->history_from == 1)?'App':'Sim' }}</td>
												</tr>										
												@endforeach
												@endif
											</tbody>
										</table>
									</div>
								</div>
								<div class="tab-pane fade" id="userPayment">
									
									<div class="table-responsive">
										<table id="userDetailPayment" class="display deliveryTbl" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th>#</th>	
													<th>Txn ID</th>
													<th>Description</th>
													<th>Amount</th>
													<th>Payment Method</th>	
													<th>Payment Date</th>
													<th>Status</th>
												</tr>
											</thead>
											<tbody>
												@php
												$i=1;
												@endphp
												@if($user_payments)
												@foreach ($user_payments as $payments)
												<tr>
													<td>{{ $i++ }}</td>
													<td>{{$payments->transaction_id}}</td>
													<td>{{ $payments->description }}</td>
													<td>{{$user->currency_symbol}}{{ $payments->total_amount }}</td>
													<td>{{ $payments->payment_method }}</td>
													<td>{{ date('d-M-Y H:i:s',strtotime($payments->created_at))}}</td>
													<td>
														@switch($payments->status)
														@case(0)
														Failed
														@break

														@case(1)
														Success
														@break

														@case(2)
														Success with error
														@break
														@endswitch
													</td>
												</tr>										
												@endforeach
												@endif
											</tbody>
										</table>
									</div>
								</div>
								@endif
								<div class="tab-pane fade" id="simPurchase">
									<div class="table-responsive">
										<table id="userDetailsimPurchase" class="display deliveryTbl" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th>#</th>	
													<th>Phone Number</th>
													<th>Sim Number</th>
													<th>PromoCode</th>	
													<th>Purchase Date</th>
													<th>Status</th>
												</tr>
											</thead>
											<tbody>
												@php
												$i=1;
												@endphp
												@if($simPurchase)
												@foreach ($simPurchase as $sim)
												<tr>
													<td>{{ $i++ }}</td>
													<td>{{ $sim->stock->phone_number }}</td>
													<td>{{ $sim->stock->sim_number }}</td>
													<td>{{ $sim->sim_request->promocode }}</td>
													<td>{{ date('d-M-Y H:i:s',strtotime($sim->created_at))}}</td>
													<td>
														
														@switch($sim->reg_status)
														@case(0)
														InActive
														@break

														@case(1)
														Active-Callback pending
														@break

														@case(2)
														Active
														@break
														@endswitch
														
													</td>
												</tr>										
												@endforeach
												@endif
											</tbody>
										</table>
									</div>
								</div>
								<div class="tab-pane fade" id="childUser">
									<div class="table-responsive">
										<table id="childUserTbl" class="display deliveryTbl" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th>#</th>
													<th>Name</th>	
													<th>Email</th>
													<th>Phone Number</th>
													<th>Active Plan</th>	
													<th>Plan Expiry</th>
													<th>Action</th>
												</tr>
											</thead>
											<tbody>
												@php
												$i=1;
												@endphp
												@if($child_users)
												@foreach ($child_users as $child)
												@php
												$plan_data = $child->userPlan();
												@endphp
												<tr>
													<td>{{ $i++ }}</td>
													<td>{{ $child->name }}</td>
													<td>{{ $child->email }}</td>
													<td>{{ $child->phone }}</td>
													<td>{{ $plan_data['plan'] }}</td>
													<td>{{ $plan_data['expiry'] }}</td>
													<td><form class="grid_form" method="post" action="{{url('/show-user')}}">@csrf<input type="hidden" name="filter" value="{{ $child->phone}}"><button type="submit" class="anchor_btn" title="View Details"><i class="fa fa-eye"></i></button></form>
													@if(Auth::id() == 1)
														<a href="#" class="split_users" data-user_id="{{ $child->id }}"><i class="fa fa-users" aria-hidden="true"></i></a>
													@endif
													</td>
												</tr>										
												@endforeach
												@endif
												@if($parent_user)
												@foreach ($parent_user as $parent)
												@php
												$plan_data = $parent->userPlan();
												@endphp
												<tr> 
													<td>{{ $i++ }}</td>
													<td>{{ $parent->name }} (P)</td>
													<td>{{ $parent->email }}</td>
													<td>{{ $parent->phone }}</td>
													<td>{{ $plan_data['plan'] }}</td>
													<td>{{ $plan_data['expiry'] }}</td>
													<td><form class="grid_form" method="post" action="{{url('/show-user')}}">@csrf<input type="hidden" name="filter" value="{{ $parent->phone}}"><button type="submit" class="anchor_btn" title="View Details"><i class="fa fa-eye"></i></button></form></td>
												</tr>										
												@endforeach
												@endif
											</tbody>
										</table>
									</div>
								</div>
								
							</div>
						</div>
					</div>
					@endif
					@if($filter_result != '')
					<div class="alert alert-danger">{{ $filter_result }}</div>
					@endif
				</div>
			</div>
		</div>
	</div>
	@if($user)
	<div id="portingModal" class="modal fade avoopopup" role="dialog">
		<div class="modal-dialog mt-7p">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title" id="addCreditModalTitle">Enable Porting Request</h3>
				</div>
				<div class="modal-body">
					<form id="change-port-form" method="POST">
						<div class="form-group">
							<label>PAC Number</label>
							<input type="hidden" id="stock_id" name="stock_id" value="{{$user->stock_id}}">
							<input type="hidden" name="status" value="0">
							<input type="text" id="pac" class="form-control" name="pac">
						</div>
						<div class="form-group">
							<label>Number To Keep</label>									
							<input type="text" id="port_to" class="form-control" name="port_to">
						</div>	
						<div class="form-group">
							<label>Provider</label>									
							<input type="text" id="provider" class="form-control" name="provider">
						</div>	

						<div id="port_chck_error" class="form-error"></div>									
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-success" id="change_port_status">Submit</button>
					</form>
				</div>
			</div>

		</div>
	</div>

	<div id="autoRechargeModal" class="modal fade avoopopup" role="dialog">
		<div class="modal-dialog mt-7p">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title" id="addCreditModalTitle">Enable Auto Recharge</h3>
				</div>
				<div class="modal-body">
					<form id="new-autorecharge-form" method="POST">
						<div class="form-group">
							<label>Auto Recharge Amount</label>
							<input type="hidden" id="ar_user_id" name="ar_user_id">
							<select id="auto_amount" class="form-control" name="amount">
								@foreach($credits as $credit)
									<option value="{{$credit->amount}}" {{($credit->default_amount)?'selected':''}}>{{$credit->currency_symbol}}{{$credit->amount}}</option>
								@endforeach
							</select>
							<!-- <input type="text" id="auto_amount" class="form-control" name="amount"> -->
						</div>	
						<div class="form-group">
							<label>Choose Credit Card</label>
							<select id="auto_card" class="form-control" name="auto_card">
								@foreach($credit_cards as $cards)
									<option value="{{ $cards->id }}" {{($cards->is_default)?'selected':''}}>{{ $cards->card_type }}</option>
								@endforeach
							</select>
							<!-- <input type="text" id="auto_amount" class="form-control" name="amount"> -->
						</div>
						<div id="auto_recharge_error" class="alert alert-danger"></div>	
						<div id="auto_recharge_success" class="alert alert-success"></div>									
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-success" id="enableAutoRecharge">Enable</button>
					</form>
					
				</div>
			</div>

		</div>
	</div>

	<div id="creditCardModal" class="modal fade avoopopup" role="dialog">
		<div class="modal-dialog mt-7p">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title" id="addCreditModalTitle">Choose a credit card</h3>
				</div>
				<div class="modal-body">
					<form id="change-card-form" >
						@if (!$credit_cards->isEmpty())
						<div class="box-div">
							@foreach($credit_cards as $cards)
								<div class="radio">
									<label><input type="radio" name="credit_card" value="{{ $cards->id }}">{{ $cards->card_type }}</label>
								</div>

							@endforeach
						</div>
						
						<div style="font-size: 20px; font-weight:bold; text-align: center">OR</div>
						@endif
						<div class="box-div">
						<div class="radio">
							<label><input id="new-card-radio" type="radio" name="credit_card" value="new" {{ !isset($credit_cards) ? 'checked' : '' }}>Add New Card</label>
						</div>
						</div>
						<div id="new-card-div" class="box-div" style="display: {{ isset($credit_cards) ? 'none' : 'block' }}">
							<div class="form-group">
								<div class="row">
									<div class="col-md-6">
										<label>First Name</label>
										<input type="text" class="form-control" name="first_name" value="{{$user->first_name}}">
									</div>
									<div class="col-md-6">
										<label>Last Name</label>
										<input type="text" class="form-control" name="last_name" value="{{$user->last_name}}">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-md-8">
										<label>Address</label>
										<input type="text" class="form-control" name="address" value="{{$user->userDetail->address}}">
									</div>
									<div class="col-md-4">
										<label>State</label>
										<input type="text" class="form-control" name="state" value="{{$user->userDetail->state}}">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-md-8">
										<label>City</label>
										<input type="text" class="form-control" name="city" value="{{$user->userDetail->city}}">
									</div>
									<div class="col-md-4">
										<label>Zip Code</label>
										<input type="text" class="form-control" name="zip" value="{{$user->userDetail->postal_code}}">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-md-12">
										<label>Card Number</label>
										<input type="text" class="form-control" id="card_number" name="card_number">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-md-6">
										<label>Expiry Date</label>
										<select name="exp_month" class="form-control">
                                            <option value="" disabled selected>Month</option>
                                            <option value="01">01(Jan)</option>
                                            <option value="02">02(Feb)</option>
                                            <option value="03">03(Mar)</option>
                                            <option value="04">04(Apr)</option>
                                            <option value="05">05(May)</option>
                                            <option value="06">06(June)</option>
                                            <option value="07">07(July)</option>
                                            <option value="08">08(Aug)</option>
                                            <option value="09">09(Sept)</option>
                                            <option value="10">10(Oct)</option>
                                            <option value="11">11(Nov)</option>
                                            <option value="12">12(Dec)</option>
                                        </select>
									</div>
									<div class="col-md-6">
										<label></label>
										<select name="exp_year" class="form-control">
                                            <option value="" disabled selected>Year</option>
                                            <option value="2019">2019</option>
                                            <option value="2020">2020</option>
                                            <option value="2021">2021</option>
                                            <option value="2022">2022</option>
                                            <option value="2023">2023</option>
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2027">2027</option>
                                            <option value="2028">2028</option>
                                            <option value="2029">2029</option>
                                            <option value="2030">2030</option>
                                        </select>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-md-6">
										<label>Security Number</label>
										<input type="text" class="form-control" name="cvv">
									</div>
								</div>
							</div>	
							<div class="form-group">
								<div class="row">
									<div class="col-md-12">
										<label><input id="is_refund" type="checkbox" name="is_refund" value="1" style="margin-right: 5px"><b>Refund the amount?</b></label>
										<div>When adding a new card, as part of our verification process, {{$user->currency_symbol}}0.1 will be debited from your credit card and added to your AVOOMobile account. Instead if you want the amount to be refunded, then tick this box</div>
									</div>
								</div> 
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-md-12">
										<label><input type="checkbox" name="is_default" value="1" style="margin-right: 5px"><b>Make this card my default credit card</b></label>
									</div>
								</div>
							</div>
							
						</div>
						<div class="box-div" id="addcreditDiv" style="display: none;">
							<div class="form-group">
								<div class="row">
									<div class="col-md-12">
										<label>Select Top-up Amount</label>
										<select name="topupamount" id="topup" class="form-control">
											<option value="-1">Choose</option>
										@foreach($credits as $credit)
                                        <option value="{{$credit->amount}}" {{ ($credit->default_amount)?'selected':''}}>Top-up for {{$credit->currency_symbol}} {{$credit->amount}}</option>
                                        @endforeach 
										</select>
									</div>
									<div class="col-md-12">
										<label>Choose where to credit</label>
										<select name="credit_to" id="credit_to" class="form-control">
											<option value="-1">Choose</option>
											<option value="1" selected>App</option>  
											@if($provider == 'EE')
                                            <option value="2" >SIM</option>
                                            @endif
										</select>
									</div>
								</div>
							</div>
						</div>

						@if(Auth::user()->role == 1 || Auth::user()->role == 3)
						<div id="directCashDIv" style="display: none;">
							<div style="font-size: 20px; font-weight:bold; text-align: center">OR</div>
							<div class="box-div">
								<div class="radio">
									<label><input id="direct-cash-radio" type="radio" name="credit_card" value="cash">Direct Cash</label>
								</div>
							</div>
							<div class="box-div" id="direct-cash-box" style="display: none;">
								<div class="form-group">
									<div class="row">
										<div class="col-md-12">
											<label>Enter Amount</label>
											<input type="text" name="cash_amount" class="form-control">
										</div>
										<div class="col-md-12">
											<label>Description</label>
											<textarea name="cash_desc" class="form-control"></textarea>
										</div>
										<div class="col-md-12">
											<label>Choose where to credit</label>
											<select name="cash_credit_to" id="cash_credit_to" class="form-control">
												<option value="-1" disabled>Choose</option>
												<option value="1" selected>App</option>
												@if($provider == 'EE')  
	                                            <option value="2" >SIM</option>
	                                            @endif
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
						@endif
						<div class="alert alert-danger" id="error-message">

						</div>
						<div class="alert alert-success" id="success-message">

						</div>

						<input type="hidden" id="modify_id" name="modify_id">
						<input type="hidden" id="modify_card_for" name="modify_card_for" value="plan">
						<input type="hidden" id="card_type" name="card_type">
						<input type="hidden" name="user_id" value="{{$user->id}}">
						<button type="button" class="btn btn-success" id="saveCard">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div id="debitModal" class="modal fade avoopopup" role="dialog">
		<div class="modal-dialog mt-7p">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title" id="DebitModalTitle">Choose a credit card</h3>
				</div>
				<div class="modal-body">
					@if (!$credit_cards->isEmpty())
					<form id="debit-card-form" >						
						<div class="box-div">
							@foreach($credit_cards as $cards)
								<div class="radio">
									<label><input type="radio" name="credit_card" value="{{ Crypt::encrypt($cards->id) }}" {{($cards->is_default)?'checked':''}}>{{ $cards->card_type }}</label>
								</div>
							@endforeach
						</div>						
						
						<div class="box-div">
							<div class="form-group">
								<div class="row">
									<div class="col-md-12">
										<label>Amount</label>
										<input class="form-control" type="text" name="debit_amount" placeholder="Enter Amount">
										<label>Description</label>		
										<input class="form-control" type="text" name="debit_description" placeholder="Description">	
									</div>
								</div>
							</div>
						</div>
						<div class="alert alert-danger" id="debit-error-message"></div>
						<div class="alert alert-success" id="debit-success-message"></div>

						<!-- <input type="hidden" id="modify_id" name="modify_id"> -->
						<!-- <input type="hidden" id="card_type" name="card_type"> -->
						<input type="hidden" name="user_id" value="{{$user->id}}">
						<button type="button" class="btn btn-success" id="debit-amount">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</form>
					@endif
				</div>
			</div>
		</div>
	</div>

	<div id="simUsageModal" class="modal fade avoopopup" role="dialog">
		<div class="modal-dialog mt-7p">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title">Account Information & Usage</h3>
				</div>
				<div class="modal-body">
					<table class="table table-striped table-bordered table-round dataTable no-footer">
						<tbody>							
							<tr>
								<td>Current Status</td>
								<td id="current_status">-</td>
							</tr>
							<tr>
								<td>Local Minutes</td>
								<td id="call_balanace">-</td>
							</tr>
							<tr>
								<td>SMS</td>
								<td id="sms_balance">-</td>
							</tr>
							<tr>
								<td>Data</td>
								<td id="data_balance">-</td>
							</tr>
							<tr>
								<td>Roaming Minutes</td>
								<td id="roaming_call_balance">-</td>
							</tr>
							<tr>
								<td>Roaming SMS</td>
								<td id="roaming_sms_balance">-</td>
							</tr>
							<tr>
								<td>Roaming Data</td>
								<td id="data_roaming_balance">-</td>
							</tr>
							<tr>
								<td>Credit Balance</td>
								<td id="main_credit">-</td>
							</tr>
							<tr>
								<td>Next Renewal</td>
								<td id="next_renewal">-</td>
							</tr>
						</tbody>
					</table>					
				</div>
			</div>

		</div>
	</div>

	<div id="interplanModal" class="modal fade avoopopup" role="dialog">
		<div class="modal-dialog mt-7p">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title">International Plan</h3>
				</div>
				<div class="modal-body">
					<table class="table table-striped table-bordered table-round dataTable no-footer">
						<thead>
							<th>Plan Name</th>
							<th>Price</th>
							<th>Data Limit</th>
							<th>Call Limit</th>
							<th>In.Call Limit</th>
							<th>Action</th>
						</thead>
						<tbody>							
							@foreach($interplan as $inter)
							<tr>
								<td>{{ $inter->plan_name }}</td>
								<td>{{ $inter->sell_price }}</td>
								<td>{{ $inter->data_limit }}</td>
								<td>{{ $inter->call_limit }}</td>
								<td>{{ $inter->in_call_limit }}</td>
								<td><a data-toggle="tooltip" title="Buy"  href="{{ url('/inter-plan',Crypt::encrypt($user->id.'-'.$inter->id)) }}" class="fa">Buy</a></td>
							</tr>
							@endforeach
						</tbody>
					</table>					
				</div>
			</div>

		</div>
	</div>

	<div id="switchPlanModal" class="modal fade avoopopup" role="dialog">
		<div class="modal-dialog mt-7p">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title">Switch Plans</h3>
				</div>
				<div class="modal-body">
					<table class="table table-striped table-bordered table-round dataTable no-footer">
						<thead>
							<th>Plan Name</th>
							<th>Action</th>
						</thead>
						<tbody>							
							@foreach($switch_plans as $splan)
							<tr>
								<td>{{ $splan->plan_name }}</td>								
								<td><a data-toggle="tooltip" title="Activate" class="fa active_switch_plan" data-user_id="{{$user->id}}" data-switch="{{ $splan->id }}">Activate</a></td>
							</tr>
							@endforeach
						</tbody>
					</table>					
				</div>
				<p class="form-success" id="switch_renew_success"></p>
				<p class="form-error" id="switch_renew_error"></p>
			</div>

		</div>
	</div>

	<div id="subscription-modal-area"></div>	
	<script type="text/javascript">
		$(document).on('click','#reset_otp_limit',function () {
			var $this = $(this);
			var user_id = {{ $user->id }};
			var phone = {{ $user->phone }};
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {phone:phone,user_id:user_id},
				url: base_url+'/reset-otp-try',
				success: function(response){ 
					$this.addClass('hidden');					
				}
			});
		});
	</script>
	@endif
</div>

<script type="text/javascript">

	$(document).ready(function(){
		$('.deliveryTbl').DataTable({responsive: true, "lengthChange": false});
		$('.dataTables_filter input').attr("placeholder", "Search");

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);
	});
</script>

<script type="text/javascript">
	$(document).ready(function(){	
		$(document).on('click','#reset_passowrd',function () {
			var url = $(this).data('url');	
			var user_id	= $(this).data('user_id');	
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {user_id:user_id},
				url: url,
				success: function(response){
					$('#reset_passowrd').hide();				
				}			
			});
		});

		$(document).on('click','.active_switch_plan',function () {
			var switch_id = $(this).data('switch');	
			var user_id	= $(this).data('user_id');	
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {user_id:user_id,switch_id:switch_id},
				url: base_url+'/renew-switch-plan',
				success: function(response){
					if(response.status == 1){
						$('#switch_renew_error').html('');
						$('#switch_renew_success').html(response.message);
						setTimeout(function() {
						    $("#switchPlanModal").modal('hide');
					  	}, 5000);										
					}else{
						$('#switch_renew_success').html('');
						$('#switch_renew_error').html(response.message);
					}			
				}			
			});
		});

		$(document).on('click','#send_payment_link',function () {
			var url = $(this).data('url');	
			var user_id	= $(this).data('user_id');	
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {user_id:user_id},
				url: url,
				success: function(response){
					$('#send_payment_link').hide();				
				}			
			});
		});

		$('#card_number').mask('0000-0000-0000-0000-0000');

		$(document).on('click','.enable_auto_recharge',function () {			
			var user_id = $(this).data('user_id');	
			$('#ar_user_id').val(user_id);	
			$('#autoRechargeModal').modal('show');
			$('#auto_recharge_error, #auto_recharge_success').hide();
		});

		$(document).on('click','#change_port_status',function () {
			if($('#change-port-form').valid()) {	
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',
					url: 'user-port',
					data : $('#change-port-form').serialize(),
					success:function(response){					
						if(response.success){		
							$('#port-btn').hide();				
							$('#portingModal').modal('hide');
						}else{
							$('#port_chck_error').html(response.error);
						}
					}
				});
			}
		});

		$(document).on('click','.split_users',function () {
			var user_id = $(this).data('user_id');
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				url: base_url+'/split-user',
				data : {user_id:user_id},
				success:function(response){					
					if(response.status == 1){					
						location.reload();
					}
				}
			});			
		});

		$(document).on('click','.change_auto_plan_card',function () {
			$('#change-card-form').trigger("reset");	
			$('#new-card-div, #addcreditDiv, #directCashDIv').hide();
			$('input[name="credit_card"][value="'+$(this).data('card_id')+'"]').attr("checked","checked")	
			$('#modify_id').val($(this).data('ps_id'));	
			$('#is_refund').attr('disabled', false);
			$('#modify_card_for').val('plan');
			$('#error-message, #success-message').hide();
			$('#creditCardModal').modal('show');
			
		});

		$(document).on('click','.change_auto_recharge_card',function () {
			$('#change-card-form').trigger("reset");	
			$('#new-card-div, #addcreditDiv, #directCashDIv').hide();
			$('input[name="credit_card"][value="'+$(this).data('card_id')+'"]').attr("checked","checked")	
			$('#modify_id').val($(this).data('ar_id'));	
			$('#is_refund').attr('disabled', false);
			$('#modify_card_for').val('recharge');
			$('#error-message, #success-message').hide();
			$('#creditCardModal').modal('show');
			
		});

		$(document).on('click','#add-credit',function () {
			$('#change-card-form').trigger("reset");	
			$('#new-card-div').hide();
			$('#addcreditDiv, #directCashDIv').show();
			$('input[name="credit_card"][value="'+$(this).data('card_id')+'"]').attr("checked","checked")	
			// $('#modify_id').val($(this).data('ar_id'));	
			$('#is_refund').attr('disabled', true);
			$('#modify_card_for').val('credit');
			$('#error-message, #success-message').hide();
			$('#creditCardModal').modal('show');
			
		});

		$(document).on('click','#custom-debit',function () {
			$('#debit-error-message, #debit-success-message').hide();
			$('#debitModal').modal('show');			
		});

		$('input[type=radio][name=credit_card]').change(function() {
			$('#new-card-div, #direct-cash-box').hide();
		    if (this.value == 'new') {
		        $('#new-card-div').show();
		    }
		    else if (this.value == 'cash') {
		        $('#direct-cash-box').show();
		    }
		    else {
		    	$('#new-card-div, #direct-cash-box').hide();
		    }
		});

		$(document).on("click", '#saveCard', function (){
			var pass = 1;
			$('#error-message, #success-message').html('');
			$('#error-message, #success-message').hide();

			if($('input[type=radio][name=credit_card]:checked').length == 0) {
				$('#error-message').text('Please select a card');
				$('#error-message').show();
				pass = 0;
			}
			if($('#modify_card_for').val() == 'credit') {
				console.log($('input[type=radio][name=credit_card]:checked').val());
				if($('input[type=radio][name=credit_card]:checked').val() == 'cash'){					  
					if($('input[name="cash_amount"]').val() == ''){
						pass = 0;
						$('#error-message').text('Enter the amount');
						$('#error-message').show();
					} else if($('textarea[name="cash_desc"]').val() == ''){
						pass = 0;
						$('#error-message').text('Enter the description');
						$('#error-message').show();
					}
				} else {
					if( $("#topup option:selected").val() == '-1'){
						pass = 0;
						$('#error-message').text('Please choose topup');
						$('#error-message').show();
					} else if( $("#credit_to option:selected").val() == '-1'){
						pass = 0;
						$('#error-message').text('Please choose credit');
						$('#error-message').show();
					}
				}				
			}

			if($('input[type=radio][name=credit_card]:checked').val() == "new") {
				if($('#change-card-form').valid()) {
					pass = 1;
				}
				else {
					pass = 0;
				}
			}

			if(pass) {
				$('#loadingsign').show();
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',
					data: $('#change-card-form').serialize(),
					url: '<?php echo url('/'); ?>/change-user-card',
					success: function(response){ 
						$('#loadingsign').hide();
						if(response.status == "success"){
							if($('#modify_card_for').val() == 'plan') {
								$('#plan_card_type_'+$('#modify_id').val()).text(response.data.card_type);
								$('#change_auto_plan_card_'+$('#modify_id').val()).data('card_id', response.data.card_id);
							}
							else if ($('#modify_card_for').val() == 'recharge')  {
								$('#recharge_card_type_'+$('#modify_id').val()).text(response.data.card_type);
								$('#change_auto_recharge_card_'+$('#modify_id').val()).data('card_id', response.data.card_id);
							}
							$('#change-card-form').trigger("reset");	
							$('#success-message').text(response.message);
							$('#success-message').show();
						}
						else{
							$('#error-message').text(response.message);
							$('#error-message').show();
						}
					}
				});
			}
			
		});

		$(document).on("click", '#debit-amount', function (){
			var pass = 1;
			$('#debit-error-message, #debit-success-message').html('');
			$('#debit-error-message, #debit-success-message').hide();

			if($('input[type=radio][name=credit_card]:checked').length == 0) {
				$('#debit-error-message').text('Please select a card');
				$('#debit-error-message').show();
				pass = 0;
			}

			if( $("input[name=debit_amount]").val() == ''){
				pass = 0;
				$('#debit-error-message').text('Enter the amount');
				$('#debit-error-message').show();
			}
								
			if(pass) {
				$('#loadingsign').show();
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',
					data: $('#debit-card-form').serialize(),
					url: base_url+'/custom-debit',
					success: function(response){ 
						$('#loadingsign').hide();
						if(response.status == 1){
							$('#debit-card-form').trigger("reset");	
							$('#debit-success-message').text(response.message);
							$('#debit-success-message').show();
						}else{
							$('#debit-error-message').text(response.message);
							$('#debit-error-message').show();
						}
					}
				});
			}
		});


		$(document).on("click", '#enableAutoRecharge', function (){
			$('#auto_recharge_error, #auto_recharge_success').hide();
			$('#loadingsign').show();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: $('#new-autorecharge-form').serialize(),
				url: '<?php echo url('/'); ?>/add-autorecharge',
				success: function(response){ 
					$('#loadingsign').hide();
					if(response.status == "success"){
						$('#auto_recharge_success').text(response.message);
						$('#auto_recharge_success').show();
					}
					else{
						$('#auto_recharge_error').text(response.message);
						$('#auto_recharge_error').show();
					}
				}
			});
			
		});


	});

	$(document).on('click','.renewsubscription',function () {		
		var plan_id = $(this).data('plan_id');		
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',
			data: {plan_id:plan_id},
			url: base_url+'/subscription-renewal-popup',
			success: function(response){ 
				$('#subscription-modal-area').html(response);
				$('#subscriptionModal').modal('show');
			}
		});				
	});

	$(document).on('click','#subscriptionRenewal', function () {	
		$('#subscriptionRenewal').attr('disabled',true); 			
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',
			data: $('#renewal-form').serialize(),
			url: base_url+'/subscription-renewal',
			success: function(response){				
				if(response.status == 1){					
					location.reload();
				}else{
					$('#subscription_error').html('<div class="alert alert-danger">'+response.message+'</div>');
					$('#subscriptionRenewal').removeAttr('disabled'); 
					setTimeout(function() {
						$('.alert.alert-danger').fadeOut('fast');
					}, 5000);
				}
			}
		});
	});

	$(document).on('change','input[name="renewal_type"]', function () {		
		if($(this).val() == 2){
			$('#mode_select').addClass('hidden');
			$('#transaction_details').addClass('hidden');
		}else{
			$('#mode_select').removeClass('hidden');
			if($('input[name="payment_mode"]:checked').val() == 2){				
				$('#transaction_details').removeClass('hidden');
			}
		}		
	});

	$(document).on('change','input[name="payment_mode"]', function () {
		if($(this).val() == 2){
			$('#transaction_details').removeClass('hidden');
		}else{
			$('#transaction_details').addClass('hidden');
		}
	});

	function getSimAccountInfo(user_id) {
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',
			data: {'user_id' :user_id},
			url: base_url+'/get-sim-info',
			success: function(response){ 
				if(response.status == 'success'){					
					$('#current_status').html(response.network_status);
					$('#call_balanace').html(response.voice_bundle);
					$('#sms_balance').html(response.sms_bundle);
					$('#data_balance').html(response.data_bundle);
					$('#roaming_call_balance').html(response.voice_bundle_roam);
					$('#roaming_sms_balance').html(response.sms_bundle_roam);
					$('#data_roaming_balance').html(response.data_bundle_roam);
					$('#main_credit').html(response.remaining_credit);
					$('#next_renewal').html(response.next_renewal);
				}
				$('#simUsageModal').modal('show');
			}
		});
	}

	function changeUserStatus(user_id) {
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',
			data: {'user_id' :user_id},
			url: '<?php echo url('/'); ?>/change-user-status',
			success: function(response){ 
				if(response.status == 1){
					$('#change-status-btn').text('Disable User');
					$('#user-status').text('Active');
					$('#user_status').val(1);
				}
				else{
					$('#change-status-btn').text('Enable User');
					$('#user-status').text('Inactive');
					$('#user_status').val(0);
				}
			}
		});
	}

	function updateAutoPlanStatus(autoplan_id,userid,amount,elem,admin) {
		var result;
		var thiselem = $(elem);
		var status   = thiselem.attr('data-status');
		if(status == 1){
			var dataamount = thiselem.attr('data-amount');
			var dedamount  = parseFloat((parseFloat(dataamount)*parseFloat(80))/100).toFixed(2);

			var options = [{ text: 'Disable Now ( '+dedamount+' will be deducted )', value: '1' },
				{ text: 'After Contract Period (30 Days)', value: '2' }];

			if(admin == 1){
				options.push({ text: 'Disable sim card without any payment', value: '0' });			    
			}

			bootbox.prompt({
		    title: "Plan Subscription",
		    message: '<p>Please select an option below:</p>',
		    inputType: 'radio',
		    inputOptions: options,

		    callback: function (result) {
		        if(result != null){ 
		        	$('#loadingsign').show(); 
		        	$.ajax({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						type: 'POST',
						data: {'autoplan_id' :autoplan_id,'status_type':result,'user_id':userid,'amount':amount},
						url: '<?php echo url('/'); ?>/update-autoplan-status',
						success: function(response){ 
							$('#loadingsign').hide();
							if(response.status == 'success'){
								thiselem.attr('data-status',0);
							}
							else if(response.status == 'failure'){
								thiselem.prop('checked',true);
							}
							alert(response.message);

						}
					});   
	 			}else{
	 				thiselem.prop('checked',true);
	 			}
		    }
			});
		}else{
			$('#loadingsign').show();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {'autoplan_id' :autoplan_id,'status_type':0,'user_id':userid,'amount':amount},
				url: '<?php echo url('/'); ?>/update-autoplan-status',
				success: function(response){ 
					$('#loadingsign').hide();
					alert(response.message);
					if(response.status == 'success'){
						thiselem.attr('data-status',1);
					}
					else if(response.status == 'failure'){
					  thiselem.prop('checked',false);
					}
				}
			});   
		}
	}

	function updateAutoRechargeStatus(autorecharge_id) {
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',
			data: {'autorecharge_id' :autorecharge_id},
			url: '<?php echo url('/'); ?>/update-autorcharge-status',
			success: function(response){ 
				
			}
		});
	}

	function updateUserDetails(user_id) {
		if($('#userDetailForm').valid()) {
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: $('#userDetailForm').serialize(),
				url: '<?php echo url('/'); ?>/update-user/'+user_id,
				success: function(response){ 
					if(response.status == "success"){
						//editUser();
						document.getElementById("search-form").submit();
					}
					else{
						editUser();
					}
				}
			});
		}
	}

	function editUser() {
		$('#listUserDetails').toggle();
		$('#userDetailForm').toggle();
	}

	function updateSimHistory(user_id) {
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',
			data: {user_id:user_id},
			url: base_url+'/update-sim-history',
			success: function(response){ 
				location.reload();
			}
		});
	}

	$(document).ready(function () {
		$.validator.addMethod(
	        "regex",
	        function(value, element, regexp) {
	            var re = new RegExp(regexp);
	            return this.optional(element) || re.test(value);
	        },
	        "Please check your input."
		);

		$('#userDetailForm').validate({
			errorClass: "my-error-class",
			rules: {
				first_name: 'required',
				email: {
					required: true,
					email: true
				},
				last_name: 'required',
				phone_number: {
					minlength:5,
					maxlength:11,
					required: true
				},
			},
			messages: {
				phone_number: "Enter valid phone number"
			}
		});  

		$('#change-port-form').validate({
			errorClass: "my-error-class",
			rules: {
				stock_id: 'required',
				pac: 'required',
				port_to: {
		                    number: true,
		                    required: true, 
		                    minlength:10,
		                    maxlength:12,
		                    regex:/^(((44))[0-9]{10})$/,
		                },  
				provider: 'required',
			},
			messages: {
				port_to: "Enter valid phone number"
			}
		});      
	});

	$(document).on('click','#user_updt_gt_adrs',function (e) {
		var postal_code = $('#user_updt_postal_code').val();
		var house_no = $('#user_updt_house_no').val();
		if (postal_code == '') {
			$('#usr_updt_adrs_error-msg').text('Please enter postal code');
			$('#usr_updt_adrs_error').show();
			setTimeout(function(){
				$('#usr_updt_adrs_error').hide();
			}, 3000);
		} else {
			$('#loadingsign').show();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: "<?= url('get-address') ?>",
				data: {postal_code:postal_code,house_no:house_no},
				success:function(data){	
					$('#loadingsign').hide();
					if (data.error) {
						if (data.type == 1) {
							$('#usr_updt_adrs_select_hldr').show();
							$('#usr_updt_adrs_select_box').html(data.list);
							$('#usr_updt_adrs_error').show();
							$('#usr_updt_adrs_error-msg').text(data.message);
						} else if (data.type == 1) {
							$('#usr_updt_adrs_select_hldr').hide();
							$('#usr_updt_adrs_error').show();
							$('#usr_updt_adrs_error-msg').text(data.message);
						}
					} else {
						$('#usr_updt_adrs_select_hldr').hide();
						$('#usr_updt_adrs_error').hide();
						
						var value = data.address;
						var result = value.split(' , ');
						if (result.length == 4) {
							$('#usr_updt_adrs').val(result[0]);
							$('#usr_updt_city').val(result[1]);
							//$('#usr_updt_state').val(result[2]);
							$('#usr_updt_country').val(result[2]);
						}
					}
				}
			});
		}
	});

	$(document).on('click','#usr_updt_adrs_select_box',function () {
		var value = $(this).val();
		var result = value.split(' , ');
		if (result.length == 4) {
			$('#usr_updt_adrs').val(result[0]);
			$('#usr_updt_city').val(result[1]);
			//$('#usr_updt_state').val(result[2]);
			$('#usr_updt_country').val(result[2]);
		}
	})

	$('#change-card-form').validate({
	    rules: {
	        first_name:'required',                        
	        last_name:'required',                    
	        address:'required',        
	        state:'required',
	        city:'required',
	        zip:'required',
	        card_number:{
	            required:true,                            
	        },
	        exp_month:'required',
	        exp_year:'required',
	        cvv:'required'
	    },
	    messages: {
	        address: "Enter your street address",
	        state: "Enter your state",
	        city: "Enter your city",
	        postal_code: "Enter your postal code",
	    },
	    submitHandler: function(form) {
	        $('#pay-now').attr('disabled',true);  
	        form.submit();
	    },
	    errorClass: "invalid form-error",
	    errorElement: 'div',
	    errorPlacement: function(error, element) {                       
	        error.appendTo(element.parent());
	    },
	});

	$(document).on( 'change', '#card_number', function(){                  
	    result = $("#card_number").validateCreditCard();
	    if(result.card_type !== null){
	        $('#card_type').val(result.card_type.name);
	    }else{
	        $('#card_type').val('Card');
	    }
	    // if(!result.length_valid){                
	    // }
	});
	$(document).on('click','#inter-plan',function () {
		$("#interplanModal").modal('show');
	});

	$(document).on('click','#switch-plan',function () {
		$("#switchPlanModal").modal('show');
	});
</script>

@endsection

