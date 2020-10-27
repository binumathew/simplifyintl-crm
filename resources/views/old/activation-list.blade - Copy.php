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
								<span class="label">Card :</span> {{$sm_request->autoPlan->card_type}}
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
							@if($sim_list)
							<table class="display responsive no-wrap deliveryTbl" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th class="tbl_tick_bx displayNone">#</th>
										<th>Phone</th>
										<th>Sim Number</th>	
										<th>Plan</th>
										<th>Order Date</th>											
										<th>Status</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($sim_list as $sim)
									@php
									$simStock[] = $sim->stock_id;
									@endphp
									<tr id="request_{{$sim->id}}">
										<td class="displayNone">
											@if($sim->reg_status == 0)
											<input type="checkbox" name="list_id[]" value="{{$sim->id}}" checked class="selected_sim">
											@endif
										</td>	
										<td>{{$sim->stock->phone_number}}</td>
										<td>{{$sim->stock->sim_number}}</td>
										<td>{{$sim->sim_request->autoPlan->plan->plan_name}}</td>
										<td>{{ Carbon::parse($sim->created_at)->format('d M Y') }}</td>
										<td>
											<div class="sm_status_{{$sim->id}}">
											@php
											switch($sim->reg_status){
											case 0:
											echo 'Inactive';
											$active_flag = true;
											break;
											case '1':
											echo 'Active-Callback pending';
											break;
											case '2':
											echo 'Active';
											break;
										}
										@endphp
										</div>
									</td>
									<td>
										@if($sim->reg_status == 1)
										<a title="Add Credit"  href="#" class="fa fa-money" data-toggle="modal" data-target="#addCreditModal"></a>
										<a title="Mark as Welcome Call Completed"  href="#" class="fa fa-check-square mark_wel_call" data-id="{{Crypt::encrypt($sim->id) }}" data-value="{{$sim->id}}" id="ad_crdt_{{$sim->id}}"></a>
										@elseif($sim->reg_status == 2)
										<a title="Add Credit"  href="#" class="fa fa-money" data-toggle="modal" data-target="#addCreditModal"></a>
										@endif
									</td>
								</tr>										
								@endforeach
							</tbody>
						</table>
						@if($active_flag)
						<button type="button" id="activate_sim" class="btn btn-warning pull-right">PROCEED</button>
						@endif
						@php

						@endphp
						<input type="hidden" id="act_stock_id" name="act_request_id" value="{{ serialize($simStock) }}">
						@endif
					</div>
					<input type="hidden" id="act_request_id" name="act_request_id" value="{{ json_encode($request_id)}}">

				</div>
				<div id="reg_wizard"></div>
			</div>
		</div>
	</div>
</div>
</div>

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
					<input type="text" id="auto_amount" class="form-control" name="amount">
				</div>	
				<div id="auto_recharge_error" class="form-error"></div>									
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-success" id="enableAutoRecharge">Enable</button>
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

<script type="text/javascript">
	$(document).ready(function(){
		$('.deliveryTbl').DataTable({responsive: true,"bSort" : false, "searching": false, "lengthChange": false});
		// $('.dataTables_filter input').attr("placeholder", "Search");

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);

		$(document).on('click','#activate_sim1', function () {
			var selected = [];
			$(".selected_sim:checked").each(function() {
				selected.push($(this).val());
			});			
			swal({
				title: "Confirm Activation",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				cancelButtonText: 'Cancel',
				confirmButtonText: 'Activate',
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function (isConfirm) {
				if (isConfirm) {
					$.ajax({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						type: 'POST',                                                
						url: 'activation-process',
						data: {selected:selected},
						success:function(data){	
							swal.close();
							if (data.error) {	
								$.each(data.error, function( index, value ) {
									$('.alert-status').append('<div class="alert alert-danger"> * ' + value + '</div>');	
								});							
							}else{
								$('.alert-status').append('<div class="alert alert-success" id="success1">' + data.message + '</div>');	
							}
							setTimeout(function() {
								$('.alert-status').html('');
							}, 10000);
						}
					});
				} else {
					swal.close();
				}
			});
		});

		$(document).on('click','.mark_wel_call',function () {
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
		
		$(document).on('click','#activate_sim',function () {
			var req_id = $('#act_request_id').val();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'show-activation-list',
				data : {req_id:req_id},
				success:function(data){	
					
					if (data.error) {	
						alert();						
					}else{
						$('#act_btl').hide();
						$('#reg_wizard').html(data.html);	
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
							if ((stepNumber == 1) || (stepNumber == 2)) {
								var value = $('#wizard-error_'+stepNumber).val();
								if (value == 1) {
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
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'show-activation-list',
				data : {req_id:req_id},
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
							if ((stepNumber == 1) || (stepNumber == 2)) {
								var value = $('#wizard-error_'+stepNumber).val();
								if (value == 1) {
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
				$('#auto_amount').val('');	
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
	});
</script>
@endsection

