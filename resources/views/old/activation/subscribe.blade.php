<div class="wizard_content">
	<table class="display responsive no-wrap dataTable" cellspacing="0" width="100%">
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
			@foreach($simList as $sim)
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
</div>

@if($error_flag == 1)
	<input type="hidden" id="wizard-error_2" name="wizard-error" value="1">
@else
	<input type="hidden" id="wizard-error_2" name="wizard-error" value="0">
@endif