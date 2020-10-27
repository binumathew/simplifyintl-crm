@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">
					<h1>Edit Credits</h1>
				</div>
				@if ($errors->any())
				    <div class="alert alert-danger">
				        <ul>
				            <li>{{$errors->first()}}</li>
				        </ul>
				    </div>
				@endif
				<form action="{{ url('update-credits', [$credits->id]) }}" method="post" id="form">
					{{csrf_field()}}
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Amount</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="amount" value="{{ $credits->amount}}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Switch</i>
									<select name="switch" id="switch">
										@foreach ($switch_template as $switch)
											<option value="{{$switch->id}}" @php if($credits->switch_id == $switch->id) echo 'selected'; @endphp>{{$switch->currency}}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Is Default Amount?</i>
									<input type="checkbox" value="1" name="is_default_amount" @php echo $credits->default_amount == 1 ? 'checked' : '' @endphp>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>User Status</i>
									<select name="credit_status" id="credit_status">
										<option value="1" @php if($credits->status == 1) echo 'selected'; @endphp>Active</option>
										<option value="0" @php if($credits->status == 0) echo 'selected'; @endphp>Inactive</option>
									</select>
								</div>
							</div>
							
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<a class="cancel_btn" href="{{ url('/credits') }}">CANCEL</a>
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

				amount: 'required',

				switch: 'required',

			},

		});      

	});

	

</script>

@endsection