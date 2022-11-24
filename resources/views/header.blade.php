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
                            @php $notifyLog = Helper::notifications();  
                            $logcount = ($notifyLog->isNotEmpty()) ? count($notifyLog) : 0 ;
                            @endphp
                            <li class="list-inline-item dropdown notification-list">
                                <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown" href="#" role="button"
                                   aria-haspopup="false" aria-expanded="false">
                                    <i class="ion-ios7-bell noti-icon"></i>
                                    <span class="badge badge-danger noti-icon-badge">{{$logcount}}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-menu-lg" style="overflow-y: scroll;height: 410px;">
                                    <!-- item-->
                                    <div class="dropdown-item noti-title">
                                        <h5>Notification ({{$logcount}})</h5>
                                    </div>
                                    @if($notifyLog->isNotEmpty())
                                    @foreach($notifyLog as $key => $list)
                                    @if(!is_null($list->payload))
                                    @php
                                        $payload = json_decode($list->payload);
                                    @endphp
                                    @if($payload->type == 'order_activation')
                                    <a href="javascript:void(0);" class="dropdown-item notify-item notify_log" data-type="{{ $payload->type }}" data-id="{{ $list->id }}" data-param="{{ $payload->order_id }}">
                                        <div class="notify-icon bg-success"><i class="mdi mdi-cart-outline" ></i></div>
                                        <p class="notify-details"><small class="text-muted">{{ $list->message }}</small></p>
                                    </a>
                                    @elseif($payload->type == 'msisdn_update')
                                    <a href="javascript:void(0);" class="dropdown-item notify-item notify_log" data-type="{{ $payload->type }}" data-id="{{ $list->id }}" data-param="{{ $payload->order_id }}">
                                        <div class="notify-icon bg-warning" ><i class="mdi mdi-message"></i></div>
                                        <p class="notify-details"><small class="text-muted">{{ $list->message }}</small></p>
                                    </a>
                                    @endif
                                    @endif
                                    @endforeach
                                    <div class="notify_form"></div>
                                    @endif
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
                                    <!-- <a href="javascript:void(0);" class="dropdown-item notify-item">
                                        View All
                                    </a> -->

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
                            @if(Helper::has_permission('plans') || Helper::has_permission('plan_management', 'view_own'))
                           <!--  <li class="has-submenu"><a><i class="mdi mdi-book-multiple"></i>Plans</a>
                                <ul class="submenu">
                                    @if(Helper::has_permission('plan_management'))
                                    <li><a href="{{ url('/sim-plans') }}">Sim Plans</a></li>
                                    <li><a href="{{ url('/switch-plans') }}">{{ config('settings.app_name') }} App Plans</a></li>
                                    @elseif(Helper::has_permission('plan_management', 'view_own'))
                                    <li><a href="{{ url('/sim-plans') }}">My Plans</a></li>
                                    @endif
                                </ul>
                            </li> -->
                            @endif
                            <!-- <li class="has-submenu"><a><i class="mdi mdi-menu"></i>Settings</a>
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
                            </li> -->
                            @if(Helper::has_permission('eSim_activation'))
                            <li class="has-submenu"><a><i class="mdi mdi-menu"></i>Activation</a>
                                <ul class="submenu">
                                    <li><a href="{{ url('/eSim-bulk-activation') }}">eSim Bulk Activataion</a></li>
                                </ul>
                            </li>
                            @endif

                        </ul>
                        <!-- End navigation menu -->
                    </div> <!-- end #navigation -->
                </div> <!-- end container -->
            </div> <!-- end navbar-custom -->
        </header>
        <!-- End Navigation Bar-->


        <div class="drag-target"></div>

