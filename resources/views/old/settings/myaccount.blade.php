@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6">
					<h1>My Account <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#accountEdit">Edit</button></h1>
					<div class="col-md-12">
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
					</div>
					<div class="col-md-12">
	                <div class="card">
                        <div class="card-content">
                        	<div class="row">
                                <div class="col-md-4"><p><strong>Name:</strong></p></div>
                                <div class="col-md-8"><p>{{ Auth::user()->first_name.' '.Auth::user()->last_name }}</p></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><p><strong>E-mail:</strong></p></div>
                                <div class="col-md-8"><p>{{ Auth::user()->email }}</p></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><p><strong>Promocode:</strong></p></div>
                                <div class="col-md-8"><p>{{ Auth::user()->promocode }}</p></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><p><strong>Current Password:</strong></p></div>
                                <div class="col-md-2"><p>• • • • • •</p></div>
                                <div class="col-md-2"><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#ChangePass">Change password</button></div>
                            </div>
                        </div>
                    </div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Modal -->
  <div class="modal fade avoopopup" id="ChangePass" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Change Password</h4>
        </div>
        <div class="modal-body">
          <div class="col-md-12">
                <p>Password must be alpha numerical and first alphabet should be capital. Minimum characters required is 8 and maximum characters allowed is 12.</p>
           </div>
            <form action="{{url('/change-password')}}" method="post">
            	@csrf
			  <div class="form-group">
			    <label for="password">Password:</label>
			    <input type="password" class="form-control" minlength="8" name="password" id="password" required>
			    <span class="fa fa-eye eyespan showtype" ></span>
			  </div>
			  <div class="form-group">
			    <label for="confirmpassword">Confirm Password:</label>
			    <input type="password" class="form-control" minlength="8" id="confirm_password" name="confirm_password" required> <span class="fa fa-eye eyespan showtype"></span>
			  </div>
			  <button type="submit" class="btn btn-info btn-sm">Change</button>
			</form>
        </div>
      </div>
  </div>
</div>
	<!-- Modal -->
  <div class="modal fade avoopopup" id="accountEdit" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">My Account</h4>
        </div>
        <div class="modal-body">
            <form action="{{url('/account-edit')}}" method="post">
            	@csrf
			  <div class="form-group">
			    <label for="Name">First Name:</label>
			    <input type="text" value="{{ Auth::user()->first_name }}" class="form-control" name="first_name" id="first_name" autocomplete="off" required>
			  </div>
			  <div class="form-group">
			    <label for="LastName">Last Name:</label>
			    <input type="text" value="{{ Auth::user()->last_name }}" class="form-control" name="last_name" id="last_name" autocomplete="off" required>
			  </div>
			  <div class="form-group">
			    <label for="Email">Email:</label>
			    <input type="text" value="{{ Auth::user()->email }}" class="form-control"id="email" name="email" autocomplete="off" required> </span>
			  </div>
			  <button type="submit" class="btn btn-primary btn-sm">Save</button>
			</form>
        </div>
      </div>
  </div>
</div>
<script type="text/javascript">
	$(document).ready(function(){  
	$(document).on( 'click', '.showtype', function(){ 
        var pwd_type = $(this).prev('input').attr('type');
            pwd_type = (pwd_type === 'password')?'text':'password';
            $(this).prev('input').attr('type', pwd_type);
        });
	});
</script>
@endsection