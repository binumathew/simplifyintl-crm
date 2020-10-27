@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">
					<h1>Update {{ ($user->role == 5)? 'Dealer':'Staff' }}</h1>
				</div>
				<form action="{{ route('staff.update', $id) }}" method="post" id="form">
					@method('PUT')
					@csrf
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>First Name</i>
									<input type="text" class="form-control" name="first_name" value="{{ $user->first_name }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Last Name</i>
									<input type="text" name="last_name" value="{{ $user->last_name }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Email</i>
									<input type="text" name="email" value="{{ $user->email }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Phone</i>
									<input type="text" name="phone" value="{{ $user->phone }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Promocode</i>
									@if($user->promocode == '')
									<input type="text" name="promocode">
									@else
									<input type="text" name="promocode_updated" value="{{ $user->promocode }}" class="input_disabled" disabled>
									@endif
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Designation</i>
									<select name="role" id="role">
										@foreach($roles as $role)
											@if(Auth::user()->role <= $role->id)
												@php $selected = ($user->role == $role->id)?'selected':''; @endphp
												<option value="{{ $role->id }}" {{ $selected }}>{{ $role->name }}</option>
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
										<option value="1" {{ $user->status == 1 ? 'selected' : '' }}>Active</option>
										<option value="0" {{ $user->status == 0 ? 'selected' : '' }}>InActive</option>
									</select>
								</div>
							</div>
							@php 
							$payment_mode = json_decode($user->payment_mode, true);
							$options = json_decode(Auth::user()->payment_mode, true);
							@endphp

							@foreach($options as $option)
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>{{ ucfirst($option) }} Payment</i>
									<label class="switch">
										<input type="checkbox" name="payment_mode[]" {{ in_array($option, $payment_mode)?'checked':''}} value="{{$option}}">
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
								@if(Auth::user()->role == 5)
								<a class="cancel_btn" href="{{ url('dealers') }}">CANCEL</a>
								@else
								<a class="cancel_btn" href="{{ url('staff') }}">CANCEL</a>
								@endif
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

		$(document).on('change', '#role', function(){
			get_parents();
		});

		function get_parents($parent_id = "") {
			var role_id = $('select[name=role]').val();
			$('#loadingsign').show();
			if(role_id != ""){
				$('#parent').find('option').not(':first').remove();
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',
					data:{role:role_id},
					url: base_url+'/admin-get-parent',
					success: function(response){ 
						var data = JSON.parse(response);
						console.log(data);
						$.each(data,function(k,val){
							sel = "";
							if( $parent_id == val.id){
								sel = "selected";
							}
							var option = "<option "+sel+" value='"+val.id+"'>"+val.first_name+' '+val.last_name+"</option>";
							$("#parent").append(option); 
						});
						$('#loadingsign').hide();
					}
				});
			}
		}   

		get_parents(<?php echo $user->parent_id;?>); 

	});

</script>

@endsection