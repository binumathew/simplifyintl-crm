@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-8">
					<div class="Cleftpart">
						<h1>Edit Contacts</h1>
						<!--<span>Home  |  My Avoo  |  Conference</span> -->
					</div>
					<div class="Unavbg">
						<div class="row">
							@if(session()->has('message'))
							<div class="alert alert-success">
								{{ session()->get('message') }}
							</div>
							@endif
							<div class="col-md-12">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="#">Edit Contacts</a></li>
								</ul>
							</div>
						</div>
					</div>
					<form action="{{ url('updatecontact') }}" method="post" id="form">
						{{csrf_field()}}
						<input type="hidden" name="id" value="{{$user_contacts->id}}"/>
						<input type="hidden" name="user_id" value="{{$user_contacts->user_id}}"/>
						<div class="Paymentsbg ctablebg clearfix">
							<div class="row">
								<div class="col-md-12">
									<div class="addCformbg">
										<h2>Edit Contact</h2>
										<div class="row">
											<div class="col-md-6 col-sm-6">
												<div class="popTbox">
													<i>First Name</i>
													<div class="input-group">
														<span class="input-group-addon" id="basic-addon1">
															<em class="fa fa-user-o"></em>
														</span>
														<input type="text" class="form-control" aria-describedby="basic-addon1" name="first_name" value="{{$user_contacts->first_name}}">
													</div>
												</div>
											</div>
											<div class="col-md-6 col-sm-6">
												<div class="popTbox"><i>Last Name</i><input type="text" name="last_name" value="{{$user_contacts->last_name}}"></div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6 col-sm-6">
												<div class="popTbox">
													<i>Country code</i>
													<select name="user_country_code">
													@php
													$no = explode("-", $user_contacts->phone_number, 2);		 @endphp 
													@foreach($countries as $key => $value)
										            <option value="{{$value->id}}"
														@if($value->c_code == $no[0]) echo "selected"; @endif>
										              {{$value->c_code}}  {{$value->country_name}}</option>
													@endforeach 
													</select>
												</div>
											</div>
											@php $user_contacts->phone_number =$no[1]; @endphp		
											<div class="col-md-6 col-sm-6">
												<div class="popTbox">
													<i>Phone No</i>
													<input type="number" name="phone_number" value="{{$user_contacts->phone_number}}">
												</div>
											</div>
											<div class="col-md-6 col-sm-6">
												<div class="popTbox">
													<i>Email</i>
													<input type="text" name="email" value="{{$user_contacts->email}}">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="enabletxt">
													<div class="enablecheck">
														<input type="checkbox" value="None" id="E1" name="check" checked />
														<label for="E1"></label>
													</div>
													ACTIVE
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="addbtnsbg">
													<a class="greenbtn dull" href="{{ url('contacts') }}">CANCEL</a>
													<input name="" type="submit" class="greenbtn" value="SUBMIT">
												</div>
											</div>
										</div>									
									</div>
								</div>
							</div>
						</div>
					</form>
                </div>
                <div class="col-md-4">
                    <div class="Advt">
                    	<img src="{{ asset('public/images/advt1.jpg') }}" class="img-responsive center-block">
                    </div>
                    <div class="Advt">
                    	<img src="{{ asset('public/images/advt2.jpg') }}" class="img-responsive center-block">
                    </div>
                </div>
            </div>
        </div>
    </div>

	<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
  	<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>

	<script>
   		$(document).ready(function () {
         	$('#form').validate({
				errorClass: "my-error-class",
				rules: {
				 	first_name: 'required',
				 	email: {
					   	required: true,
					   	email: true
					},
				 	last_name: 'required',
				 	user_country_code: 'required',
				 	phone_number: {
					   	minlength:5,
					   	maxlength:11,
					   	required: true
				 	},
				},
	           	messages: {
	             	phone_number: "Enter valid phone number"
	            }
         	});      
       	});
 	</script>
@endsection