<div class="wizard_content">
	<table class="display responsive no-wrap dataTable" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th>Phone</th>
				<th>Swtich ID</th>
				<th>Status</th>	
				<th>Auto Recharge</th>
				<!-- <th>Porting</th> -->
			</tr>
		</thead>
		<tbody>
			@php $error_flag = 0; @endphp
			@foreach($simList as $sim)
			@php 
			$simDetail = $sim->getSimDetails();
			@endphp
			<tr>				
				<td>{{$sim->stock->phone_number}}</td>
				<td>{{$status[$sim->stock_id]}}</td>
				<td>@if($status[$sim->stock_id]) 						
						Done
					@else 
						@php $error_flag = 1; @endphp
						<a class="btn btn-warning btn-xs activation_reload" data-active-page="3" data-id="{{ $simDetail['idetifier'] }}"> Try Again</a>
					@endif
				</td>
				<td>
					@php 
						$checked = '';
						if(isset($sim->auto_recharge->status) &&  $sim->auto_recharge->status == 1){
							$checked = 'checked';
						}
					@endphp
					<label class="switch">
					  <input type="checkbox" id="ar_chck_{{$sim->stock_id}}" {{$checked}} class="enable_auto_recharge" title="Enable Auto Recharge" data-stock_id="{{$sim->stock_id}}">
					  <span class="slider round"></span>
					</label>				
				</td>
				<?php /*	
				<td>
					@php 
						$checked = '';
						if($sim->port == 1){
							$checked = 'checked';
						}
					@endphp 
					
					<label class="switch">
					  <input type="checkbox" id="port_chck_{{$sim->id}}" {{$checked}} title="Manage Porting" class="change_port_status" data-id="{{$sim->id}}" data-stock_id="{{$sim->stock_id}}">
					  <span class="slider round"></span>
					</label>
					@if($sim->port)
					<a data-toggle="tooltip" title="Manage Porting" class="fa fa-pencil-square-o editbtn edit_port_details" data-id="{{$sim->id}}" data-stock_id="{{$sim->stock_id}}"></a>
					@endif			
				</td>
				*/?>		
			</tr>			
			@endforeach
		</tbody>
	</table>
</div>

@if($error_flag == 0)
<form method="post" action="{{url('/sim-activate') }}" id="success-form">
	@csrf
	<input type="hidden" name="order_id" value="{{ $simList[0]->sim_request->order_id }}">
	<button type="button" class="btn btn-success pull-right complete_process" title="View Details">Finish</button>	
</form>
@endif
