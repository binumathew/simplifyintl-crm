@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">
					<h1>Edit Staff Commission</h1>
				</div>
				@if ($errors->any())
				    <div class="alert alert-danger">
				        <ul>
				            <li>{{$errors->first()}}</li>
				        </ul>
				    </div>
				@endif
				<form action="{{ url('save-staff-commission') }}" method="post" id="form">
					{{csrf_field()}}
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Role</i>
									<select name="role" id="role">
										@foreach ($roles as $role)
											<option value="{{$role->id}}" @php if($commission->role_id == $role->id) echo 'selected'; @endphp>{{$role->name}}</option>
										@endforeach
									</select>
									
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Sales Target From</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="target_from" value="{{$commission->target_from}}">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Sales Target To</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="target_to" value="{{$commission->target_to}}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Commission Rate</i>
									<input type="text" class="form-control customdate" name="commission_rate" id="commission_rate"  value="{{$commission->commission}}">
								</div>
							</div>
							
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<input type="hidden" name="commission_id" value="{{$commission->id}}">
									<a class="cancel_btn" href="{{ url('\staff-commission') }}">CANCEL</a>
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