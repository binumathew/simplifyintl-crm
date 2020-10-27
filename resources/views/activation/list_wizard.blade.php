@if($step == 1)
<ul class="nav">
   <li><a class="nav-link" href="#step-1">Sim List</a></li>
   <li><a class="nav-link" href="#step-2" data-content-url="{{ url('/terms-and-condition') }}">Terms & Conditions</a></li>
   <li><a class="nav-link" href="#step-3" data-content-url="{{ url('/activate') }}">Activate</a></li>
   <li><a class="nav-link" href="#step-4" data-content-url="{{ url('/subscribe') }}">Subscription</a></li>
   @if(Helper::get_option('enable_switch_support'))
   <li><a class="nav-link" href="#step-5" data-content-url="{{ url('/create-sippy-account') }}">APP Registration</a></li>
   @endif
</ul>

<div class="tab-content">
    <div id="step-1" class="tab-pane {{($step == 1)?'active':''}}">  
    	<form id="wizard-form-1">	
		@if($sim_list)
		<table class="table table-striped dt-responsive nowrap table-vertical datatable" width="100%" cellspacing="0">
			<thead>
				<tr>
					<th>Phone</th>
					<th>Sim Number</th>
					<th>Plan Name</th>	
					<th>Next Renewal</th>	
					<th>Credit</th>									
				</tr>
			</thead>
			<tbody>
				@php $balance = $error_count = $pro_rata = 0; @endphp
				@foreach ($sim_list as $sim)
				<tr id="request_{{$sim->id}}">	
					<td>
						@if($sim->stock->verified == 0)
							@php ++$error_count; @endphp
							<p id="custom_phone_{{$sim->id}}">0759xxxxxxx  <a class="fa fa-pencil-square-o customize_number" data-list_id="{{$sim->id}}"></a></p>
							<div class="row d-none" id="custom_phone_edit_{{$sim->id}}">
								<div class="col-md-12">	                                
	                                <div class="input-group">                                            
	                                    <input id="phone_number_{{$sim->stock_id}}" type="text" class="form-control">
	                                    <div class="input-group-append">
	                                        <button type="button" class="btn btn-success update_stock_number" data-stock_id="{{$sim->stock_id}}" data-list_id="{{$sim->id}}">Update</button>
	                                        <button type="button" class="btn btn-warning reset_stock_number d-none" data-stock_id="{{$sim->stock_id}}" data-list_id="{{$sim->id}}">Reset</button>
	                                    </div>	                                    
	                                </div>
	                                <span class="text-danger"></span>	                               
	                            </div>	                            
							</div>
						@else									
							{{$sim->stock->phone_number}} 
						@endif
					</td>
					<td>{{$sim->stock->sim_number}}</td>
					<td>{{$sim->auto_plan->plan->plan_name}}</td>
					<td class="next_renewal">
						@if(is_null($sim->auto_plan->next_renewal))
							@if($sim->stock->network->service_type == 1)
								{{ Carbon::now()->addDays(30)->format('Y-m-d') }}
							@else
								@php 
									$cur_day = date('d');
									if($cur_day != '01'){
										++$error_count;
										$pro_rata = 1;
									}
								@endphp
							@endif
						@else
							{{ $sim->auto_plan->next_renewal }}
						@endif
					</td>
					<td>@if($sim->credit) {{$currency}}{{$sim->credit}} @else - @endif</td>
				</tr>										
				@endforeach	
				@if($pro_rata)
				<tr id="request_prorata">	
					<td colspan="4"></td>
					<td>
						<button type="button" class="btn btn-success" id="prorarta_bill" data-autoplan="{{ $sim->autoplan_id }}">Collect Pro-rata</button>
					</td>
				@endif		
			</tbody>
		</table>
		@endif
		<input type="hidden" id="wizard-error_1" name="wizard_error" value="{{$error_count}}">
		</form>		
	</div>
	<div id="step-2" class="tab-pane"></div>
	<div id="step-3" class="tab-pane"></div>
	<div id="step-4" class="tab-pane"></div>
	@if(Helper::get_option('enable_switch_support'))
	<div id="step-5" class="tab-pane"></div>
	@endif
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$(document).on('click','.customize_number',function () {
			var list_id = $(this).data('list_id');
			$('#custom_phone_'+list_id).addClass('d-none');
			$('#custom_phone_edit_'+list_id).removeClass('d-none');
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
							$this = $('#wizard-error_1');
							$this.val($this.val() - 1);
							$('#custom_phone_edit_'+list_id).addClass('d-none');
							$('#custom_phone_'+list_id).removeClass('d-none');	
						}else{
							$('.update_stock_number').addClass('d-none');
							$('.reset_stock_number').removeClass('d-none');	
							$('#custom_phone_edit_'+list_id+' span').html(data.message);
						}				
					}
				});
			}else{							
				$('#custom_phone_edit_'+list_id+' span').html('Invalid number format!');
			}			
		});	

		$(document).on('click','.reset_stock_number',function () {
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
					url: 'reset-stock-list',
					data: {stock_id:stock_id,phone_number:phone},
					success:function(data){	
						if(data.success){														
							$('.reset_stock_number').addClass('d-none');
							$('.update_stock_number').removeClass('d-none');	
							$('#custom_phone_edit_'+list_id+' span').html('');
						}else{
							$('#custom_phone_edit_'+list_id+' span').html(data.message);
						}				
					}
				});
			}else{							
				$('#custom_phone_edit_'+list_id+' span').html('Invalid number format!');
			}			
		});

		$(document).on('click','#prorarta_bill',function () {
			var autoplan = $(this).data('autoplan');
			var date_from = $('#pro_date_from').val();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: base_url+'/pro-rata-billing',
				data: {autoplan:autoplan,date_from:date_from},
				success:function(data){						
					$('#orderCustomLabel').text('Pro-rata Payment Process');
                    if(data.error){
                        $('#orderCustombody').html(data.message); 
                    } else {
                       $('#orderCustombody').html(data.html);
                    }              
                    $('#orderCustomModal').modal('show');
				}
			});		
		});
		
		$(document).on('click','#collect_prorarta',function () {			
			var date_from = $('#pro_date_from').val();
			var credit_card = $("input[name='credit_card']:checked"). val();
			var autoplan = $('#autoplan_id').val();
			$(this).prop('disabled', true);
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: base_url+'/pro-rata-payment',
				data: {date_from:date_from,credit_card:credit_card,autoplan:autoplan},
				success:function(data){	
					if(!data.error){
						$this = $('#wizard-error_1');
						$this.val($this.val() - 1);
						$('.next_renewal').html(data.next_renewal);
						$('#request_prorata').addClass('d-none');
						$('#orderCustomModal').modal('hide');
					}else{
						$('#collect_prorarta').prop("disabled", false);
						$('#error-message').html(data.message);
						$('#error-message').removeClass('hidden');
					}
				}
			});
		});		
	});
</script>
@endif
@if($step == 2)
	<form id="wizard-form-2">
		<h4 class="mt-0 header-title">All orders are accepted under your standard terms and conditions</h4>
	    <ul><li>Please Note: Pro-rata billing may apply which could affect allowances during the first billing month following a new connection or amendment. </li>
	    <li>Please refer to your Mobile Specialist or the FAQ page of your pay plans for more information.</li>

		<li>New connections will be provisioned immediately, this is an automated process and cannot be reversed should you change your mind.</li></ul>
	    <div class="p-3" style="padding:1rem 0 1rem !important;">
	        <div class="custom-control custom-checkbox">
	            <input type="checkbox" class="custom-control-input" id="terms-check" name="terms-check">
	            <label class="custom-control-label" for="terms-check">I accept the above exceptions to my standard terms and conditions with {{ config('settings.app_name') }}</label>
	        </div>
	    </div>
	    <input type="hidden" id="wizard-error_2" name="wizard_error" value="1">
	</form>
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