@if($step == 1)
<div class="row">
	<div class="col-md-9">
		<div class="card m-b-20">
			<div class="card-body">
				<p class="text-muted font-14 m-b-30 notice-board-top"><i class="mdi mdi-fullscreen noti-icon"></i>Please select the connection type for each product</p>
				<table id="datatable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th>Product</th>
							<th>Provider</th>
							<th>Sim Number</th>
							<th>Connection Type</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($sim_list as $sim)
						<tr class="odd">
							<td>{{ $sim->auto_plan->plan->plan_name }}</td>
							<td>{{ $sim->auto_plan->plan->provider }}</td>
							<td>{{ $sim->stock->sim_number }}</td>
							<td>
								<select class="form-control connection_type" data-id="{{$sim->id}}">
									<option value="1" {{ ($sim->port)?'selected':''}}>
									Port/Migration</option>
									<option value="0" {{ (!$sim->port)?'selected':''}}>New</option>
								</select>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
				<div>
	                <a href="javascript:void(0)" class="btn btn-success waves-effect waves-light pull-right" id="provision_request" data-stock_id="{{$stock_id}}"><strong>Continue</strong></a>
	            </div>
			</div>
		</div>
	</div>

	<div class="col-md-3">
		<div class="card m-b-20">
		    <div class="card-body right-nav">
				<ul>
					<li><a href="#"><b>Step 1</b><br />Select voice and data</a></li>
					<li><a href="#" class="selected"><b>Step 2</b><br />Configure voice and data</a></li>
					<li><a href="#"><b>Step 3</b><br />Select bolt-ons</a></li>
					<li><a href="#"><b>Step 4</b><br />Provisioning information</a></li>
					<li><a href="#"><b>Step 5</b><br />Bill limits</a></li>
					<li><a href="#"><b>Step 6</b><br />Summary</a></li>
					<li><a href="#"><b>Step 7</b><br />Add Customer</a></li>
					<li><a href="#"><b>Step 8</b><br />Payment</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
@endif
@if($step == 2)
<link href="{{ asset('plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
<!-- <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->
<!-- <link href='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/ui-lightness/jquery-ui.css' rel='stylesheet'>  -->
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>  -->
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  -->

<div class="row">
@if(in_array('E_SIM', $provider))
	<div class="col-md-9">
		<div class="card m-b-20">
			<div class="card-body">
				<div class="table-responsive b-0 fixed-solution" data-pattern="priority-columns">
					<form id="provision-form">
					<table id="tech-companies-1-clone" class="table  table-striped inner-form">
						<thead>
							<tr>
								<th colspan="2" nowrap="nowrap" width="200">Product</th>
								<th nowrap="nowrap" width="200">Mobile Number <span style="color: #f00">*</span></th>
								<th nowrap="nowrap" width="200">Username</th>
								<th nowrap="nowrap"> Sim Serial Number <span style="color: #f00">*</span></th>
								<th nowrap="nowrap" width="190">subscription Date</th>
								<th nowrap="nowrap" title="If 'yes', the sim will have subscription date set on bundle first used.">Activate On First Use</th>
								<th nowrap="nowrap" width="100">Send SMS</th>
								<th nowrap="nowrap" title="Decide whether to take payment now or at time of the subscription.">Take Payment</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($sim_list as $sim)
							<tr>
								<td colspan="2" style="vertical-align: middle;">
								<input type="hidden" id="port_request" value="{{$sim->port}}">
								<input type="hidden" id="list_id" name="list_id" value="{{$sim->id}}">
								{{ $sim->auto_plan->plan->plan_name }} ({{ $sim->auto_plan->plan->provider }})
								</td>
								<td><input class="form-control" id="port_cli_{{$sim->id}}" name="porting_to" type="text" value="{{($sim->porting_to)?:''}}" maxlength="11"{{(!$sim->port)?'readonly':''}}></td>
								<td><input class="form-control" name="user_name" type="text" value="{{$sim->sim_request->order_id}}"></td>
								<td>
									<div class="input-group">
										<input class="form-control" id="sim_number_{{$sim->id}}" name="sim_serial" type="text" value="{{ $sim->stock->sim_number }}" readonly>
										<div class="input-group-append">
											<span class="input-group-text"><i class="mdi mdi-rotate-3d mdi-18px verify_sim_number" data-sim_id="{{$sim->id}}"></i></span>
										</div>
									</div>
								</td>
								<td>
									<div class="input-group">
										<input type="text" name="activation" class="form-control {{($sim->port)?'':'act_datepicker'}}" placeholder="yyyy-mm-dd" {{($sim->port)?'disabled':''}}>
										<div class="input-group-append">
											<span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
										</div>
									</div>
								</td>
								<td>
									<select class="form-control" name="activate_onfirstuse">
										<option value="yes">Yes</option>
										<option value="no" selected>No</option>
									</select>
								</td>
								<td>
									<select class="form-control" name="send_sms">
										<option value="yes" selected>Yes</option>
										<option value="no">No</option>
									</select>
								</td>
								<td>
									<select class="form-control" name="take_payment" required>
										<option value="yes" selected>Yes</option>
										<option value="no">No</option>
									</select>
								</td>
								<td></td>
							</tr>
							@endforeach
						</tbody>
					</table>
					<br>
					<table id="tech-companies-2-clone" class="table  table-striped inner-form">
						<thead>
							<tr>
								<th nowrap="nowrap" width="200">Bill Limit</th>
								<th nowrap="nowrap" width="200">Warn Limit</th>
								<th nowrap="nowrap" width="200">Lock Limit</th>
							</tr>
						</thead>
						<tbody>
						<tr>
							<td><div class="input-group"><input class="form-control number" id="bill_limit" name="bill_limit" type="text" value="0" maxlength="8" placeholder="Bill Limit" required></div>
							</td>
							<td><div class="input-group"><input class="form-control number" id="warn_limit" name="warn_limit" type="text" value="0" maxlength="8" placeholder="Warn Limit" required></div>
							</td>
							<td><div class="input-group"><input class="form-control number" id="lock_limit" name="lock_limit" type="text" value="0" maxlength="8" placeholder="Lock Limit" required></div>
							</td>
						</tr>
						</tbody>
					</table>
			            <div class="my-3">
			            	<a href="javascript:void(0)" class="btn btn-secondary sim_provisioning m-10 waves-light" data-stock_id="{{$stock_id}}"><strong>Back</strong></a>
			                <button class="btn btn-success waves-effect waves-light pull-right m-10" id="provision_process" data-stock_id="{{$stock_id}}"><strong>Continue</strong></button>
			            </div>
		        	</form>
				</div>
			</div>
		</div>
	</div>
@else
<div class="col-md-9">
		<div class="card m-b-20">
			<div class="card-body">
				<div class="table-responsive b-0 fixed-solution" data-pattern="priority-columns">
					<form id="provision-form">
					<table id="tech-companies-1-clone" class="table  table-striped inner-form">
						<thead>
							<tr>
								<th colspan="2" nowrap="nowrap" width="200">Product</th>
								<th nowrap="nowrap" width="200">Mobile Number <span style="color: #f00">*</span></th>
								<th nowrap="nowrap" width="200">Username</th>
								<th nowrap="nowrap">STAC/PAC<span style="color: #f00">*</span> Code</th>
								<th nowrap="nowrap" width="190">Transfer Date <span style="color: #f00">*</span></th>
								<th nowrap="nowrap" width="100">Bill Limit</th>
								<th nowrap="nowrap"> Sim Serial Number <span style="color: #f00">*</span></th>
								<th nowrap="nowrap" width="190">Activation Date</th>
								<th nowrap="nowrap">Sim Required?</th>
								<th nowrap="nowrap" width="100">WWCap</th>
								<th nowrap="nowrap"></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($sim_list as $sim)
							<tr>
								<td colspan="2" style="vertical-align: middle;">
								<input type="hidden" id="port_request" value="{{$sim->port}}">
								<input type="hidden" id="list_id" name="list_id" value="{{$sim->id}}">
								{{ $sim->auto_plan->plan->plan_name }} ({{ $sim->auto_plan->plan->provider }})
								</td>
								<td><input class="form-control" id="port_cli_{{$sim->id}}" name="porting_to" type="text" value="{{($sim->porting_to)?:''}}" maxlength="11"{{(!$sim->port)?'readonly':''}}></td>
								<td><input class="form-control" name="user_name" type="text" value="{{$sim->sim_request->order_id}}"></td>
								<td>
									<div class="input-group">
										<input class="form-control" id="pac_code_{{$sim->id}}" name="pac_code" type="text" value="{{($sim->pac_no)?:''}}" {{(!$sim->port)?'readonly':''}}>
										<div class="input-group-append">
											<span class="input-group-text"><i class="mdi mdi-rotate-3d mdi-18px verify_pac_code" data-sim_id="{{$sim->id}}"></i></span>
										</div>
									</div>
								</td>
								<td>
									<div class="input-group">
										<input type="text" id="transfer-date" name="transfer" class="form-control {{($sim->port)?'datepicker':''}}" placeholder="yyyy-mm-dd" {{(!$sim->port)?'disabled':''}}>
										<div class="input-group-append">
											<span class="input-group-text">
												<i class="mdi mdi-calendar"></i>
											</span>
										</div>
									</div>
								</td>
								<td>
									<select class="form-control" name="bill_limit">
										<option value="1" selected>1</option>
										<option value="5">5</option>
										<option value="10">10</option>
									</select>
								</td>
								<td>
									<div class="input-group">
										<input class="form-control" id="sim_number_{{$sim->id}}" name="sim_serial" type="text" value="{{ $sim->stock->sim_number }}" readonly>
										<div class="input-group-append">
											<span class="input-group-text"><i class="mdi mdi-rotate-3d mdi-18px verify_sim_number" data-sim_id="{{$sim->id}}"></i></span>
										</div>
									</div>
								</td>
								<td>
									<div class="input-group">
										<input type="text" name="activation" class="form-control {{($sim->port)?'':'datepicker'}}" placeholder="yyyy-mm-dd" {{($sim->port)?'disabled':''}}>
										<div class="input-group-append">
											<span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
										</div>
									</div>
								</td>
								<td>
									<select class="form-control" name="sim_required">
										<option value="1" selected>Yes</option>
										<option value="0">No</option>
									</select>
								</td>
								<td>
									<select class="form-control" name="wwc">
										<!-- <option value=""></option> -->
										<option value="1" selected>Yes</option>
										<option value="0">No</option>
									</select>
								</td>
								<td></td>
							</tr>
							@endforeach
						</tbody>
					</table>

			            <div class="my-3">
			            	<a href="javascript:void(0)" class="btn btn-secondary sim_provisioning m-10 waves-light" data-stock_id="{{$stock_id}}"><strong>Back</strong></a>
			                <button class="btn btn-success waves-effect waves-light pull-right m-10" id="provision_process" data-stock_id="{{$stock_id}}"><strong>Continue</strong></button>
			            </div>
		        	</form>
				</div>
			</div>
		</div>
	</div>
@endif
	<div class="col-md-3">
		<div class="card m-b-20">
		    <div class="card-body right-nav">
				<ul>
					<li><a href="#"><b>Step 1</b><br />Select voice and data</a></li>
					<li><a href="#"><b>Step 2</b><br />Configure voice and data</a></li>
					<li><a href="#"><b>Step 3</b><br />Select bolt-ons</a></li>
					<li><a href="#"  class="selected"><b>Step 4</b><br />Provisioning information</a></li>
					<li><a href="#"><b>Step 5</b><br />Bill limits</a></li>
					<li><a href="#"><b>Step 6</b><br />Summary</a></li>
					<li><a href="#"><b>Step 7</b><br />Add Customer</a></li>
					<li><a href="#"><b>Step 8</b><br />Payment</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function(){
		var today = new Date();
		$('.datepicker').datepicker({
            autoclose: true,
            orientation:'bottom left',
            minDate: today,
            startDate: today,
            format: 'yyyy-mm-dd',
            daysOfWeekDisabled: [0,6],
        });
		$('.act_datepicker').datepicker({
            autoclose: true,
            orientation:'bottom left',
            minDate: today,
            startDate: today,
            format: 'yyyy-mm-dd',
        });

		$.validator.addMethod('regex', function(value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        }, 'Please check your input.');

		$('#provision-form').validate({
			errorClass: 'text-danger',
            highlight: function ( element, errorClass, validClass ) {
                $( element ).addClass('border border-danger').removeClass('border border-success');
            },
            unhighlight: function (element, errorClass, validClass) {
                $( element ).addClass('border border-success').removeClass('border border-danger');
            },
            // errorPlacement: function(error, element) {
            //     element.addClass('border border-danger');
            // },
            rules: {
                porting_to: {
                    number: true,
                    required: {
                        depends: function () { return ($('#port_request').val() == 1)?1:0 }
                    },
                    regex:/^((07)[0-9]{9})$/,
                },
                user_name:'required',
                pac_code: {
                    required: {
                        depends: function () { return ($('#port_request').val() == 1)?1:0 }
                    }
                },
                transfer: {
                    required: {
                        depends: function () { return ($('#port_request').val() == 1)?1:0 }
                    }
                },
                activation: {
                    required: {
                        depends: function () { return ($('#port_request').val() == 1)?0:1 }
                    }
                },
            }
        });
	});
</script>
@endif
@if($step == 3)
<form id="wizard-form-3">
	<table class="table table-striped dt-responsive nowrap table-vertical datatable" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th>Phone</th>
				<th>Account Id</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			@php $error_flag = 0; @endphp
			@foreach($sim_list as $sim)
			@php
			$simDetail = $sim->getSimDetails();
			@endphp
			<tr>
				<td>{{$sim->stock->phone_number}}</td>
				<td>{{$accounts[$sim->stock_id]}}</td>
				<td>@if($accounts[$sim->stock_id]) Done
					@else
					@php $error_flag = 1; @endphp
					<a class="btn btn-warning btn-xs activation_reload" data-active-page="1" data-id="{{ $simDetail['idetifier'] }}">
					 Try Again</a>
					@endif
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<input type="hidden" id="wizard-error_3" name="wizard_error" value="{{ $error_flag }}">
</form>
@endif
@if($step == 4)
<form id="wizard-form-4">
	<table class="table table-striped dt-responsive nowrap table-vertical datatable" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th>Phone</th>
				<th>Plan Name</th>
				<th>Subscription ID</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			@php $error_flag = 0; @endphp
			@foreach($sim_list as $sim)
			@php
			$simDetail = $sim->getSimDetails();
			@endphp
			<tr>
				<td>{{$sim->stock->phone_number}}</td>
				<td>{{$sim->auto_plan->plan->plan_name}}</td>
				<td>{{$status[$sim->stock_id]}}</td>
				<td>@if($status[$sim->stock_id]) Done
					@else
					@php $error_flag = 1; @endphp
					<a class="btn btn-warning btn-xs activation_reload" data-active-page="2" data-id="{{ $simDetail['idetifier'] }}"> Try Again</a>
					@endif
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<input type="hidden" id="wizard-error_4" name="wizard_error" value="{{$error_flag}}">
</form>
@endif
@if($step == 5)
<form id="wizard-form-5">
	Sippy account
	<input type="hidden" id="wizard-error_5" name="wizard_error" value="0">
</form>
@endif
