@extends('layouts.home')

@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="Cleftpart">
					<h1>Add Staff Paycredit Commission</h1>
				</div>
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
				<form action="{{ url('save-staff-paycredit-commission') }}" method="post" id="form">
					{{csrf_field()}}
					<input type="hidden" name="id" value=" {{ !empty($details) ? $details[0]->role_id : '' }}">
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-4 col-sm-4">
								<div class="popTbox">
									<i>Role</i>
									<select name="role" id="role">
										@foreach ($roles as $role)
										@php $sel = ""; @endphp
										@if(isset($details) && !empty($details))
										@php $sel = $details[0]->role_id == $role->id ? 'selected': "";@endphp
										@endif
											<option  {{$sel}} value="{{$role->id}}">{{$role->name}}</option>
										@endforeach
									</select>
									
								</div>
							</div>
						</div>
						@if(!empty($details))
						@php $i = 0; @endphp
						@foreach ($details as $det)
						@if($i == 0)
						<div class="toClone">
						@endif
						<div class="child">
						<div class="row">
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>Sales Target From</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" required name="target_from[]" value="{{ $det->target_from }}">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>Sales Target To</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="target_to[]" value="{{ $det->target_to }}">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>Commission Rate</i>
									<input type="text" class="form-control customdate" name="commission_rate[]" required id="commission_rate"  value="{{ $det->commission }}">
								</div>
							</div>
							@if($i == 0)
							<div class="col-md-1 col-sm-1">
								<div class="popTbox">
									<i>&nbsp</i>
									<button type="button" class="btn btn-primary toadd" id="addButton">+</button>
								</div>
							</div>
							@else
							<div class="col-md-1 col-sm-1">
								<div class="popTbox">
									<i>&nbsp;</i>
									<button type="button" class="btn toremove btn-warning" id="addButton">-</button>
								</div>
							</div>
							@endif
						</div>
						</div>
						@if($i == 0)
						</div>
						@endif
						@php $i++; @endphp
						@endforeach
						@else
						<div class="toClone">
						<div class="child">
						<div class="row">
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>Sales Target From</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" required name="target_from[]" value="">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>Sales Target To</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="target_to[]" value="">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>Commission Rate</i>
									<input type="text" class="form-control customdate" name="commission_rate[]" required id="commission_rate"  value="">
								</div>
							</div>
							<div class="col-md-1 col-sm-1">
								<div class="popTbox">
									<i>&nbsp</i>
									<button type="button" class="btn btn-primary toadd" id="addButton">+</button>
								</div>
							</div>
						</div>
						</div>
						</div>
						@endif
						<div class="forCloned">
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<a class="cancel_btn" href="{{ url('comm-staff-paycreditlist') }}">CANCEL</a>
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
	$(document).ready(function(){
		$(document).on('click', '.toadd', function(){

			var clone = $(".toClone").children().clone();
			clone.find('button').removeClass('btn-primary toadd').addClass('toremove btn-warning').html('-');
			//clone.attr('toClone').removeClass('toClone').addClass('cloned');
			clone.find('input:text').val('');
			$(".forCloned").append(clone);
		});
		$(document).on('click', '.toremove', function(){
			$(this).parents().eq(2).remove();
		});
	});

	$(document).ready(function () {

		$('#form').validate({

			errorClass: "my-error-class",

			rules: {

				role: 'required',

				target_from: 'required',

				commission_rate: "required"

			},

		});  
    

	});

	

</script>

@endsection