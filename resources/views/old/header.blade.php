<!doctype html>
<html>
	<head>
		<title>{{isset($title)? $title:'AvooAdmin'}}</title>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="base-url" content="{{ url('/') }}" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, minimum-scale=1.0, maximum-scale=1.0">

		<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css">

		<link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" type="text/css">

		<link href="{{ asset('css/font.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/animate.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/sweetalert.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/portalstyle.css?version=1.2') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/portalbreaks.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/progress-wizard.min.css') }}" rel="stylesheet" type="text/css">

		<link href="{{ asset('css/smart_wizard.min.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/smart_wizard_theme_arrows.min.css') }}" rel="stylesheet" type="text/css">

		<link href="{{ asset('css/avoo.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/homestyle.css') }}" rel="stylesheet" type="text/css">

		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">

		<link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/icon">
		<script type="text/javascript">
            var base_url = '<?php echo url('/'); ?>';
        </script>
		<style>
			.nav-active {
				background-color:#3399ff !important;
			}
			.no-js #loader { display: none;  }
			.js #loader { display: block; position: absolute; left: 50%; top: 0; }
			.se-pre-con {
				position: fixed;
				left: 0px;
				top: 0px;
				width: 100%;
				height: 100%;
				z-index: 9999;
				background: url('{{ asset('images/Preloader_1.gif') }}')  center no-repeat #fff;
			}
			.bill1{
				height:400px;
			}
			.but
			{
				display: inline-block;
				padding: 5px 15px;
				border-radius: 3px;
				background-color: #024a80;
				color: #fff;
				margin: 10px 5px;
				font-size: 15px;
			}
			.but.clicked
			{
				display: inline-block;
				padding: 5px 15px;
				border-radius: 3px;
				background-color: red;
				color: #fff;
				margin: 10px 5px;
				font-size: 15px;
			}
			.scbtns.scbtns2
			{
				display: inline-block;
				padding: 5px 15px;
				border-radius: 3px;
				background-color: red;
				color: #fff;
				margin: 10px 5px;
				font-size: 15px;
			}
		</style>
		<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
		<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->

		<script src="{{ asset('js/jquery.min.js') }}"></script>
		<script src="{{ asset('js/jquery.validate.min.js') }}"></script>
		<script src="{{ asset('js/additional-methods.min.js') }}"></script>
		<script src="{{ asset('js/modernizr.js') }}"></script>
		<script src="{{ asset('js/jquery-ui.js') }}"></script>

	</head>

	<body>
		<div class="se-pre-con"></div>
		<div id="loadingsign" ></div>
		<div class="leftnav" id="top">
			<h1 class="logo"><a href="{{ url('/dashboard') }}">Avoo Mobile </a></h1>
			<ul>
				<li>
					<a href="{{ url('/dashboard') }}" class="fa fa-home {{ request()->is('dashboard') ? 'nav-active' : '' }}">Avoo Mobile</a>
				</li>
				@if(Helper::has_permission('users'))
				<li>
					<a href="{{ url('/users') }}" class="fa fa-users {{ request()->is('users') ? 'nav-active' : '' }}">Users</a>
				</li>
				@endif
				@if(Helper::has_permission('user_details'))
				<li>
					<a href="{{ url('/show-user') }}" class="fa fa-user {{ request()->is('show-user') ? 'nav-active' : '' }}">Users Details</a>
				</li>
				@endif
				@if(Helper::has_permission('staff'))
				<li>
					<a href="{{ url('/staff') }}" class="fa fa-user-circle-o {{ request()->is('staff') ? 'nav-active' : '' }}">Staff</a>
				</li>
				@endif
				@if(Helper::has_permission('dealer'))
				<li>
					<a href="{{ url('dealers') }}" class="fa-user-circle {{ request()->is('dealers') ? 'nav-active' : '' }}">Dealer</a>
				</li>
				@endif
				@if(Helper::has_permission('commission'))
				<!-- <li class="{{ request()->is('commission') ? 'nav-active' : '' }}">
					<a href="{{ url('/commission') }}" class="fa fa-gift">Commission</a>
				</li> -->
				<li class="drop {{ (Route::currentRouteName() == 'commission')? 'open' : '' }}">
					<a href="#" class="fa fa-gift">Commission</a>
					<div class="bigdrop">
						<ul>
							<li class="{{ request()->is('comm-userlist') ? 'nav-active' : '' }}">
								<a href="{{ url('comm-userlist') }}" class="fa fa-money">User</a>
							</li>
							<li class="{{ request()->is('comm-payment-list') ? 'nav-active' : '' }}">
								<a href="{{ url('comm-payment-list') }}" class="fa fa-money">Dealer Commission</a>
							</li>
						</ul>
					</div>
				</li>
				@endif

				@if(Helper::has_permission('discount_coupons'))

				<li class="{{ request()->is('discount-coupon') ? 'nav-active' : '' }}">
					<a href="{{ url('/discount-coupon') }}" class="fa fa-percent">Discount Coupons</a>
				</li>
				@endif
				@if(Helper::has_permission('fraudsters'))

				<li class="{{ request()->is('fraudster-list') ? 'nav-active' : '' }}">
					<a href="{{ url('/fraudster-list') }}" class="fa fa-user-secret">Fraudsters</a>
				</li>
				@endif

				@if(Helper::has_permission('plan_purchase'))
				<li class="{{ request()->is('plan-purchase') ? 'nav-active' : '' }}">
					<a href="{{ url('/plan-purchase') }}" class="fa fa-phone">Plan Purchase</a>
				</li>
				@endif
				@if(Helper::has_permission('delivery'))
				<li class="{{ request()->is('delivery-management') ? 'nav-active' : '' }}">
					<a href="{{ url('/delivery-management') }}" class="fa fa-truck">Delivery Management</a>
				</li>
				@endif
				@if(Helper::has_permission('orders'))

				<li class="{{ request()->is('orders') ? 'nav-active' : '' }}">
					<a href="{{ url('/orders') }}" class="fa fa-magic">Orders</a>
				</li>
                <li class="{{ request()->is('sim-activate') ? 'nav-active' : '' }}">
                        <a href="{{ url('sim-activate') }}" class="fa fa-paper-plane">Sim Activation</a>
                </li>
				@endif

				@if(Helper::has_permission('porting'))
					<li class="{{ request()->is('porting') ? 'nav-active' : '' }}">
						<a href="{{ url('porting') }}" class="fa fa-recycle">Porting</a>
					</li>
				@endif
				@if(Helper::has_permission('settings'))
				<li class="drop {{ (Route::currentRouteName() == 'settings')? 'open' : '' }}">
					<a href="#" class="fa fa-cogs">Settings</a>
					<div class="bigdrop">
						<ul>
							<li class="{{ request()->is('settings') ? 'nav-active' : '' }}">
								<a href="{{ url('/settings') }}" class="fa fa-cogs">Genral</a>
							</li>
							@if(Helper::has_permission('sim_management'))
							<li>
								<a href="{{ url('sim-management') }}" class="fa fa-empire {{ request()->is('sim-management') ? 'nav-active' : '' }}">Sim Stock</a>
							</li>
							@endif
							@if(Helper::has_permission('roles'))
							<li>
								<a href="{{ url('/roles') }}" class="fa fa-user-circle {{ request()->is('roles') ? 'nav-active' : '' }}">Roles</a>
							</li>
							@endif
							<li class="{{ request()->is('countries') ? 'nav-active' : '' }}">
								<a href="{{ url('/countries') }}" class="fa fa-globe">Countries</a>
							</li>
							<li class="{{ request()->is('credits') ? 'nav-active' : '' }}">
								<a href="{{ url('/credits') }}" class="fa fa-money">Credits</a>
							</li>
							<li class="{{ request()->is('comm-planlist') ? 'nav-active' : '' }}">
								<a href="{{ url('comm-planlist') }}" class="fa fa-money">Plan Commission</a>
							</li>

							<li class="{{ request()->is('staff-commission') ? 'nav-active' : '' }}">
								<a href="{{ url('staff-commission') }}" class="fa fa-money">Sale  Commission</a>
							</li>

							<li class="{{ request()->is('comm-staff-portlist') ? 'nav-active' : '' }}">
								<a href="{{ url('comm-staff-portlist') }}" class="fa fa-money">Porting Commission</a>
							</li>

							<li class="{{ request()->is('comm-staff-paycreditlist') ? 'nav-active' : '' }}">
								<a href="{{ url('comm-staff-paycreditlist') }}" class="fa fa-money">Credit Commission</a>
							</li>
							<li class="{{ request()->is('switch-management')?'nav-active':'' }}">
								<a href="{{ url('/switch-management') }}" class="fa fa-connectdevelop">Switch Management</a>
							</li>
							@if(Helper::has_permission('firewall'))
							<li class="{{ request()->is('firewall') ? 'nav-active' : '' }}">
								<a href="{{ url('/firewall') }}" class="fa fa-shield">Firewall</a>
							</li>
							@endif

							<li class="{{ request()->is('comm-staff-list') ? 'nav-active' : '' }}">
								<a href="{{ url('comm-staff-list') }}" class="fa fa-money">Staff Commission</a>
							</li>

							@if(Helper::has_permission('did_pool'))
							<li class="{{ request()->is('did-pool') ? 'nav-active' : '' }}">
								<a href="{{ url('/did-pool') }}" class="fa fa-phone">DID Pool</a>
							</li>
							@endif
							<li class="{{ request()->is('scheduled-tasks') ? 'nav-active' : '' }}">
								<a href="{{ url('/scheduled-tasks') }}" class="fa fa-android">Cron Jobs</a>
							</li>
							<li class="{{ request()->is('api-logger') ? 'nav-active' : '' }}">
								<a href="{{ url('/api-logger') }}" class="fa fa-superpowers">API Log</a>
							</li>
						</ul>
					</div>
				</li>
				@endif
				@if(Helper::has_permission('reports'))
				<li class="drop {{ (Route::currentRouteName() == 'reports')? 'open' : '' }}">
					<a href="#" class="fa fa-pie-chart">Reports</a>
					<div class="bigdrop">
						<ul>
							<li class="{{ request()->is('report-order') ? 'nav-active' : '' }}">
								<a href="{{ url('report-order') }}" class="fa fa-money">Order</a>
							</li>
							<li class="{{ request()->is('report-port') ? 'nav-active' : '' }}">
								<a href="{{ url('report-port') }}" class="fa fa-paper-plane">Porting</a>
							</li>
							<li class="{{ request()->is('report-eelog') ? 'nav-active' : '' }}">
								<a href="{{ url('report-eelog') }}" class="fa fa-phone">EE Log</a>
							</li>
							<li class="{{ request()->is('report-autoplan') ? 'nav-active' : '' }}">
								<a href="{{ url('report-autoplan') }}" class="fa fa-phone">Auto Plan</a>
							</li>
							<li class="{{ request()->is('report-autorecharge') ? 'nav-active' : '' }}">
								<a href="{{ url('report-autorecharge') }}" class="fa fa-phone">Auto Recharge</a>
							</li>
							<li class="{{ request()->is('report-subscription') ? 'nav-active' : '' }}">
								<a href="{{ url('report-subscription') }}" class="fa fa-phone">Subscription</a>
							</li>
							<li class="{{ request()->is('report-staff-comm') ? 'nav-active' : '' }}">
								<a href="{{ url('report-staff-comm') }}" class="fa fa-phone">Staff Commission</a>
							</li>
							<li class="{{ request()->is('report-dealer-comm') ? 'nav-active' : '' }}">
								<a href="{{ url('report-dealer-comm') }}" class="fa fa-phone">Dealer Commission</a>
							</li>
						</ul>
					</div>
				</li>
				@endif
				@if(Helper::has_permission('payment_history'))

				<li class="{{ request()->is('payment-history') ? 'nav-active' : '' }}">
					<a href="{{ url('/payment-history') }}" class="fa fa-money">Payment History</a>
				</li>
				@endif
				@if(Helper::has_permission('call_history'))

				<li class="{{ request()->is('call-history') ? 'nav-active' : '' }}">
					<a href="{{ url('/call-history') }}" class="fa fa-phone">Call History</a>
				</li>
				@endif

				@if(Helper::has_permission('plan_management'))
				<li class="drop {{ (Route::currentRouteName() == 'plan')? 'open' : '' }}">
					<a href="#" class="fa fa-product-hunt">Plans</a>
					<div class="bigdrop">
						<ul>
						<li class="{{ request()->is('plan-management') ? 'nav-active' : '' }}">
							<a href="{{ url('/plan-management') }}" class="fa fa-star">Plan Management</a>
						</li>

						<li class="{{ request()->is('package-management') ? 'nav-active' : '' }}">
							<a href="{{ url('/package-management') }}" class="fa fa-star">Packages Management</a>
						</li>

						</ul>
					</div>
				</li>
				@endif
				@if(Helper::has_permission('fraudsters'))
				<li class="{{ request()->is('throttles') ? 'nav-active' : '' }}">
					<a href="{{ url('/throttles') }}" class="fa fa-laptop">Login Attempts</a>
				</li>
				@endif
				<li>
					<a class="fa-sign-out" href="{{ route('logout') }}"> Log Out </a>
				</li>
			</ul>
		</div>

		<div class="header clearfix">
			<div class="navclick fa fa-bars"></div>
			<ul>
				@if(Helper::has_permission('settings') || Auth::user()->role == 3)
				<li class="bell"><a href="{{ url('notification-log') }}" class="fa fa-bell"></a>
				@endif
					<!-- <span class="badge badge-notify">3</span></li> -->
				<li class="user">
					<a href="{{ url('my-account') }}" class="fa fa-user-circle-o">
						<b>{{ Auth::user()->first_name }}</b>
					</a>
				</li>

			</ul>
		</div>
