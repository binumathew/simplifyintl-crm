<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="base-url" content="{{ url('/') }}" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>{{isset($title)? $title:config('settings.app_name').' Portal'}}</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App Icons -->
        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
        <!-- App css -->
        <link href="{{ asset('plugins/jquery-steps/jquery.steps.css') }}" rel="stylesheet">
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('css/alertify.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/icons.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('css/style.css?v=0.1') }}" rel="stylesheet" type="text/css"/>

        <script type="text/javascript">
            var base_url = '{{ url('/') }}';
        </script>
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
        <script src="{{ asset('js/additional-methods.min.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('plugins/morris/morris.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/chartist/css/chartist.min.css') }}">
        <link href="{{ asset('css/bootstrap4-toggle.min.css') }}" rel="stylesheet">
        <script src="https://js.stripe.com/v3/"></script>
    </head>

    <body>

        <!-- Loader -->
        <div id="preloader"><div id="status"><div class="spinner"></div></div></div>

        <!-- Navigation Bar-->
        <header id="topnav">
            <div class="topbar-main">
                <div class="container-fluid">

                    <div class="logo">
                        <a href="{{ url('/') }}" class="logo">
                            <img src="{{ asset('images/logo.png') }}" alt="" height="65">
                        </a>
                    </div>

                    <div class="menu-extras topbar-custom">

                        <!-- Search input -->
                        <div class="search-wrap" id="search-wrap">
                            <div class="search-bar">
                                <input class="search-input" type="search" placeholder="Search" />
                                <a href="#" class="close-search toggle-search" data-target="#search-wrap">
                                    <i class="mdi mdi-close-circle"></i>
                                </a>
                            </div>
                        </div>

                        <ul class="list-inline float-right mb-0">
                            <!-- Search -->
                            <li class="list-inline-item dropdown notification-list">
                                <a class="nav-link waves-effect toggle-search" href="#"  data-target="#search-wrap">
                                    <i class="mdi mdi-magnify noti-icon"></i>
                                </a>
                            </li>
                            <!-- Fullscreen -->
                            <li class="list-inline-item dropdown notification-list hide-phone">
                                <a class="nav-link waves-effect" href="#" id="btn-fullscreen">
                                    <i class="mdi mdi-fullscreen noti-icon"></i>
                                </a>
                            </li>
                            <!-- <li class="list-inline-item dropdown notification-list hide-phone">
                                <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                    Pound (£)
                                </a>
                                <div class="dropdown-menu dropdown-menu-right language-switch">
                                    <a class="dropdown-item" href="#"><span> Dollar ($)</span></a>
                                    <a class="dropdown-item" href="#"><span> Dollar ($)</span></a>
                                </div>
                            </li> -->
                            <li class="list-inline-item dropdown notification-list">
                                <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown" href="#" role="button"
                                   aria-haspopup="false" aria-expanded="false">
                                    <i class="ion-ios7-bell noti-icon"></i>
                                    <span class="badge badge-danger noti-icon-badge">0</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-menu-lg">
                                    <!-- item-->
                                    <div class="dropdown-item noti-title">
                                        <h5>Notification (0)</h5>
                                    </div>

                                    <!-- <a href="javascript:void(0);" class="dropdown-item notify-item active">
                                        <div class="notify-icon bg-success"><i class="mdi mdi-cart-outline"></i></div>
                                        <p class="notify-details"><b>Your order is placed</b><small class="text-muted">Dummy text of the printing and typesetting industry.</small></p>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                                        <div class="notify-icon bg-warning"><i class="mdi mdi-message"></i></div>
                                        <p class="notify-details"><b>New Message received</b><small class="text-muted">You have 87 unread messages</small></p>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                                        <div class="notify-icon bg-info"><i class="mdi mdi-martini"></i></div>
                                        <p class="notify-details"><b>Your item is shipped</b><small class="text-muted">It is a long established fact that a reader will</small></p>
                                    </a> -->

                                    <!-- All-->
                                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                                        View All
                                    </a>

                                </div>
                            </li>
                            <!-- User-->
                            <li class="list-inline-item dropdown notification-list">
                                <a class="nav-link dropdown-toggle arrow-none waves-effect nav-user" data-toggle="dropdown" href="#" role="button"
                                   aria-haspopup="false" aria-expanded="false">
                                    <!-- <img src="{{ asset('images/users/avatar-1.jpg') }}" alt="user" class="rounded-circle"> -->
                                    <span>{{ Auth::user()->email }}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                                    <!-- <a class="dropdown-item" href="#"><i class="dripicons-user text-muted"></i> Profile</a> -->
                                    <!-- <a class="dropdown-item" href="#"><span class="badge badge-success pull-right m-t-5">5</span><i class="dripicons-gear text-muted"></i> Settings</a> -->
                                    <!-- <div class="dropdown-divider"></div> -->
                                    <a class="dropdown-item" href="{{ route('logout') }}"><i class="dripicons-exit text-muted"></i> Log Out</a>
                                </div>
                            </li>
                            <li class="menu-item list-inline-item">
                                <!-- Mobile menu toggle-->
                                <a class="navbar-toggle nav-link">
                                    <div class="lines">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </a>
                                <!-- End mobile menu toggle-->
                            </li>
                        </ul>
                    </div>
                    <!-- end menu-extras -->

                    <div class="clearfix"></div>

                </div> <!-- end container -->
            </div>
            <!-- end topbar-main -->

            <!-- MENU Start -->
            <div class="navbar-custom">
                <div class="container-fluid">
                    <div id="navigation">
                        <!-- Navigation Menu-->
                        <ul class="navigation-menu">
                            <li><a href="{{ url('/') }}"><i class="mdi mdi-view-dashboard"></i>Dashboard</a></li>
                            <li class="has-submenu"><a><i class="mdi mdi-account"></i>Subscribers</a>
                                <ul class="submenu">
                                    @if(Helper::has_permission('users') || Helper::has_permission('users','view_own'))
                                    <li><a href="{{ url('/users') }}">Subscriber List</a></li>
                                    @endif
                                    @if(Helper::has_permission('user_details'))
                                    <li><a href="{{ url('/user-details') }}">Subscriber Details</a></li>
                                    @endif
                                    <!-- <li><a href="{{ url('/temp-user') }}">In complete Signup</a></li> -->
                                </ul>
                            </li>

                            @if(Helper::has_permission('conference'))
                           <!--  <li class="has-submenu"><a><i class="mdi mdi-phone-in-talk"></i>Conference</a>
                                <ul class="submenu">
                                    <li><a href="{{ url('/user-list') }}">Users List</a></li>
                                    <li><a href="{{ url('/conference') }}">Conference List</a></li>
                                </ul>
                            </li>  -->
                            @endif
                            <li class="has-submenu">
                                <a><i class="mdi mdi-sim"></i>Orders</a></a>
                                <ul class="submenu">
                                    <li><a href="{{ url('/new-order') }}">Create Order</a></li>
                                    @if(Helper::has_permission('delivery') || Helper::has_permission('delivery', 'view_own'))
                                        <li><a href="{{ url('/delivery') }}">Orders List</a></li>
                                    @endif
                                    @if(Helper::has_permission('orders'))
                                    <!-- <li><a href="{{ url('/abandoned-order') }}">In Complete</a></li> -->
                                    @endif
                                    <!-- <li><a href="#">Services</a></li>
                                    <li><a href="#">Auto-Switch</a></li>
                                    <li><a href="#">Disconnections</a></li>
                                    <li class="has-submenu">
                                        <a href="#" data-toggle="dropdown">Bulk Changes</a>
                                        <ul class="submenu">
                                            <li><a href="#">Attributes</a></li>
                                            <li><a href="#">Billing</a></li>
                                            <li><a href="#">Bill Limits</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="#">PAC Checker</a></li>
                                    <li class="has-submenu">
                                        <a href="#" data-toggle="dropdown">Additional Bundles</a>
                                        <ul class="submenu">
                                            <li><a href="#">Shared Bundles</a></li>
                                        </ul>
                                    </li> -->
                                </ul>
                            </li>

                            <li class="has-submenu"><a><i class="mdi mdi-history"></i>Activation</a>
                                <ul class="submenu">
                                    @if(Helper::has_permission('orders') || Helper::has_permission('orders', 'view_own'))
                                    <li><a href="{{ url('/orders') }}">Orders</a></li>
                                    @endif
                                    @if(Helper::has_permission('porting'))
                                    <li><a href="{{ url('/port-list') }}">Porting</a></li>
                                    @endif
                                </ul>
                            </li>

                            @if(Helper::has_permission('commission') || Helper::has_permission('commission', 'view_own'))
                            <li class="has-submenu"><a><i class="mdi mdi-chart-pie"></i>Dealers</a>
                                <ul class="submenu">
                                @if(Helper::has_permission('dealer') || Helper::has_permission('dealer', 'view_own'))
                                <li><a href="{{ url('/dealers') }}">Dealers</a></li>
                                @endif
                                @if(Helper::has_permission('commission'))
                                   <li><a href="{{ url('/comm-plan') }}">Define Plan Commission</a></li>
                                   <li><a href="{{ url('/comm-plan-dealer') }}">Define Dealer Commission</a></li>
                                   <li><a href="{{ url('/clawback-plan') }}">Define Clawback Plan</a></li>
                                   <li><a href="{{ url('/clawback-dealer') }}">Define Clawback Dealer</a></li>
                                   <li><a href="{{ url('/comm-payment') }}">Commission Payments</a></li>
                                @elseif(Helper::has_permission('commission', 'view_own'))
                                 <li><a href="{{ url('/comm-plan-dealer') }}">My Commission Rates</a></li>
                                 <li><a href="{{ url('/comm-payment') }}">Earnings</a></li>
                                @endif

                                </ul>
                            </li>
                            @endif
                            @if(Helper::has_permission('plans') || Helper::has_permission('plan_management', 'view_own'))
                            <li class="has-submenu"><a><i class="mdi mdi-book-multiple"></i>Plans</a>
                                <ul class="submenu">
                                    @if(Helper::has_permission('plan_management'))
                                    <li><a href="{{ url('/sim-plans') }}">Sim Plans</a></li>
                                    <li><a href="{{ url('/switch-plans') }}">{{ config('settings.app_name') }} App Plans</a></li>
                                    <!-- <li><a href="{{ url('/conf-plans') }}">Conference Plans</a></li> -->
                                    @elseif(Helper::has_permission('plan_management', 'view_own'))
                                    <li><a href="{{ url('/sim-plans') }}">My Plans</a></li>
                                    @endif
                                </ul>
                            </li>
                            @endif
                            @if(Helper::has_permission('reports'))
                            <li class="has-submenu"><a><i class="mdi mdi-chart-pie"></i>Reports</a>
                                <ul class="submenu">
                                   <li><a href="{{ url('/report-dashboard') }}">Dashboard</a></li>
                                   <li><a href="{{ url('/report-autoplan') }}">Subscription</a></li>
                                   <li><a href="{{ url('/report-cardexpiry') }}">Card Expiry</a></li>
                                   <li><a href="{{ url('/report-user') }}">User Report</a></li>
                                   <li><a href="{{ url('/report-usage') }}">Usage Report</a></li>
                                   <li><a href="{{ url('/report-order') }}">Order Report</a></li>
                                    @if(Helper::has_permission('call_history'))
                                    <li><a href="{{ url('/call-history') }}">CDRs</a></li>
                                    @endif
                                    @if(Helper::has_permission('payment_history'))
                                    <li><a href="{{ url('/payment-history') }}">Transactions</a></li>
                                    @endif
                                </ul>
                            </li>
                            @endif
                            @if(Helper::has_permission('reports'))
                            <!-- <li><a href="#"><i class="mdi mdi-ticket-account"></i>Ticketing</a></li> -->
                            @endif
                            <li class="has-submenu"><a><i class="mdi mdi-menu"></i>Settings</a>
                                <ul class="submenu">
                                    @if(Helper::has_permission('settings'))
                                    <li><a href="{{ url('/settings') }}">System Settings</a></li>
                                    @endif
                                    @if(Helper::has_permission('stock','view_own'))
                                    <li><a href="{{ url('/stock-list') }}">Stock List</a></li>
                                    @endif
                                    @if(Helper::has_permission('staff') || Helper::has_permission('staff', 'view_own'))
                                    <li><a href="{{ url('/staff-list') }}">Portal Users</a></li>
                                    @endif
                                </ul>
                            </li>

                        </ul>
                        <!-- End navigation menu -->
                    </div> <!-- end #navigation -->
                </div> <!-- end container -->
            </div> <!-- end navbar-custom -->
        </header>
        <!-- End Navigation Bar-->


        <div class="drag-target"></div>

<!--
		<div class="leftnav" id="top">
			<h1 class="logo"><a href="{{ url('/dashboard') }}">{{ config('settings.app_name') }}</a></h1>
			<ul>
				<li>
					<a href="{{ url('/dashboard') }}" class="fa fa-home {{ request()->is('dashboard') ? 'nav-active' : '' }}">{{ config('settings.app_name') }}</a>
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
				<! -- <li class="{{ request()->is('commission') ? 'nav-active' : '' }}">
					<a href="{{ url('/commission') }}" class="fa fa-gift">Commission</a>
				</li> - ->
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
					<! -- <span class="badge badge-notify">3</span></li> - ->
				<li class="user">
					<a href="{{ url('my-account') }}" class="fa fa-user-circle-o">
						<b>{{ Auth::user()->first_name }}</b>
					</a>
				</li>

			</ul>
		</div> -->
