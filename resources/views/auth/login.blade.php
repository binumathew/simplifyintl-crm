<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>{{config('settings.app_name')}} Login</title>
        <!-- App Icons -->
        <link rel="shortcut icon" href="{{ asset('public/images/favicon.ico') }}" type="image/icon">
        <!-- Basic Css files -->
        <link href="{{ asset('public/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('public/css/icons.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('public/css/style.css') }}" rel="stylesheet" type="text/css">
    </head>
    <body class="fixed-left">
        <!-- Loader -->
        <div id="preloader"><div id="status"><div class="spinner"></div></div></div>
        <!-- Begin page -->
        <div class="accountbg" style="background: url('{{ asset('public/images/bg-2.jpg') }}');background-size: cover;"></div>
        <div class="wrapper-page account-page-full">
            <div class="card"> <!-- style="margin-top:50%;" -->
                <div class="card-body"> <!-- style="margin-width:200%;" -->

                    <h3 class="text-center m-0">
                        <a href="{{ url('/') }}" class="logo logo-admin"><img src="{{ asset('public/images/logo.png') }}" height="50" alt="logo"></a>
                    </h3>
                    <div class="p-3">
                        <h4 class="font-18 m-b-5 text-center">Welcome Back !</h4>
                        <p class="text-muted text-center">Sign in to continue to {{config('settings.app_name')}}.</p>
                        <form method="POST" action="{{ url('/login') }}" class="form-horizontal m-t-30" >
                            @csrf 
                            <div class="form-group">
                                <label for="username">Email / Username</label>
                                <input type="text" class="form-control" id="email" name="email" placeholder="Enter Username" value="{{ old('email') }}" required autofocus>
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
                            <!-- <div class="form-group">
                                <label for="userpassword">Password</label>
                                <input type="password" class="form-control" id="userpassword" placeholder="Enter password">
                            </div> -->
                            <div class="form-group row m-t-20">
                                <div class="col-sm-6">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="customControlInline" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="customControlInline">Remember me</label>
                                    </div>
                                </div>
                                <div class="col-sm-6 text-right">
                                    <button class="btn btn-primary w-md waves-effect waves-light" type="submit">Log In</button>
                                </div>
                            </div>
                            <!-- <div class="form-group m-t-10 mb-0 row">
                                <div class="col-12 m-t-20">
                                    <a href="" class="text-muted"><i class="mdi mdi-lock"></i> Forgot your password?</a>
                                </div>
                            </div> -->
                        </form>
                    </div>
                </div>
            </div>
            <div class="m-t-40 text-center">
                <!-- <p class="">Don't have an account ? <a href="#" class="font-500 font-14 font-secondary green-link"> Signup Now </a> </p> -->
                <p class="">Copyright © {{ date('Y') }} {{config('settings.app_name')}}. All Rights Reserved.</p>
            </div>
        </div>
        <!-- jQuery  -->
        <script src="{{ asset('public/js/jquery.min.js') }}"></script>
        <script src="{{ asset('public/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('public/js/modernizr.min.js') }}"></script>
        <script src="{{ asset('public/js/jquery.slimscroll.js') }}"></script>
        <script src="{{ asset('public/js/waves.js') }}"></script>
        <script src="{{ asset('public/js/jquery.nicescroll.js') }}"></script>
        <script src="{{ asset('public/js/jquery.scrollTo.min.js') }}"></script>
        <!-- App js -->
        <script src="{{ asset('public/js/app.js') }}"></script>
    </body>
</html>