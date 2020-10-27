@extends('layouts.home')

@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">
					<h1>Create {{ ($staff_type==1)?'Staff':'Dealer' }}</h1>
				</div>
				<form action="{{ url('staff') }}" method="post" id="form">
					{{csrf_field()}}
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>First Name</i>
									<input type="text" class="form-control" name="first_name">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Last Name</i>
									<input type="text" name="last_name">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Email</i>
									<input type="text" name="email">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Phone</i>
									<input type="text" name="phone">
								</div>
							</div>
							<div class="col-md-4 col-sm-4">
								<div class="popTbox">
									<i>Promocode</i>
									<input type="text" name="promocode" id="promocode" maxlength="15" autocomplete="off">
									<span id="promo-msg" class="" for="promocode"></span>
								</div>
							</div>
							<div class="col-md-2 col-sm-2">
								<div class="popTbox">
									<i>Check available</i>
									<input type="button" class="btn btn-info" id="check_promocode" value="Check">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Designation</i>
									<select name="role" id="role">
										<option value="">Choose</option>
										@foreach($roles as $role)
											@if(Auth::user()->role <= $role->id)
												<option value="{{ $role->id }}">{{ $role->name }}</option>
											@endif
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Parent</i>
									<select name="parent" id="parent">
										<option value="">Choose</option>
									</select>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Status</i> 
									<select name="status">
										<option value="1">Active</option>
										<option value="0">InActive</option>
									</select>
								</div>
							</div>
							@php 
							$options = json_decode(Auth::user()->payment_mode, true);
							@endphp
							@foreach($options as $option)
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>{{ ucfirst($option) }} Payment</i>
									<label class="switch">
										<input type="checkbox" name="payment_mode[]" value="{{$option}}">
										<span class="slider round"></span>
									</label>
								</div>
							</div>
							@endforeach	
						</div>
						@if ($errors->any())
						<div class="alert alert-danger">
							<ul>
								@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
						@endif
						
						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<a class="cancel_btn dull" href="{{ url('staff') }}">CANCEL</a>
									<input name="" type="submit" class="greenbtn" value="SUBMIT">
								</div>
							</div>
						</div>									
					</div>
				</form>
			</div>
			<div class="col-md-4">
			</div>
		</div>
	</div>
</div>

<script>

	$(document).ready(function () {

		$('#form').validate({
			errorClass: "form-error",
			rules: {
				first_name: 'required',
				email: {
					required: true,
					email: true
				},
				last_name: 'required',
			},
		}); 
		$(document).on("click", '#check_promocode', function(){ 
			var promocode = $('#promocode').val();
			var defaultmsg = "Enter promocode less than 15 char";
			if(promocode.length <= 15 && promocode != ""){
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {promocode:promocode},
				url: base_url+'/check-promocode',	
				dataType: 'json',
				success:function(data){
					if(data.avail){  
						$("#promo-msg").html(data.msg).removeClass('form-error').addClass('form-success');                   		
					}else{
						$("#promo-msg").html(data.msg).removeClass('form-success').addClass('form-error');
					}
				}				
			});
			}else{
				$("#promo-msg").html(defaultmsg).removeClass('form-success').addClass('form-error');
			}
		});     

		$(document).on('change', '#role', function(){
			var type = $('select[name=role]').val();
			$('#loadingsign').show();
			if(type != ""){
			$('#parent').find('option').not(':first').remove();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data:{role:type},
				url: base_url+'/admin-get-parent',
				success: function(response){ 
					var data = JSON.parse(response);
					console.log(data);
					$.each(data,function(k,val){
						var option = "<option value='"+val.id+"'>"+val.first_name+' '+val.last_name+"</option>";
						$("#parent").append(option); 
					});
					$('#loadingsign').hide();
				}
			});
			}
		});

	});

</script>

@endsection