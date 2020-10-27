@if(isset($enquiry_history))
<div id="enquiryHistory" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0" id="myModalLabel">Enquiry History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <ol class="activity-feed mb-0">
                    @if (count($enquiry_history) > 0)
                    @foreach ($enquiry_history as $enquiry)
                    <li class="feed-item">
                       <span class="date font-600 text-muted">{{ date('M d, Y H:i',strtotime($enquiry->created_at))}}</span>
                       <div class="activity-text">Handled By : <span class="font-600 text-muted"> {{$enquiry->handled_by}} </span></div>
                       <span class="activity-text ">Note : {{$enquiry->note}}</span>
                    </li>                            
                    @endforeach
                    @else
                        <li class="feed-item">
                            <span class="activity-text"> No Enquiry </span>
                        </li>
                    @endif
                </ol>
                <hr>                
                <input type="hidden" name="request_id" id="enq_request_id" value="{{Crypt::encrypt($req_id)}}"> 
                <div class="form-group">
                    <label class="form-label">Add Note</label>
                    <input type="text" name="enquiry_note" id="enquiry_note" class="form-control" required>
                    <div id="enquiry_status"></div>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="save_enquiry" class="btn btn-success pull-right">Save</button>
            </div>
        </div>
    </div>
</div>
@endif
@if(isset($order_status))
<div id="oderStatusModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0" id="myModalLabel">Order Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <ol class="activity-feed mb-0">
                    <li class="feed-item">
                        <div class="activity-text">Request Recieved on</div>
                        <span class="date font-600 text-muted">{{ date('M d, Y H:i',strtotime($sim_request->created_at))}}</span>                        
                    </li>
                    @foreach ($order_status as $status)
                    <li class="feed-item">                                            
                       <span class="activity-text ">{{$status->note}}</span>
                       <span class="date font-600 text-muted">{{ date('M d, Y H:i',strtotime($status->time))}}</span>
                    </li>                            
                    @endforeach
                </ol>
                <button type="button" class="btn btn-secondary pull-right" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@if(isset($sim_list))
@php
    $address = json_decode($address);
    $ship_address = $address->street.', '.$address->city.', '.$address->country.', '.$address->postal_code;
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="card m-b-20">
            <div class="card-body">
                <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <table id="sim_detail_content" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                        <thead>
                            <tr>                                            
                                <th>#</th>
                                <th>Phone/Sim Number</th>
                                <th>Shipping Address</th>
                                <th>Box</th>
                                @if(Helper::has_permission('delivery','edit'))
                                <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                         @foreach ($sim_list as $Bundle)
                                @php
                                    $simDetail = $Bundle->getSim();
                                    $i =1;
                                @endphp
                                @foreach ($simDetail['simList'] as $sim)
                                    <tr>
                                        @if($i==1)
                                        <td rowspan="{{ $simDetail['count'] }}">{{ $sim->auto_plan->plan->plan_name }}
                                            ({{$sim->auto_plan->plan->provider}})</td>
                                        @php $i++ @endphp
                                        @endif
                                        <td>{{ ($sim->stock->verified)?$sim->stock->phone_number:'0759xxxxxxx'}}/<br>{{$sim->stock->sim_number }}</td>
                                        <td>{{ $ship_address }}</td>
                                        <td>{{ $sim->stock->box_no }}</td>
                                        @if(Helper::has_permission('delivery','edit'))
                                        <td>
                                            <a href="javascript:void(0);" class="text-muted update_address" data-toggle="tooltip" data-placement="top" title="Update Address" data-id="{{ $sim->id }}"><i class="mdi mdi-pencil mdi-18px"></i></a>&nbsp;&nbsp;
                                            <a href="javascript:void(0);" class="text-muted order_duplicate" data-toggle="tooltip" data-placement="top" title="Order Duplicate" data-id="{{ $sim->id }}"><i class="mdi mdi-rotate-3d mdi-18px"></i></a>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach                                     
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" id="print_sim_detail" class="btn btn-primary pull-right"><i class="fa fa-print"></i> Print</button>
    </div>
</div>
@endif
@if(isset($port_details))
<ol class="activity-feed mb-0">
    <li class="feed-item">
        <div class="activity-text">Request Recieved on</div>
        <span class="date font-600 text-muted">{{ date('M d, Y H:i',strtotime($port_details->created_at))}}</span>                        
    </li>
    @if($port_details)
        @php $description = json_decode($port_details->description); @endphp
        @if($description)
            @foreach ($description as $details)
            <li class="feed-item">                                            
               <span class="activity-text ">{{ $details->proceed_by }}</span>
               <span class="date font-600 text-muted">{{ date('M d, Y H:i',strtotime($details->proceed_at))}}</span>
               
            </li> 
            @endforeach
        @endif                            
    @endif
</ol>
<button type="button" class="btn btn-secondary pull-right" data-dismiss="modal">Close</button>       
@endif
@if(isset($scheduled_task))
<!-- Find addess popup start -->
    <div id="scheduledTaskModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0">{{ isset($task)?'Edit':'Add'}} Scheduled Task</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">                    
                    <div class="card m-b-20">
                        <div class="card-body">
                            <form action="{{ url('/save-task')}}" method="post" id="task-form">
                                <div class="form-group col-md-12">
                                    @if(isset($task))
                                    <input type="hidden" id="task_id" name="id" value="{{$task->id}}">
                                    @endif
                                    <label>Description</label>
                                    <input type="text" id="description" class="form-control" name="description" value="{{isset($task)?$task->description:''}}" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Command</label>                                  
                                    <input type="text" id="command" class="form-control" name="command" value="{{isset($task)?$task->command:''}}" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Status</label> 
                                    <select name="status" class="custom-select form-control">
                                        <option value="1" @php if(!isset($task) || $task->status == 1) echo 'selected'; @endphp>Enabled</option>
                                        <option value="0" @php if(isset($task) && $task->status == 0) echo 'selected'; @endphp>Disabled</option>
                                    </select>                                   
                                </div>                                
                                <div class="form-group col-md-12">
                                    <div id="task_error" class="text-danger"></div>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success pull-right">Submit</button>  
                                </div>
                            </form>                          
                        </div>
                    </div>           
                </div>
            </div>
        </div>
    </div>
    <!-- Find addess popup end -->
@endif
@if(isset($discount_coupon))
<div class="card m-b-20">
    <div class="card-body">
        <form id="coupon-form">
            @csrf
            <div class="form-group col-md-12">
                @if(isset($coupon))
                <input type="hidden" id="coupon_id" name="coupon_id" value="{{$coupon->id}}">
                @endif
                <label>Coupon Code</label>
                <input type="text" id="coupon_code" class="form-control" name="coupon_code" value="{{isset($coupon)?$coupon->coupon_code:''}}" required>
            </div>
            <div class="form-group col-md-12">
                <label>Discount Value</label>                                  
                <input type="text" id="discount_value" class="form-control" name="discount_value" value="{{isset($coupon)?$coupon->discount_value:''}}" required>
            </div>
            <div class="form-group col-md-12">
                <label>Status</label> 
                <select name="is_fixed" class="custom-select form-control">
                    <option value="1" @php if(!isset($coupon) || $coupon->is_fixed == 1) echo 'selected'; @endphp>Fixed Amount</option>
                    <option value="0" @php if(isset($coupon) && $coupon->is_fixed == 0) echo 'selected'; @endphp>Percentage</option>
                </select>                                   
            </div>  
            <div class="form-group col-md-12">
                <label>Expires On</label>                                  
                <input type="text" id="expiry_date" class="form-control" name="expiry_date" value="{{isset($coupon)?$coupon->expiry_date:''}}" required>
            </div>
            <div class="form-group col-md-12">
                <label>Status</label> 
                <select name="status" class="custom-select form-control">
                    <option value="1" @php if(!isset($coupon) || $coupon->status == 1) echo 'selected'; @endphp>Active</option>
                    <option value="0" @php if(isset($coupon) && $coupon->status == 0) echo 'selected'; @endphp>In-Active</option>
                </select>                                   
            </div>                              
            <div class="form-group col-md-12">
                <div id="coupon_status"></div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success pull-right" id="save_coupon">Submit</button>  
            </div>
        </form>                          
    </div>
</div>      
@endif
@if(isset($selected_plans))
<div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
    <p align="left">Please select which connections to add this bolt on to</p>
    <form id="bolt-form">
        <table class="table table-condensed table-bordered no margins">
            <tbody>
                <tr>
                    <td colspan="2">{{ $bolt->plan_name }}</td>                
                    <td width="60px">
                        <label class="checkbox">
                            <input type="checkbox" id="add_to_all" value="all"> All
                        </label>
                    </td>
                </tr>
                @foreach($selected_plans as $selected)
                    @foreach($selected->list as $list)
                    <tr class="info">
                        <td>{{ $selected->product->plan_name }}</td>
                        <td>{{$list->stock->sim_number}} /<br/>{{ ($list->stock->verified)?$list->stock->phone_number:'0759xxxxxxx' }}</td>
                        <td>
                            <label class="checkbox">  
                                <input type="hidden" name="bolt_ons[{{$list->id}}]" value="0">
                                @if($provider == 'app')
                                    <!-- <input type="hidden" name="bolt_ons[{{$list->id}}]" value="{{ $list->app_bolt }}">  -->
                                    <input type="checkbox" class="add_to_all" name="bolt_ons[{{$list->id}}]" value="{{($list->app_bolt)?:$bolt->id}}" {{($list->app_bolt && $list->app_bolt != $bolt->id)?'disabled':''}} {{($list->app_bolt)?'checked':''}}> 
                                @else
                                    <!-- <input type="hidden" name="bolt_ons[{{$list->id}}]" value="{{ $list->sim_bolt }}"> -->
                                    <input type="checkbox" class="add_to_all" name="bolt_ons[{{$list->id}}]" value="{{($list->sim_bolt)?:$bolt->id}}" {{($list->sim_bolt && $list->sim_bolt != $bolt->id)?'disabled':''}} {{($list->sim_bolt)?'checked':''}}> 
                                @endif                          
                            </label>
                        </td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
        <input type="hidden" name="provider" value="{{ $provider }}">
        <!-- <input type="hidden" name="bolt" value="{{ $bolt->id }}"> -->
        <div class="pull-right">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success" id="manage_bolt_ons">Continue</button>
        </div>
    </form>    
</div>
@endif

@if(isset($sim_item_details))
<div class="row">
    <div class="col-md-12">
        <div class="card m-b-20">
            <div class="card-body">
                <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <table id="sim_detail_content" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                        <thead>
                            <tr>                
                                <th>Phone</th>
                                <th>Number to Keep</th>
                                <th>Provision Date</th>
                                <th>Sim Number</th>
                                <th>Status</th>
                                <th>Action</th>                                     
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($simList as $sim)
                            <tr>
                                <td>{{ ($sim->stock->verified)?$sim->stock->phone_number:'0759xxxxxxx'}}</td>
                                <td>{{ ($sim->port)?$sim->porting_to:'-'}}</td>
                                <td>{{ ($sim->provision)?Helper::date_format($sim->provision_date,'M d, Y'):'-'}}</td>
                                <td>{{$sim->stock->sim_number }}</td>
                                <td>
                                    @switch($sim->reg_status)
                                        @case(0)
                                            <span class="badge badge-danger badge-pill"> In Progress</span>
                                        @break
                                        @case(1)
                                            <span  class="badge badge-info badge-pill">Callback</span>
                                        @break
                                        @case(2)
                                            <span  class="badge badge-success badge-pill">Completed</span>
                                        @break
                                        @default
                                            <span>-</span>
                                    @endswitch                                    
                                </td>                                
                                <td>
                                @if($sim->reg_status == 1)
                                <a title="Mark as Welcome Call Completed" href="javascript:void(0);" data-id="{{Crypt::encrypt($sim->id) }}" data-value="{{$sim->id}}" id="ad_crdt_{{$sim->id}}"><i class="mdi mdi-phone-in-talk mdi-24px"></i></a>
                                @endif                                    
                                </td>
                            </tr>                           
                        @endforeach                                     
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary pull-right" data-dismiss="modal">Close</button>        
    </div>
</div>
@endif
@if(isset($provision_check))
<div class="row">
    <div class="col-md-12">
        <div class="card m-b-20">
            <div class="card-body">
                <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <table class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                        <thead>
                            <tr>                
                                <th>ID</th>
                                <th>Process</th>
                                <th>Updated</th>
                                <th>Status</th>                                     
                            </tr>
                        </thead>
                        <tbody>
                        
                            <tr>
                                <td>{{ $status['request_id'] }}</td>
                                <td>
                                    <span class="badge badge-info badge-pill">{{ $status['state'] }}</span>
                                </td>
                                <td>{{ $status['updated_at'] }}</td>                                
                                <td>
                                   <span  class="badge badge-warning badge-pill">{{ $status['request_status'] }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary pull-right" data-dismiss="modal">Close</button>        
    </div>
</div>
@endif

@if(isset($prorata_billing)) 
<div class="row">
    <div class="col-md-12">
        <div class="card m-b-20">
            <div class="card-body">

                <div class="row m-b-20">
                    @foreach($cards as $card)
                    <div class="col-md-3">
                        <div class="card-body">
                            <label for="card_1{{$card->id}}0">
                            <input type="radio" name="credit_card" id="card_1{{$card->id}}0" value="{{Crypt::encrypt($card->id)}}" {{($card->is_default)?'checked':''}}> {{ $card->card_type }} / {{ Helper::date_format($card->card_expiry, 'M Y') }} 
                            </label>
                        </div>
                    </div>
                    @endforeach                                        
                </div>
                <div class="row m-b-20">
                    <div class="col-md-4">
                        <div class="form-group">
                            <!-- <label>Date From</label> -->
                            <input type="text" name="date_from" id="pro_date_from" class="form-control" placeholder="Pro-rata From" value="{{ $date_from }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <!-- <label>Date From</label> -->
                            <input type="text" name="date_to" class="form-control" placeholder="Pro-rata To" value="{{ $date_to }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- <label></label> -->
                        <button type="button" id="prorarta_bill" class="btn btn-info" data-autoplan="{{ $simList[0]->autoplan_id }}">Check</button>
                    </div>
                </div>
                <table id="sim_detail_content" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                    <thead>
                        <tr>                
                            <th>Phone</th>
                            <th>Plan Name</th>
                            <th>Price</th>
                            <th>Pro rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $prorata_amount = 0; @endphp
                        @foreach($simList as $sim)
                        <tr>
                            <td>{{ ($sim->stock->verified)?$sim->stock->phone_number:'0759xxxxxxx'}}</td>
                            <td>{{$sim->auto_plan->plan->plan_name }}</td>
                            <td>{{ $currency.$sim->auto_plan->plan->sell_price }}</td>
                            <td>   
                                @php 
                                $sell_price = $sim->auto_plan->plan->sell_price;
                                $endofday = date('t'); $cur_day = date('d',strtotime($date_from));                
                                $remain_days = $endofday - $cur_day + 1;
                                $pro_rata_bill = ($sell_price/$endofday) * $remain_days;
                                $prorata_amount += $pro_rata_bill;                    
                                @endphp
                                {{ $currency. Helper::number_format($pro_rata_bill) }}                              
                            </td>                
                        </tr>                           
                        @endforeach  
                        <tr>
                            <td class="text-right" colspan="3">Total Amount</td>
                            <td>{{ $currency. Helper::number_format($prorata_amount) }} </td>
                        </tr>
                        <input type="hidden" id="autoplan_id" value="{{ $simList[0]->autoplan_id }}">

                    </tbody>
                </table>
            </div>            
        </div>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success pull-right" id="collect_prorarta">Collect</button>
    </div>
    <span class="text-danger pull-right" id="error-message"></span>
</div>
@endif
@if(isset($gateway))
@if($gateway->gateway == 'Stripe')
@php $amount = ($purchase->total_amount * 100); 
$currency    = strtolower($purchase->currency);
@endphp
<div class="tab-pane active p-3"  role="tabpanel">
<input type="hidden" name="gateway" value="{{ $gateway->gateway }}">
<div class="row m-b-20">
    @foreach($cards as $card)
    <div class="col-md-4">
        <div class="card-body">
            <label for="card_1{{$card->id}}0">
            <input type="radio" name="card_list" class="radio_card_list" id="card_1{{$card->id}}0" value="{{Crypt::encrypt($card->id)}}" {{($card->is_default)?'checked':''}}> {{ $card->card_type }} / {{ Helper::date_format($card->card_expiry, 'M Y') }} 
            </label>
            @if($card->is_default)
            <input type="hidden" name="credit_card" class="selected_card" value="{{ Crypt::encrypt($card->id) }}">
            @endif
        </div>
    </div>
    @endforeach                                        
</div>  

<div class="col-md-12 d-flex justify-content-center">
<form action="{{url('/payment')}}" method="POST">
    <input type="hidden" name="gateway" value="{{ $gateway->gateway }}">
    @csrf
    <script
        src="https://checkout.stripe.com/checkout.js" class="stripe-button"
        data-key="{{ config('app.stripe_api_key') }}"
        data-amount="{{ $amount }}"
        data-name="Order Payment"
        data-description=""
        data-image="{{ asset('public/images/logo.png') }}"
        data-locale="auto"
        data-currency="{{$currency}}">

    </script>
    <button type="submit" class="btn btn-success waves-effect waves-light stripePayBtn">Add New Card</button>
</form>
</div>
@if($cards->isNotEmpty())
<div class="row">
    <div class="col-md-12 m-t-20">
        <span id="payment_status" class="text-danger"></span>
        <button type="button" class="btn btn-success waves-effect waves-light pull-right confirm_payment">Submit</button>
    </div> 
</div>
@endif
</div>
@else
<div class="tab-pane active p-3"  role="tabpanel">                      
    <input type="hidden" name="credit_card" class="selected_card" value="new">
    <input type="hidden" name="gateway" value="{{ $gateway->gateway }}">
    <div class="row m-b-20">
        @foreach($cards as $card)
        <div class="col-md-4">
            <div class="card-body">
                <label for="card_1{{$card->id}}0">
                <input type="radio" name="card_list" class="radio_card_list" id="card_1{{$card->id}}0" value="{{Crypt::encrypt($card->id)}}" {{($card->is_default)?'checked':''}}> {{ $card->card_type }} / {{ Helper::date_format($card->card_expiry, 'M Y') }} 
                </label>
                @if($card->is_default)
                <input type="hidden" name="credit_card" class="selected_card" value="{{ Crypt::encrypt($card->id) }}">
                @endif
            </div>
        </div>
        @endforeach                                        
    </div>

    <div class="row m-b-20 {{ ($cards->isEmpty())?'d-none':'' }}">
        <!-- <div class="col-md-12 text-center"><b>OR</b></div> -->
        <div class="col-md-12 d-flex justify-content-center">
            <a href="javascript:void(0);" class="btn btn-success waves-effect waves-light btn_add_new_card">Add New Card</a>
        </div>
    </div>                          
    <div class="m-b-20 card_form {{ ($cards->isEmpty()) ? '' : 'd-none' }}">
        <div class="row card-body">
            <div class="col-md-12">
                <h4 class="mt-0 header-title">Enter Card Details</h4>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" id="card_number" class="form-control card_details card_number" name="card_number"  placeholder="Ex : 1234-0000-4444-5555">
                    <input type="hidden" id="card_type" name="card_type" value="Card">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Expiry Month</label>
                    <input type="text" name="expiry_month" class="form-control card_details" placeholder="MM">                                   
                   
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Expiry Year</label>
                    <input type="text" name="expiry_year" class="form-control card_details" placeholder="YY">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>CVV</label>
                    <input type="text" name="card_cvv" class="form-control card_details" placeholder="Card secuity number" minlength="3" maxlength="4">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Card Holder</label>
                    <input type="text" name="card_holder" class="form-control card_details" placeholder="Card Holder Name">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Street Address</label>
                    <input type="text" name="card_street" class="form-control card_details" placeholder="Street Address">
                </div>
            </div>                            
            <div class="col-md-4">
                <div class="form-group">
                    <label>Postal Code</label>
                    <input type="text" name="card_postcode" class="form-control card_details" placeholder="Postcode">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 m-t-20">
            <span id="payment_status" class="text-danger"></span>
            <button type="button" class="btn btn-success waves-effect waves-light pull-right confirm_payment">Submit</button>
        </div> 
    </div>
</div>
@endif
<script type="text/javascript">
    $(document).ready(function () {
        $('.card_number').mask('0000-0000-0000-0000');

        $("#pay-form").validate({
            // errorClass: "invalid form-error",
            // errorElement: 'div',
            errorPlacement: function(error, element) {                       
                element.addClass('border border-danger');
                error.insertAfter(element);
            },
            ignore: ":hidden",
            rules: {
                card_number:{
                    required: {
                        depends: function () { 
                          return ($("input[name='credit_card']").val() == 'new')?true:false; 
                        }
                    },
                    regex:/^[0-9-]{19}$/,
                },
                card_holder:{
                    required: {
                        depends: function () { 
                          return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                        }
                    },
                    lettersonly: true
                },
                expiry_year: {
                    required: {
                        depends: function () { 
                          return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                        }
                    },
                    maxlength: 2,
                    minlength: 2,
                    min: 20
                },
                expiry_month: {
                    required: {
                        depends: function () { 
                          return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                        }
                    },
                    maxlength: 2,
                    minlength: 1,
                    max: 12,min: 1
                },
                card_cvv: {
                    required: {
                        depends: function () { 
                          return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                        }
                    },
                    maxlength: 4,
                    minlength: 3,
                    number:true,
                },
                card_postcode: {
                    required: {
                        depends: function () { 
                          return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                        }
                    },
                    regex:/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9][A-Za-z]?))))\s?[0-9][A-Za-z]{2})$/
                },
                card_street: {
                    required: {
                        depends: function () { 
                          return ($("input[name='credit_card']"). val() == 'new')?true:false; 
                        }
                    },
                },
                credit_card: 'required',
            },
            messages: {
                card_holder:{
                    lettersonly:"Enter a valid card holder name!",
                }, 
                card_postcode: {
                    required:"Enter your postal code!",
                    regex:"Invalid Postalcode",               
                },
                expiry_year: "Enter card expiry year!", 
                expiry_month: "Enter card expiry month!",                  
                terms_cond: 'Agree the Terms and Conditions to proceed!.', 
                credit_card: 'Please choose a credit card or add a new one'                
            }
        });

        $.validator.addMethod(
            "regex",
            function(value, element, regexp) {
                var re = new RegExp(regexp);
                return this.optional(element) || re.test(value);
            },
            "Please check your input."
        );
    });
</script>
@endif

@if(isset($stock_details))                  
<div class="m-2">
    <form action="{{ url('/stock-manage') }}" id="stock-details" method="POST">
    <div class="row card-body">
        <div class="col-md-6">
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" id="phone_number" class="form-control" name="phone_number" value="{{ ($stock->verified)?$stock->phone_number:'0759xxxxxxx'}}" disabled>
                <input type="hidden" id="stock_id" name="stock_id" value="{{ Crypt::encrypt($stock->id) }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Sim Number</label>
                <input type="text" name="sim_number" class="form-control" value="{{$stock->sim_number}}" disabled>                                   
               
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>IMSI Number</label>
                <input type="text" name="imsi_number" class="form-control" value="{{$stock->imsi_number}}" disabled>
            </div>
        </div>        
        <div class="col-md-3">
            <div class="form-group">
                <label>Price</label>
                <input type="text" name="price" class="form-control card_details" value="{{$stock->price}}">
            </div>
        </div>  
         <div class="col-md-3">
            <div class="form-group">
                <label>Box Number</label>
                <input type="text" name="box_no" class="form-control" value="{{$stock->box_no}}">
            </div>
        </div>   
        <div class="col-md-6">
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" class="form-control" value="{{$stock->category}}">
            </div>
        </div> 
        <div class="col-md-6">
            <div class="form-group">
                <label>Provider</label>
                <input type="text" name="provider" class="form-control" value="{{$stock->provider}}">
            </div>
        </div>               
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="dealer_id" class="col-form-label">Dealer</label>
                <select name="dealer_id" id="dealer_id" class="custom-select">
                    @foreach($dealers as $dealer)
                    <option value="{{Crypt::encrypt($dealer->id)}}" {{ ($stock->dealer_id == $dealer->id)?'selected':'' }}>{{ $dealer->name }}</option>
                    @endforeach                                               
                </select>
            </div>
        </div>               
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="stock_status" class="col-form-label">Status</label>
                <select name="status" id="stock_status" class="custom-select">
                    <option value="1"  {{ ($stock->status == 1)?'selected':'' }}>Active</option>
                    <option value="0"  {{ ($stock->status == 0)?'selected':'' }}>Sold</option>                                                 
                </select>
            </div>
        </div> 
    </div>
    </form>
    @if($stock->status == 1)
    <div class="row">
        <div class="col-md-12 m-t-20">
            <button type="button" class="btn btn-success waves-effect waves-light pull-right stock_manage">Update</button>
        </div> 
    </div> 
    @endif
</div>
@endif

@if(isset($subscription))  
    @foreach($options as $key => $option)
    <div class="row">  
        <div class="col-md-6">
            <input type="hidden" id="subscription_cancel_id" value="{{$subscription->id}}">       
            <label for="card_{{$key}}">
                <input type="radio" name="cancel_type" id="card_{{$key}}" {{ ($key==2)?'checked':'' }} value="{{Crypt::encrypt($key)}}"> {{$option}}
            </label>
        </div>
    </div>
    @endforeach
    <div class="row">
        <div class="col-md-12 m-t-20">
            <span id="sub_status" class="text-danger"></span>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success waves-effect waves-light pull-right confirm_subscription_cancel">Submit</button>
        </div> 
    </div>                                   
@endif

@if(isset($card_list))
    <p>Choose a credit card</p>
    <div class="row m-b-20">
        <input type="hidden" id="subscription_renew_id" name="renew_id" value="{{$renew_id}}">
        @foreach($cards as $card)
        <div class="col-md-3 p-1">
            <div class="card-body {{($card->card_expiry < Carbon::now() )?'border border-danger':''}}">
                <label for="card_1{{$card->id}}0">
                <input type="radio" name="user_credit_card" id="card_1{{$card->id}}0" value="{{Crypt::encrypt($card->id)}}" {{($card->id == $card_id)?'checked':''}}> <small>{{ $card->card_type }} / {{ Helper::date_format($card->card_expiry, 'M Y') }}</small>
                </label>
            </div>
        </div>
        @endforeach
    </div>
    <div class="row">
        <span id="sub_status" class="text-danger"></span>
        <div class="col-md-12 m-t-20">            
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success waves-effect waves-light pull-right change_subscription_card">Update</button>
        </div> 
    </div>                                 
@endif
@if(isset($renewal_list))     
<div class="card-body m-2">      
    <form id="renewal-form" method="POST">
        <input type="hidden" name="autoplan_id" value="{{$auto_plan->id}}">
        <div class="form-group">
            <div class="radio">
                <label><input type="radio" class="m-l-10" name="renewal_type" value="1" checked>  Renew Now </label>
                <label><input type="radio" class="m-l-10" name="renewal_type" value="2">  On Next Renewal </label>
            </div>
        </div>  

        <div class="form-group" id="mode_select">
            <div class="radio">
                <label><input type="radio" class="m-l-10" name="payment_mode" value="1" checked>  Not Paid </label>                                
                <label><input type="radio" class="m-l-10" name="payment_mode" value="2">  Paid </label>
            </div>                      
        </div>

        <div class="d-none" id="transaction_details">
            <div class="form-group">
                <select name="payment_method" class="custom-select">
                    <option value="Direct Cash">Direct Cash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Paypal">Paypal</option>
                    <option value="Braintree">Braintree</option>
                    <option value="Stripe">Stripe</option>
                </select>
            </div>
            <div class="form-group">                            
                <input class="form-control"  type="text" name="custom_txn_id" placeholder="Transaction/Reference ID">                           
            </div>
            <div class="form-group">                                
                <input class="form-control"  type="text" name="custom_description" placeholder="Description">                           
            </div>          
        </div>
        @if($auto_plan->plan_type == 'sim')
        <div class="form-group">                
            <div class="radio">
                @if($auto_plan->bundle_id == 0)
                <select name="plan_id" class="custom-select">
                    <option> Choose Plan </option>                  
                    @foreach($plans as $plan)
                    <option value="{{$plan->id}}" {{($auto_plan->plan_id == $plan->id)? 'selected':''}}> {{$plan->plan_name}} </option>
                    @endforeach                                     
                </select>
                @else
                <select name="bundle_id" class="custom-select">
                    <option> Choose Plan </option>
                    @foreach($plans as $plan)
                    <option value="{{$plan->id}}" {{($auto_plan->bundle_id == $plan->id)? 'selected':''}}> {{$plan->plan_name.' '. $currency.$plan->sell_price.' ('.$plan->sim_count.' Sim)'}} </option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>
        @else
        <div class="form-group">                
            <div class="radio">                   
                <select name="plan_id" class="custom-select">
                    <option> Choose Plan </option>
                    @foreach($plans as $plan)
                    <option value="{{$plan->id}}" {{($auto_plan->plan_id == $plan->id)? 'selected':''}}> {{ $plan->plan_name }} </option>
                    @endforeach                                     
                </select>                    
            </div>
        </div>
        @endif                       
        <div id="subscription_error"></div>                                             
    </form>
    <div class="row">
        <span id="sub_status" class="text-danger"></span>
        <div class="col-md-12 m-t-20">            
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success waves-effect waves-light pull-right"  id="subscriptionRenewal">Submit</button>
        </div> 
    </div> 
</div>
@endif