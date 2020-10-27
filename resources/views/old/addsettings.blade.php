@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-10">
				<div class="Cleftpart">
					<h1>Settings</h1>
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
				<form action="{{ url('save-settings') }}" method="post" id="settings-form">			
					@csrf
					<input type="hidden" name="id" value=" {{ !empty($details) ? $details->id : '' }}">					
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Name</i>
									<input type="text" name="name" value="{{ !empty($details) ? $details->name : '' }}" required autocomplete="off">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Value</i>
									<input type="text" name="value" value="{{ !empty($details) ? $details->value : '' }}" required autocomplete="off">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Auto Load</i>
									<select class="form-control" name="auto_load" id="auto_load" required>
										<option value="">Choose</option>
										<option {{ (!empty($details) && $details->auto_load ==0)?'selected':'' }} value="0">InActive</option>
										<option {{ (!empty($details) && $details->auto_load ==1)?'selected':'' }} value="1">Active</option>
									</select>
								</div>
							</div>
						</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<a class="cancel_btn" href="{{ url('settings') }}">CANCEL</a>
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
@endsection