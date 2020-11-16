<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Login</title>
        <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" media="screen" >
        <link rel="stylesheet" href="{{ asset('css/blue.css') }}" >
        <link rel="stylesheet" href="{{ asset('css/main.css') }}" media="screen" >
        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/icon">
    </head>
    <body>
        <div class="main-wrapper">
            <div class="login-bg-color bg-black-300">
                <div class="row">
                    <div class="col-md-4 col-md-offset-4">
                        <div class="panel login-box">
                            <div class="panel-heading">
                                <div class="panel-title text-center">
                                    <h4>Avoo Mobile Admin</h4>
                                </div>
                            </div>
                            <div class="panel-body p-20">
                                <div class="section-title">
                                    <p class="sub-title text-muted"></p>
                                </div>
                                <form method="POST" action="{{ url('/login') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="email">Email address</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Your Email Id" value="{{ old('email') }}" required autofocus>
                                        @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" type="password" class="form-control" name="password" required>
                                        @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="checkbox op-check">
                                        <label>
                                            <input type="checkbox" name="remember" class="flat-blue-style" {{ old('remember') ? 'checked' : '' }}> <span class="ml-10">Remember Me</span>
                                        </label>
                                    </div>
                                    <div class="form-group mt-20">
                                        <div class="">
                                            <!-- <a href="{{ route('password.request') }}" class="form-link"><small class="muted-text">Forgot Password?</small></a> -->
                                            <button type="submit" class="btn btn-success btn-labeled pull-right">Sign in<span class="btn-label btn-label-right"><i class="fa fa-check"></i></span></button>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                </form>
                                <hr>
                            </div>
                        </div>
                        <p class="text-muted text-center"><small>Copyright © Avoo @php echo date('Y'); @endphp</small></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- <script src="{{ asset('js/jquery.min.js') }}"></script> -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script src="{{ asset('js/icheck.min.js') }}"></script>
        <script>
            $(function(){
                $('input.flat-blue-style').iCheck({
                    checkboxClass: 'icheckbox_flat-blue'
                });
            });
        </script>

    </body>
</html>
