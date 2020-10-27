@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
   		<div class="container-fluid">
    		<div class="row">
     			<div class="col-md-8">
      				<div class="Cleftpart">
       					<h1>Add Contacts</h1>
         				<!--<span>Home  |  My Avoo  |  Conference</span> -->
			     	</div>
     				<div class="Unavbg">
       					<div class="row">
			        		@if(session()->has('message'))
			        			<div class="alert alert-success" id="success">
			         				{{ session()->get('message') }}
			       				</div>
			       			@endif
			       			@if(session()->has('error'))
			        			<div class="alert alert-danger" id="success">
			         				{{ session()->get('error') }}
			       				</div>
			       			@endif
			     			<div class="col-md-12">
				           		<ul class="Ulinenav clearfix">
				            		<li class="active"><a href="contacts">Contacts</a></li>
				            		<li><a href="importcontactview">Import Contacts</a></li>
				          		</ul>
				        	</div>
				      	</div>
				    </div>
				    <form action="addcontact" method="post" id="form">
					    {{csrf_field()}}
				     	<div class="Paymentsbg ctablebg clearfix">
				      		<div class="row">
				       			<div class="col-md-12">
				        			<div class="addCformbg">
				         				<h2>Add New Contact</h2>
								        <div class="row">
								          	<div class="col-md-6 col-sm-6">
								           		<div class="popTbox">
										            <i>First Name</i>
										            <input type="text"  name="first_name">
								          		</div>
								        	</div>
								        	<div class="col-md-6 col-sm-6">
								         		<div class="popTbox">
									          		<i>Last Name</i>
									          		<input type="text" name="last_name">
								        		</div>
								      		</div>
								    	</div>
									    <div class="row">
									      	<div class="col-md-6 col-sm-6">
												<div class="popTbox">
													<i>Country Code</i>
													<select name="user_country_code">
														<option value="">Select Country Code</option>
														@foreach (Helper::getCountries() as  $country) 
												      		<option  value="{{$country->id}}">{{ $country->c_code }}  {{ $country->country_name }}</option>
													    @endforeach
													</select>										     
												</div>
											</div>
											<div class="col-md-6 col-sm-6">
												<div class="popTbox">
													<i>Phone No</i>
													<input type="number" name="phone_number" id="txtName">
													<a class="btn btn-primary" id="Add">Add</a>
												 	<a class="btn btn-danger" id="Remove">Remove</a>
												</div>
												<div id="textboxDiv"></div>
											</div>
											<div class="col-md-6 col-sm-6">
												<div class="popTbox">
													<i>Email</i>
													<input type="text" name="email"></div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="addbtnsbg">
													<input name="" type="button" class="greenbtn dull" value="CANCEL">
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

   	<script>  
       	$(document).ready(function() {  
         	var count = 1;
         	var txt = $('#txtName');  
         	$("#Add").on("click", function() { 
	           	if(count>2)
				{
				 	alert("Maximum number of contacts exceeded");
				} else if(txt.val() == 0 && txt.val() == '') {
					alert("Please enter your contact number");
				} else {
					$("#textboxDiv").append("<div class='popTbox'><i>Phone No" +count+"</i><input type='number' name='phone_number2[]' required='required' minlength='5' maxlength='10'></div>");  
					$('#form').validate();
					count += 1;
	            }                           
            });  

         	$("#Remove").on("click", function() { 
          		$("#textboxDiv").children().last().remove();
          		count -= 1;
          		$('#form').validate();
    		});  
       	});  
    </script>
@endsection

