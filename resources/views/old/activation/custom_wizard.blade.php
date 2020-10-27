<div id="smartwizard">
	<ul>
		<li><a href="#step-1">Sim List<br /></a></li>
		<li><a href="#step-2" data-content-url="{{ url('/activate') }}">Activate<br /></a></li>
		<li><a href="#step-3" data-content-url="{{ url('/subscribe') }}">Subscription<br /></a></li>
		<li><a href="#step-4" data-content-url="{{ url('/create-sippy-account') }}">Create Sippy Account<br /></a></li>
	</ul>

	<div>
		<div id="step-1" class="">
			<div class="wizard_content">
				@if($sim_list)
				<table class="display responsive no-wrap dataTable" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>Sim Number</th>
							<th>Phone</th>
							<th>Plan Name</th>	
							<th>Next Renewal</th>	
							<th>Credit</th>									
						</tr>
					</thead>
					<tbody>
						@php $balance = 0; $error_count = 0;@endphp
						@foreach ($sim_list as $sim)
						<tr id="request_{{$sim->id}}">	
							<td>{{$sim->stock->sim_number}}</td>
							<td>
								@if($sim->stock->verified == 0)
								@php ++$error_count; @endphp
								<p id="custom_phone_{{$sim->id}}">0759xxxxxxx  <a class="fa fa-pencil-square-o customize_number" data-list_id="{{$sim->id}}"></a></p>
								<span id="custom_phone_edit_{{$sim->id}}" class="hidden"><input type="text" id="phone_number_{{$sim->stock_id}}"><br/><span class="form-error"></span><button type="button" class="btn btn-success update_stock_number" data-stock_id="{{$sim->stock_id}}" data-list_id="{{$sim->id}}">Update</button></span>
								@else									
									{{$sim->stock->phone_number}} 
								@endif
							</td>
							<td>{{$sim->auto_plan->plan->plan_name.' ('.$sim->auto_plan->plan->provider.')'}}</td>
							<td>
								@if(is_null($sim->auto_plan->next_renewal))
									@php 
									++$error_count;
									$sell_price = $sim->auto_plan->plan->sell_price;
									$endofday = date('t'); $cur_day = date('d');
									$remain_days = $endofday - $cur_day + 1;
									$pro_rata_bill = ($sell_price/$endofday) * $remain_days;
									$balance += $pro_rata_bill;
									@endphp
									{{ 'Prorata amount ('.$remain_days.' days) - '. $currency}}{{ number_format($pro_rata_bill,2,'.','') }}
								@else
									{{ $sim->auto_plan->next_renewal }}
								@endif								 
							</td>
							<td>@if($sim->credit) {{$currency}}{{$sim->credit}} @else - @endif</td>
						</tr>										
						@endforeach
						@if(is_null($sim->auto_plan->next_renewal))
						<tr id="request_prorata">
							<td></td>
							<td></td>
							<td></td>
							<td><button type="button" class="btn btn-success pull-right" id="choose_prorarta_bill_pay">Collect {{$currency}}{{number_format($balance,2,'.','')}}</button></td>
							<td><input type="hidden" id="billing_token" value="{{Crypt::encrypt(number_format($balance,2,'.',''))}}"></td>
						</tr>
						@endif
					</tbody>
				</table>
				@endif
			</div>
		</div>
		<div id="step-2" class="">			
		</div>
		<div id="step-3" class="">			
		</div>
		<!-- <div id="step-4" class="">			
		</div> -->
	</div>
	<input type="hidden" id="wizard-error_0" value="{{$error_count}}">
</div>

<div id="creditCardModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title">Pro-Rata Bill Payment</h3>
			</div>
			<div class="modal-body">
				<form id="change-card-form" >
					@if (!$credit_cards->isEmpty())
					<div class="box-div">
						@php $checked = 'checked'; @endphp
						@foreach($credit_cards as $cards)
						<div class="radio">
							<label><input type="radio" name="credit_card" value="{{ Crypt::encrypt($cards->id) }}" {{$checked}} >{{ $cards->card_type }}</label>
						</div>
						@php $checked = ''; @endphp
						@endforeach
					</div>
					@endif								
					<div class="alert alert-danger hidden" id="error-message"></div>
					<div class="alert alert-success hidden" id="success-message"></div>
					<input type="hidden" id="user_id" name="user_id" value="{{$user->id}}">
					<input type="hidden" id="autoplan_id" name="autoplan_id" value="{{$sim->autoplan_id}}">
					<button type="button" class="btn btn-success" id="collect_prorarta_bill">Pay</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$(document).on('click','.customize_number',function () {
			var list_id = $(this).data('list_id');
			$('#custom_phone_'+list_id).addClass('hidden');
			$('#custom_phone_edit_'+list_id).removeClass('hidden');
		});

		$(document).on('click','.update_stock_number',function () {
			var list_id = $(this).data('list_id');
			var stock_id = $(this).data('stock_id');
			var phone = $('#phone_number_'+stock_id).val();

			// if(phone_number.match(/^((44|+44|0)[0-9]{10})$/)){
			if(phone.match(/^(((0|44))[0-9]{10})$/)){ 
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',                                                
					url: 'update-stock-list',
					data: {stock_id:stock_id,phone_number:phone},
					success:function(data){	
						if(data.success){
							$('#custom_phone_'+list_id).html(data.phone_number+' <a class="fa fa-pencil-square-o customize_number" data-list_id="'+list_id+'"></a>');
							$this = $('#wizard-error_0');
							$this.val($this.val() - 1);
							$('#custom_phone_edit_'+list_id).addClass('hidden');
							$('#custom_phone_'+list_id).removeClass('hidden');	
						}else{
							$('#custom_phone_edit_'+list_id+' span').html(data.message);
						}				
					}
				});
			}else{
				$('#custom_phone_edit_'+list_id+' span').html('Invalid number format!');
			}			
		});	

		$(document).on('click','#choose_prorarta_bill_pay',function () {			
			$('#creditCardModal').modal('show');
		});
		
		$(document).on('click','#collect_prorarta_bill',function () {			
			var user_id = $('#user_id').val();
			var billing_token = $('#billing_token').val();
			var credit_card = $("input[name='credit_card']:checked"). val();
			var autoplan_id = $('#autoplan_id').val();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'pro-rata-payment',
				data: {user_id:user_id,billing_token:billing_token,credit_card:credit_card,autoplan_id:autoplan_id},
				success:function(data){	
					if(data.success){
						$this = $('#wizard-error_0');
						$this.val($this.val() - 1);
						$('#request_prorata').addClass('hidden');
						$('#creditCardModal').modal('hide');
					}else{
						$('#error-message').html(data.message);
						$('#error-message').removeClass('hidden');
					}
				}
			});
		});		
	});
</script>
