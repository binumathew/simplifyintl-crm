  
                    <div class="row">
                        <div class="col-md-6">
                            @if(Helper::has_permission('report'))
                            <div class="card m-b-20">
                                <div class="card-body revenue-overview-details">
                                    <h4 class="mt-0 header-title">Revenue Overview</h4>
                                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                        @php $active = 'active';  @endphp
                                        @foreach($reports as $key => $report)
                                        <li class="nav-item">
                                            <a class="nav-link {{$active}}" data-toggle="tab" href="#months{{$key}}" role="tab">
                                                <span class="d-none d-md-block">{{$key}} Months</span>
                                                <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                                            </a>
                                        </li>
                                        @php $active = ''; @endphp
                                        @endforeach
                                    </ul>

                                    <div class="tab-content">
                                        @php $active = 'active';  @endphp
                                        @foreach($reports as $key => $report)
                                        <div class="tab-pane {{$active}} p-3" id="months{{$key}}" role="tabpanel">
                                            <div class="row p-3">
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-purple">{{$currency.Helper::number_format($report['revenue'])}}</span>Revennue
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-blue-grey">{{$currency.Helper::number_format($report['expense'])}}</span>Expense
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-blue-grey">{{$currency.Helper::number_format($report['refund'])}}</span>Refund
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-blue-grey">{{$currency.Helper::number_format($report['fee'])}}</span>Fee
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-brown">{{$currency.Helper::number_format($report['vat'])}}</span>Vat
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mini-stat-info">
                                                        <span class="counter text-teal">{{$currency.Helper::number_format($report['profit'])}}</span>Profit
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php $active = ''; @endphp
                                        @endforeach                                         
                                    </div>
                                </div>
                            </div>
                            @endif
                            <!-- <div class="card m-b-20">
                                <div class="card-body parent-account-css">
                                    <h4 class="mt-0 header-title">Balance</h4>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mini-stat-info"><span class="counter text-purple">1.5 GB</span>remaining of 1.5 GB<br />renew in 48 mins</div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <button type="button" class="btn btn-outline-secondary waves-effect">Check Balance</button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->

                            <div class="card m-b-20">
                                <div class="card-body parent-account-css">
                                    <h4 class="mt-0 header-title">Plan Details</h4>
                                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#sim-plan" role="tab">
                                                <span class="d-none d-md-block">SIM Plan</span><span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                                            </a>
                                        </li>
                                        @if(Helper::get_option('enable_switch_support'))
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#mobile-plan" role="tab">
                                                <span class="d-none d-md-block">Mobile APP Plan</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active p-3" id="sim-plan" role="tabpanel">
                                            <!-- <div class="row m-b-20">
                                                <div class="col-md-8"><div class="mini-stat-info"><span class="counter text-purple">20</span>calling minutes remaining</div></div>
                                                <div class="col-md-4 text-right"><button type="button" class="btn btn-outline-secondary waves-effect">Recharge</button></div>
                                            </div> -->
                                            <div class="row plan-data-listing">
                                                <div class="col-md-6">Data</div>
                                                @if($plan && $plan->prorata)
                                                    @php
                                                      $planstart   = $plan->created_at;
                                                      $data_limit = $plan->plan->data_limit;
                                                      $planend     = Carbon::parse($planstart)->endOfMonth();
                                                      $noofdays    = Carbon::parse($planstart)->daysInMonth;
                                                      $daysbetween = Carbon::parse($planstart)->diffInDays($planend) + 1;
                                                      $usagelimit  = round((($data_limit/$noofdays) * $daysbetween),2);
                                                    @endphp
                                                    <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{($plan)?round($plan->data_usage / pow(1024, 3),2):'0'}} GB</span>Out of {{($plan)?(($plan->plan->data_limit != 0) ? $usagelimit:'Unlimited'):'0'}} GB</div></div>
                                                @elseif($plan)
                                                    <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{($plan)?round($plan->data_usage / pow(1024, 3),2):'0'}} GB</span>Out of {{($plan)?(($plan->plan->data_limit != 0) ? $plan->plan->data_limit:'Unlimited'):'0'}} GB</div></div>
                                                @else
                                                    <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">0</span>Out of 0</div></div>
                                                @endif
                                            </div>
                                            <div class="row plan-data-listing">
                                                <div class="col-md-6">Voice</div>
                                                <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{($plan)? Helper::secondsToTime($plan->call_usage):'00:00:00'}} </span>Out of {{($plan)?$plan->plan->call_limit:0}} Min</div></div>
                                            </div>                                           
                                            <div class="row plan-data-listing">
                                                <div class="col-md-6">SMS</div>
                                                <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{($plan)? $plan->sms_usage / 60:'0'}} SMS</span>Out of {{($plan)?$plan->plan->msg_limit:0}} SMS</div></div>
                                            </div>
                                        </div>
                                        @if(Helper::get_option('enable_switch_support'))
                                        <div class="tab-pane p-3" id="mobile-plan" role="tabpanel">
                                            <div class="row plan-data-listing">
                                                <div class="col-md-6">Balance Credit</div>
                                                <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{$user->balance->balance_amount}}</span></div></div>
                                            </div>                                             
                                            <div class="row plan-data-listing">
                                                <div class="col-md-6">Balance Minutes</div>
                                                <div class="col-md-6 text-right"><div class="mini-stat-info"><span class="counter">{{$user->balance->balance_minutes}} min</span>remaining of {{ '-' }} min</div></div>
                                            </div>                                                                                       
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card m-b-20">
                                <div class="card-body">
                                    <h4 class="mt-0 header-title">Account</h4>
                                    <div class="cus-right-box"><h6>Parent Account</h6><br />
                                        <div class="row">
                                            <div class="col-md-1 col-xs-1"><i class="mdi mdi-sim sim-color"></i></div>
                                            <div class="col-md-10 col-xs-10"><span><b>{{$parent->name }}</b> </span> {{'0'.ltrim($parent->phone,'+44')}}</div>
                                            <div class="col-md-1 col-xs-1">
                                                <form id="show_user_{{$parent->id}}" method="post" action="{{url('/user-details')}}">
                                                    @csrf<input type="hidden" name="identifier" value="{{$parent->phone}}">
                                                    <a class="show_user_data" user-id="{{$parent->id}}"><i class="mdi mdi-eye"></i></a>
                                                </form>
                                                <!-- <a href="parent-account.html"><i class="mdi mdi-eye"></i></a></div> -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="cus-right-box"><h6>Child Account</h6><br />
                                        @foreach ($children as $child)
                                        @php $plan_data = $child->userPlan(); @endphp
                                        <div class="row cus-bor-btm">
                                            <div class="col-md-1 col-xs-1"><i class="mdi mdi-sim sim-color"></i></div>
                                            <div class="col-md-10 col-xs-10"><span><b>{{$child->name}}</b> </span> 0{{ltrim($child->phone,'+44') }}</div>
                                            <div class="col-md-1 col-xs-1">
                                                <form id="show_user_{{$child->id}}" method="post" action="{{url('/user-details')}}">
                                                    @csrf<input type="hidden" name="identifier" value="{{$child->phone}}">
                                                    <a class="show_user_data" user-id="{{$child->id}}"><i class="mdi mdi-eye"></i></a>
                                                </form>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card m-b-20">
                                <div class="card-body note-body">
                                    <h4 class="mt-0 header-title">Notes</h4>
                                    <div class="card m-b-20">
                                        @foreach($notes as $note)

                                        <div class="card-header">
                                            <!-- <a href="javascript:void(0);" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-close font-18"></i></a> -->
                                            <p class="font-16">{{ $note->note }}</p>
                                            <footer class="blockquote-footer text-muted">
                                            {{ $note->handled_by }} <cite>{{ Helper::date_format($note->created_at) }}</cite>
                                            </footer>
                                        </div>
                                        @endforeach                                                                            
                                    </div>
                                    <div class="m-t-20">
                                        <label class="text-muted">Add Note</label>
                                        <textarea id="note" class="form-control" maxlength="225" rows="3" placeholder=""></textarea>
                                    </div>
                                    <div class="m-t-20">
                                        <span id="error-note" class="text-danger"></span>
                                        <button type="button" class="btn btn-success waves-effect waves-light add_note pull-right"><strong>Add</strong></button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card m-b-20">
                                <div class="card-body">
                                    <div id="accordion" class="parent-accor">
                                        <div class="card">
                                            <div class="card-header p-3" id="headingOne">
                                                <h6 class="m-0"><a href="#collapseOne" class="text-dark" data-toggle="collapse" aria-expanded="true" aria-controls="collapseOne">
                                                Recharge</a></h6>
                                            </div>
                                            <div id="collapseOne" class="collapse show"
                                                aria-labelledby="headingOne" data-parent="#accordion">
                                                <div class="card-body">
                                                    <label>Vodafone Number</label>
                                                    <input type="search" class="form-control form-control-sm m-b-20" placeholder="" aria-controls="datatable">
                                                    <button type="button" class="btn btn-success waves-effect waves-light">Recharge Now</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header p-3" id="headingTwo">
                                                <h6 class="m-0"><a href="#collapseTwo" class="text-dark collapsed" data-toggle="collapse" aria-expanded="false" aria-controls="collapseTwo"> My Statement </a>
                                                </h6>
                                            </div>
                                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                                                <div class="card-body">My Statement</div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header p-3" id="headingThree">
                                                <h6 class="m-0"> <a href="#collapseThree" class="text-dark collapsed" data-toggle="collapse" aria-expanded="false" aria-controls="collapseThree"> Recharge History </a> </h6>
                                            </div>
                                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                                                <div class="card-body">Recharge History</div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header p-3" id="headingFourth">
                                                <h6 class="m-0"> <a href="#collapseFourth" class="text-dark collapsed" data-toggle="collapse" aria-expanded="false" aria-controls="collapseFourth"> Invoice History </a> </h6>
                                            </div>
                                            <div id="collapseFourth" class="collapse" aria-labelledby="headingFourth" data-parent="#accordion">
                                                <div class="card-body"> Invoice History </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script type="text/javascript">
                        $(document).ready(function(){    
                            $(document).on('click', '.show_user_data', function(e) {              
                                e.preventDefault();   
                                var id = $(this).attr('user-id');                
                                $('#show_user_'+id).submit();
                            });
                        });
                    </script>