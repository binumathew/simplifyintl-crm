@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">			
			<div class="Cleftpart">
				<h1>Update Porting Request</h1>
			</div>
			<form action="{{ url('update-porting') }}" method="post" id="port-form">					
				@csrf	
				<input type="hidden" name="id" value="{{ $port->id }}">
				<div class="Paymentsbg ctablebg clearfix">				
					<div id="reg_wizard">
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
													<td>
													<input type="text" id="porting_to" name="porting_to" value="{{ $port->porting_to }}" required>
													<div class="form-error port_to_error"></div>
													</td>	
													<td>
													<input type="text" id="pac_number" name="pac_number" value="{{ $port->pac_number }}" required>
													<div class="form-error pac_error"></div>
													</td>	
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
													<td>
													<input type="text" id="reference_id" name="reference_id" value="{{ $port->reference_id }}">
													<div class="form-error refer_error"></div>
													</td>	
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
													<td>
													<input type="text" id="exp_port"  name="expected_date" value="{{ $port->expected_date }}"><div class="form-error exp_port_error">
													</td>
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
									<button type="button" class="btn btn-success pull-right complete_port_process" title="Finish and Update User">Update User</button>			
								@endif -->
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="addbtnsbg">
								<a class="cancel_btn" href="{{ url('porting') }}">CANCEL</a>
								@if($port->status == 3)
								<a class="greenbtn complete_port_process" data-req_id="{{$port->id}}">UPDATE</a>
								@endif
							</div>
						</div>
					</div>									
				</div>
			</form>
		</div>
	</div>
</div> 

<div id="portConfirmPopup" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title">Complete Porting Process</h3>
			</div>
			<div class="modal-body">
				
				<div class="form-group">
					<input type="hidden" id="req_id" name="req_id" value="{{$port->id}}">
					<p>Are you sure you want to complete this process?</p>					
				</div>

			</div>
			<div class="modal-footer">
				<div class="form-error port_error"></div>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="confirm_porting_process btn btn-success">Complete</button>
			</div>
		</div>

	</div>
</div>

<script type="text/javascript">	
	$(function () {        
        var selected = '<?php echo $port->status; ?>';
        $('#smartwizard').smartWizard({
			selected: <?php echo $port->status; ?>,
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

        $.validator.addMethod(
		        "regex",
		        function(value, element, regexp) {
		            var re = new RegExp(regexp);
		            return this.optional(element) || re.test(value);
		        },
		        "Please check your input."
			);

		$("#smartwizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) {
			var error_flag = 0;
			var porting_to = $('#porting_to').val();
			var pac_number = $('#pac_number').val();
			if(!porting_to.match(/^(((44))[0-9]{10})$/)) {
			    $('.port_to_error').html('Invlaid phone number!. <br/>Number should start with 44.');
			    error_flag = 1;			    
			}else{
				$('.port_to_error').html('');
			}

			if(pac_number == '') {
			    $('.pac_error').html('Enter PAC Number!.');
			    error_flag = 1;	
			} else {
				$('.pac_error').html('');
			}

			if(stepNumber == 1) {	
				var reference_id = $('#reference_id').val();	
				console.log(reference_id);			
				if(reference_id == '') {
				    $('.refer_error').html('Enter Reference Number!.');
				    error_flag = 1;	
				} else {
					$('.refer_error').html('');
				}
			}

			// if(stepNumber == '2') {
			// 	var exp_port = $('#exp_port').val();			
			// 	if(exp_port == '' || exp_port == undefined) {
			// 	    $('.exp_port_error').html('Enter Expected Porting Date!.');
			// 	    error_flag = 1;	
			// 	} else {
			// 		$('.exp_port_error').html('');
			// 	}
			// }
			console.log(reference_id);			
			// console.log(exp_port);
			console.log(stepNumber);
			console.log(error_flag);
			if(error_flag == 1){
				$('.sw-btn-next').removeClass('disabled');				
				return false;
			}
			// return false;
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
						labelFinish:'Finish',
					});
				}
			});
        });  //data-content-url="{{ url('/update-porting') }}"

        $(document).on('change', '#provider', function(){
        	if($(this).val() == 'other'){
        		$('#other_provider').removeClass('hidden');
        		$('#other_provider').focus();
        	}else{
        		$('#other_provider').addClass('hidden');
        	}
        })

        $(document).on('click', '.complete_port_process', function(){
        	$('#req_id').val($(this).data('req_id'));
        	$('.port_error').text('');
        	$('#portConfirmPopup').modal('show');
        });
        
        $(document).on('click', '.confirm_porting_process', function(){
        	var port_id = $('#req_id').val();
        	$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: '{{ url("/port-finish") }}',
				data: {port_id:port_id, status:4},
				dataType: 'json',
				success:function(data){	
					if(data.success){
						window.location.href = '{{ url("/porting") }}';
					}else{
						$('.port_error').text('Process failed, please try again!');
					}
				}
			});
        });
    });   


</script>

@endsection