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
							<th>Phone</th>
							<th>Plan Name</th>	
							<th>Next Renewal</th>	
							<th>Credit</th>									
						</tr>
					</thead>
					<tbody>
						@foreach ($sim_list as $sim)
						<tr id="request_{{$sim->id}}">	
							<td>{{$sim->stock->phone_number}}</td>
							<td>{{$sim->auto_plan->plan->plan_name}}</td>
							<td>{{ !is_null($sim->auto_plan->next_renewal) ? $sim->auto_plan->next_renewal : Carbon::now()->addDays(30)->format('Y-m-d') }}</td>
							<td>@if($sim->credit) {{$currency}}{{$sim->credit}} @else - @endif</td>
						</tr>										
						@endforeach
					</tbody>
				</table>
				@endif
			</div>
		</div>
		<div id="step-2" class="">
			
		</div>
		<div id="step-3" class="">
			
		</div>
		<div id="step-4" class="">
			
		</div>
	</div>
</div>

<input type="hidden" id="wizard-error_0" name="wizard-error" value="0">