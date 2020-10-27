@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="Cleftpart">
					<h1>Activation Process</h1>
					<!--<span>Home  |  My Avoo  | Contacts </span> -->
				</div>
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
					</div>
				</div>  

				<!-- 8944125646610059314  -->

				<div class="Paymentsbg ctablebg clearfix">
					<div class="row">									
						<form action="{{ url('/sim-activate')}}" method="POST" id="search-form">
							@csrf
							<div class="col-md-6">
								<div class="form-group">
									<label for="name13">Sim Number</label>
									<input type="text" class="form-control" name="sim_number" autocomplete="off" placeholder="Enter Customer Sim Number" required value="{{($sim_number)? $sim_number :'' }}">
								</div>
							</div>
							<div class="col-md-2" style="padding-top:25px;">
								<div class="form-group" >
									<input type="submit"  class="btn btn-primary pull-right" name="submit" value="Search" />
								</div>
							</div>
							<div class="col-md-2" style="padding-top:25px;">
								<div class="form-group">
									<a onclick="(function(){ $('input[name=sim_number]').val('') })();" class="btn btn-warning">Reset</a>
								</div>
							</div>

						</form>	

					</div>
					@if($sim_request)
					@php
					$i=0;
					$active_flag = false;
					@endphp
					<div class="detail_holder_box">
						@foreach ($sim_request as $sm_request)
						@if($i==0)
						<div class="row detail_top_hldr">
							<div class="col-md-3 col-sm-6">
								<span class="label">Name :</span> {{$sm_request->user->name}}
							</div>
							<div class="col-md-3 col-sm-6">
								<span class="label">Email :</span> {{$sm_request->user->email}}
							</div>
							<div class="col-md-3 col-sm-6">
								<span class="label">Phone :</span> {{str_replace('+44','0',$sm_request->user->phone)}}
							</div>

							<div class="col-md-3 col-sm-6">
								<span class="label">Date :</span> {{ date('d-M-Y H:i',strtotime($sm_request->created_at))}}
							</div>
							
						</div>
						@endif
						<div class="row detail_sim_address">
							<div class="col-sm-5 detail_adrs_col_hldr">
								<span class="label">Sim List :</span> 
								<span class="detail_adrs">{{$sm_request->getSimList()}}</span>
							</div>
							<div class="col-sm-7 detail_adrs_col_hldr">
								<span class="label">Address :</span>
								<span class="detail_adrs">{{$sm_request->getShippingAddress()}}</span>
							</div>
						</div>
						@php
						$i++;
						@endphp
						@endforeach

					</div>
					@endif

				</div> 					
				<div class="Paymentsbg ctablebg clearfix">
					<div id="act_btl" class="row">
						<div class="col-md-12">
							@if($sim_request)
							<table class="display responsive no-wrap deliveryTbl" cellspacing="0" width="100%">
								<thead>
									<tr>
										
										<th>Plan</th>
										<th>SIM in Pack</th>
										<th>Number</th>											
										<th>Status</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($sim_list as $sim)
									@php 
										$provider = $sim->auto_plan->plan->provider;	
										$simDetail = $sim->getSimDetails();
									@endphp
									<tr>										
										<td>{{ $sim->auto_plan->plan->plan_name.' ('.$provider.')'}}</td>
										<td>{{ $sim->sim_count }}</td>
										<td>{{ $simDetail['phone_number'] }}</td>
										<td>{{ $simDetail['status'] }}</td>
										<td>
											<input type="hidden" id="sim_stock_{{ $simDetail['idetifier'] }}" name="act_sim_stock" value="{{ $simDetail['stockId'] }}">
											@if($simDetail['status'] == 'Not Active')
												<!-- @if('$provider' == 'O2' || '$provider' == 'VUK')
												@endif -->
												<button class="btn btn-warning btn-xs activate_sim" data-id="{{ $simDetail['idetifier'] }}">Activate</button>
											@endif
											<button class="btn btn-primary btn-xs btn_welcm" data-id="{{ $simDetail['idetifier'] }}">Details</button>
											@if($provider == 'O2' || $provider == 'VUK')
											<button class="btn btn-warning btn-xs send_sim_details" data-id="{{ $simDetail['idetifier'] }}">Send</button>	
											@endif

										</td>										
									</tr>										
									@endforeach
								</tbody>
							</table>
							
							@endif
						</div>
					<input type="hidden" id="act_stock_id" name="act_stock_id" value="">
					<input type="hidden" id="act_request_id" name="act_request_id" value="{{ json_encode($request_id)}}">
				</div>
				<div id="reg_wizard"></div>
			</div>
		</div>
	</div>
</div>
</div>

<!-- <div id="customActivationContainer"></div> -->
<!-- <div id="customActivationPopup" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg mt-7p">
		<! -- Modal content- ->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title">Custom Activation Process</h3>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
					<div class="row">
					    <div class="col-sm-6"> 
						  <h2>Order Details</h2>
						  <p>Order ID :  </p>
						  <p>Sim Number : </p>
						  <p>Provider : </p>
						  <p>Plan Name : </p>
						  <p>Purchased On : </p>
					    </div>

					  	<div class="col-sm-6"> 
						  <h2>Mobile Number</h2>						  
						  <p>Mobile No :  </p>
						  <button type="button" class="btn btn-primary">Update</button>
					  	</div>
					</div>
					<div class="row">
					    <div class="col-sm-6"> 
						  <h2>Pro-Rata Billing</h2>
						  <p>Amount : </p>
						  <p>Card Type : </p>						  
					    </div>
					  	<div class="col-sm-6"> 
						  <h2>Auto Recharge</h2>
						  <p>Provider : </p>
						  <p>Mobile No :  </p>
						  <p>Order ID :  </p>
					  	</div>
					</div>
				</div>
				<button type="button" class="btn btn-success" id="custom_activation">Activate</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div> -->

<div id="addCreditModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="addCreditModalTitle">Add credit</h3>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label>Amount</label>
					<input type="text" class="form-control">
				</div>
				<div class="form-group">
					<input type="checkbox" checked>
					<label>Free credit</label>
				</div>
				<button type="button" class="btn btn-success">Add</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
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
				
				<div class="form-group">
					<label>Auto Recharge Amount</label>
					<input type="hidden" id="auto_stock_id" name="auto_stock_id">
					<select id="auto_amount" class="form-control" name="amount">
						@foreach($credits as $credit)
							<option value="{{$credit->amount}}" {{($credit->default_amount)?'selected':''}}>{{$currency}}{{$credit->amount}}</option>
						@endforeach
					</select>
					<!-- <input type="text" id="auto_amount" class="form-control" name="amount"> -->
				</div>	
				<div id="auto_recharge_error" class="form-error"></div>									
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-success" id="enableAutoRecharge">Enable</button>
			</div>
		</div>

	</div>
</div>

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
						<input type="hidden" id="port_list_id" name="list_id">
						<input type="hidden" id="port_stock_id" name="stock_id">	
						<input type="hidden" id="status" name="status" value="0">					
						<input type="text" id="pac" class="form-control" name="pac">
					</div>
					<div class="form-group">
						<label>Number To Keep</label>									
						<input type="text" id="port_to" class="form-control" name="port_to">
					</div>	

					<div id="port_chck_error" class="form-error"></div>									
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-success" id="change_port_status">Submit</button>
				</form>
				
			</div>
		</div>

	</div>
</div>

<div id="welcomConfirmModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="welcomConfirmModalTitle">Confirm Welcome Call</h3>
			</div>
			<div class="modal-body">
				<input type="hidden" id="confirm_cl_value" name="confirm_cl_value">
				<input type="hidden" id="confirm_cl_id" name="confirm_cl_id">
				<div class="popup_btn_holder">
					<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					<button type="button" id="confirm_cl_btn" class="btn btn-success">Yes</button>
				</div>
				<div id="confirm_cl_popError" class="alert alert-danger displayNone"></div>
			</div>
		</div>

	</div>
</div>

<div id="bundleListModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="welcomConfirmModalTitle">Bundle Detail</h3>
			</div>
			<div class="modal-body">
				<div id="bundle_content_holdr"></div>
			</div>
		</div>

	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$('.deliveryTbl').DataTable({responsive: true,"bSort" : false, "searching": false, "lengthChange": false});
		// $('.dataTables_filter input').attr("placeholder", "Search");

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
		}); 

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);

		$(document).on('click','.btn_welcm',function () {
			var identfr = $(this).attr('data-id');
			var stock_id = $('#sim_stock_'+identfr).val();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'bundle-sim-list',
				data: {stock_id:stock_id},
				success:function(data){	
					$('#bundle_content_holdr').html(data);
						$('#bundleListModal').modal('show');
				}
			});
		});

		$(document).on('click','.mark_wel_call',function () {
			$('#bundleListModal').modal('hide');
			$('#confirm_cl_id').val($(this).attr('data-id'));
			$('#confirm_cl_value').val($(this).attr('data-value'));
			$('#welcomConfirmModal').modal('show');
		});

		$(document).on('click','#confirm_cl_btn',function () {
			var sim_id = $('#confirm_cl_id').val();
			var val = $('#confirm_cl_value').val();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'mark-welcome-call',
				data: {sim_id:sim_id},
				success:function(data){	
					
					if (data.error) {	
						setTimeout(function() {
							$('#confirm_cl_popError').text(data.message);
							$('#confirm_cl_popError').show();
						}, 3000);						
					}else{
						$('.sm_status_'+val).text('Active');
						$('#ad_crdt_'+val).hide();
						$('#welcomConfirmModal').modal('hide');		
					}
				}
			});
		});

		$(document).on('click','.send_sim_details',function () {
			alert('test');
			var sim_id = $(this).attr('data-id');
			var stock_id = $('#sim_stock_'+sim_id).val();
			// var req_id = $('#act_request_id').val();
			// $.ajax({
			// 	headers: {
			// 		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			// 	},
			// 	type: 'POST',                                                
			// 	url: 'send-activation-mail',
			// 	data : {stock_id:stock_id, req_id:req_id},
			// 	success:function(data){	
					
			// 	}
			// });
		});

		$(document).on('click','.activate_sim',function () {
			var sim_id = $(this).attr('data-id');
			var stock_id = $('#sim_stock_'+sim_id).val();
			var req_id = $('#act_request_id').val();

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'show-activation-list',
				data : {stock_id:stock_id, req_id:req_id},
				success:function(data){	
					
					if (data.error) {	
						alert();						
					}else{
						$('#act_btl').hide();
						$('#reg_wizard').html(data.html);
						$('#act_stock_id').val(stock_id);	
						$('#smartwizard').smartWizard({
							selected: 0,
							theme: 'arrows',
							transitionEffect:'fade',
							useURLhash: false,
							showStepURLhash: false,
							toolbarSettings: {
								toolbarButtonPosition: 'right',
								showPreviousButton: false,
							},
							anchorSettings: {
								anchorClickable: false, 
							},
						});

						$("#smartwizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) { 
							if (stepNumber <= 2) {
								var value = $('#wizard-error_'+stepNumber).val();
								if (value != 0) {
									swal("Error", "Please complete activation before move to next step.", "error");
									return false;
								}
								
							} 
							return true;
						});
					}
				}
			});
		});

		$(document).on('click','.activation_reload',function () {
			var selected = $(this).data('active-page')
			var req_id = $('#act_request_id').val();
			var sim_id = $(this).attr('data-id');
			var stock_id = $('#sim_stock_'+sim_id).val();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'show-activation-list',
				data : {stock_id:stock_id,req_id:req_id},
				success:function(data){	
					
					if (data.error) {	
						alert();						
					}else{
						$('#act_btl').hide();
						$('#reg_wizard').html(data.html);	
						$('#smartwizard').smartWizard({
							selected: selected,
							theme: 'arrows',
							transitionEffect:'fade',
							useURLhash: false,
							showStepURLhash: false,
							toolbarSettings: {
								toolbarButtonPosition: 'right',
								showPreviousButton: false,
							},
							anchorSettings: {
								anchorClickable: false, 
							},
						});
						$("#smartwizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) { 
							if (stepNumber <= 2) {
								var value = $('#wizard-error_'+stepNumber).val();
								if (value != 0) {
									swal("Error", "Please complete activation before move to next step.", "error");
									return false;
								}
								
							} 
							return true;
						});
					}
				}
			});
		});

		$(document).on('click','.enable_auto_recharge',function () {
			var stock_id = $(this).data('stock_id');	
			if($(this).is(':checked')){
				$('#auto_stock_id').val(stock_id);	
				// $('#auto_amount').val('');	
				$('#autoRechargeModal').modal('show');
				$('#ar_chck_'+ stock_id).prop('checked', false);
			}else{
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',                                                
					url: 'enable-auto-recharge',
					data : {stock_id:stock_id,amount:0,status:0},
					success:function(response){	
						if(response.success){
							$('#ar_chck_'+ stock_id).prop('checked', false);
						}else{
							$('#auto_recharge_error').html(response.error);
						}
					}
				});				
			}					
		});

		$(document).on('click','#enableAutoRecharge',function () {
			var stock_id = $('#auto_stock_id').val();	
			var amount = $('#auto_amount').val();				
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'enable-auto-recharge',
				data : {stock_id:stock_id,amount:amount,status:1},
				success:function(response){	
					console.log(status);
					if(response.success){						
						$('#ar_chck_'+ stock_id).prop('checked', true);
						$('#autoRechargeModal').modal('hide');
					}else{
						$('#auto_recharge_error').html(response.error);
					}
				}
			});
		});	

		$(document).on('click','.edit_port_details',function () {			
			$('#port_stock_id').val($(this).data('stock_id'));	
			$('#port_list_id').val($(this).data('id'));	
			$('#portingModal').modal('show');
		});

		$(document).on('click','.change_port_status',function () {
			var stock_id = $(this).data('stock_id');	
			var list_id = $(this).data('id');
			if($(this).is(':checked')){
				$('#port_stock_id').val(stock_id);	
				$('#port_list_id').val(list_id);	
				$('#pac_number').val('');
				$('#porting_to').val('');
				$('#portingModal').modal('show');
				$('#port_chck_'+ list_id).prop('checked', false);
			}else{
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',                                                
					url: 'change-port-status',
					data : {list_id:list_id, stock_id:stock_id, status:5},
					success:function(response){	
						if(response.success){
							$('#port_chck_'+ list_id).prop('checked', false);
						}else{
							$('#port_chck_error').html(response.error);
						}
					}
				});				
			}					
		});

		$(document).on('click','#change_port_status',function () {	
			if($('#change-port-form').valid()) {
				var list_id = $('#port_list_id').val();
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',
					url: 'change-port-status',
					data : $('#change-port-form').serialize(),
					success:function(response){					
						if(response.success){						
							$('#port_chck_'+ list_id).prop('checked', true);
							$('#portingModal').modal('hide');
						}else{
							$('#port_chck_error').html(response.error);
						}
					}
				});
			}
		});	

		$(document).on('click','.complete_process',function () {
			$('.complete_process').prop('disabled',true);
			$('#loadingsign').show();
			var stock_id = $('#act_stock_id').val();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'process-email',
				data: {stock_id:stock_id},
				success:function(data){	
					$('#success-form').submit();
				}
			});
		});
	});
</script>
@endsection

