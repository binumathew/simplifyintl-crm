<div id="smartwizard">
	<ul>
		<li><a href="#step-1">Porting Request<br/></a></li>
		<li><a href="#step-2">Register Request<br/></a></li>
		<li><a href="#step-3">Initiated<br/></a></li>
		<li><a href="#step-4">Processed<br/></a></li>
	</ul>

	<div>
		<div id="step-1">
			<div class="wizard_content">
				<table class="display responsive no-wrap dataTable" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>Temporary No (AVOO)</th>
							<th>No To Keep(Current Provider)</th>	
							<th>PAC Number</th>	
							<th>Provider</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{'0'.ltrim($port->stock->phone_number,'44')}}</td>
							<td><input type="text" id="porting_to" name="porting_to" value="{{ $port->porting_to }}" required></td>	
							<td><input type="text" id="pac_number" name="pac_number" value="{{ $port->pac_number }}" required></td>	
							<td>
								<select name="provider" id="provider">
									<option value="" {{ ($port->provider =='')?'selected':'' }}>Select</option>
									<option value="EE" {{ ($port->provider =='EE')?'selected':'' }}>EE</option>
									<option value="Vodafone" {{($port->provider == 'Vodafone')? 'selected' :''}}>Vodafone</option>
									<option value="Sky" {{ ($port->provider =='Sky')?'selected':'' }} >Sky mobile</option>
									<option value="BT" {{ ($port->provider =='BT')?'selected':'' }}>BT Mobile</option>
									<option value="Virgin" {{ ($port->provider =='Virgin')?'selected' :'' }}>Virgin Mobile</option>
									<option value="Tesco" {{ ($port->provider =='Tesco')?'selected':'' }}>Tesco Mobile</option>
									<option value="Talk Talk" {{ ($port->provider =='Talk Talk') ? 'selected':'' }}>Talk Talk</option>
									<option value="GiffGaff" {{ ($port->provider == 'GiffGaff' )? 'selected':'' }}>GiffGaff</option>
									<option value="O2" {{ ($port->provider =='O2')?'selected':'' }}>O2</option>
									<option value="Three" {{ ($port->provider =='Three')?'selected':'' }}>Three</option>
									<option value="other" {{ ($port->provider =='Three')?'selected':'' }}>Other</option>
								</select>
								<input type="text" class="hidden" id="other_provider" name="other_provider">
							</td>				
						</tr>
					</tbody>
				</table>											
			</div>
		</div>
		<div id="step-2">
			<div class="wizard_content">
				
				<table class="display responsive no-wrap dataTable" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>Temporary No (AVOO)</th>
							<th>No To Keep(Current Provider)</th>	
							<th>PAC Number</th>	
							<th>Provider</th>
							<th>Reference ID</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{'0'.ltrim($port->stock->phone_number,'44')}}</td>
							<td>{{ $port->porting_to }}</td>	
							<td>{{ $port->pac_number }}</td>	
							<td>{{ $port->provider }}</td>
							<td><input type="text" id="reference_id" name="reference_id" value="{{ $port->reference_id }}"></td>	
						</tr>
					</tbody>
				</table>										
			</div>		
		</div>
		<div id="step-3" class="">
			<div class="wizard_content">
				
				<table class="display responsive no-wrap dataTable" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>Temporary No (AVOO)</th>
							<th>No To Keep(Current Provider)</th>	
							<th>PAC Number</th>
							<th>Refernce ID</th>
							<th>Expected Date</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{'0'.ltrim($port->stock->phone_number,'44')}}</td>
							<td>{{ $port->porting_to }}</td>	
							<td>{{ $port->pac_number }}</td>	
							<td>{{ $port->reference_id }}</td>
							<td><input type="text" id="exp_port" name="expected_date" value="{{ $port->expected_date }}"></td>
						</tr>
					</tbody>
				</table>
				
			</div>
		</div>
		<div id="step-4">
			<div class="wizard_content">										
				<table class="display responsive no-wrap dataTable" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>Temporary No (AVOO)</th>
							<th>No To Keep(Current Provider)</th>	
							<th>PAC Number</th>	
							<th>Refernce ID</th>
							<th>Expected Date</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{'0'.ltrim($port->stock->phone_number,'44')}}</td>
							<td>{{ $port->porting_to }}</td>	
							<td>{{ $port->pac_number }}</td>	
							<td>{{ $port->reference_id }}</td>
							<td>{{ $port->expected_date }}</td>	
						</tr>
					</tbody>
				</table>
				
			</div>
		</div>
		<!-- @if($port->status == 3)
		<a class="greenbtn complete_port_process">UPDATE</a>
		@endif -->	
	</div>
</div>

<script type="text/javascript">
	$("#smartwizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) {
		var formData =  $('#port-form').serialize();			
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',                                                
			url: '{{ url("/update-porting") }}',
			data: {data:formData,step:stepNumber},
			dataType: 'json',
			success:function(data){										
				$('#reg_wizard').html(data.html);

				$('#smartwizard').smartWizard({
					selected: stepNumber+1,
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
			}
		});
    }); 
</script>