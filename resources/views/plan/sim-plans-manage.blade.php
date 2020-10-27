@extends('layouts.home')
@section('content')
<!-- page wrapper start -->
        <div class="wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="btn-group pull-right">
                                <ol class="breadcrumb hide-phone p-0 m-0">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">Sim Plans</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Sim Plans</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">

                        <div class="card m-b-20">

                            <div class="card-body right-nav">

                                <ul>
                                    @if(Helper::has_permission('plan_management') || Helper::has_permission('plan_management', 'view_own'))
                                    <li><a href="{{ url('/sim-plans') }}" class="selected">Sim Plans</a></li>                                    
                                    @endif
                                    @if(Helper::has_permission('conference_plan') || Helper::has_permission('conference_plan', 'view_own'))
                                    <li><a href="{{ url('/conf-plans') }}">Conference Plans</a></li>
                                    @endif
                                </ul>                            

                            </div>

                        </div>

                    </div>
                    <div class="col-10">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('sim-plans-actions') }}" id="sim-plans-actions-form" method="POST">
                                        @csrf
                                    <h5>{{ (!empty($plan)) ? 'Edit' : 'Add' }} Plan</h5>
                                    <input type="hidden" name="edit_id" value="{{ (!empty($plan)) ? Crypt::encrypt($plan->id) : ''}}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Plan Name</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Plan Name" name="plan_name" maxlength="50" value="{{ (!empty($plan) ? $plan->plan_name : '')}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Switch Billing Plan</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Switch Billing Plan" name="switch_billing_plan" maxlength="10" value="{{ (!empty($plan) ? $plan->switch_billing_plan : 0)}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Sim Billing Plan</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Sim Billing Plan" name="sim_billing_plan" maxlength="10" value="{{ (!empty($plan) ? $plan->sim_billing_plan : 0)}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Description</label>
                                                <div class="input-group">
                                                    <textarea name="description" class="form-control ">{{ (!empty($plan) ? $plan->description : '')}}</textarea>
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Buy Price</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Buy Price" name="buy_price" maxlength="8" value="{{ (!empty($plan) ? $plan->buy_price : '')}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Sell Price</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Sell Price" name="sell_price" maxlength="8" value="{{ (!empty($plan) ? $plan->sell_price : '')}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Data Limit</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Data Limit" name="data_limit" maxlength="25" value="{{ (!empty($plan) ? $plan->data_limit : '')}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Call Limit</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Call Price" name="call_limit" maxlength="25" value="{{ (!empty($plan) ? $plan->call_limit : '')}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>International Call Limit</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="International Call Limit" name="in_call_limit" maxlength="25" value="{{ (!empty($plan) ? $plan->in_call_limit : '')}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Message Limit</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Message Limit" name="msg_limit" maxlength="25" value="{{ (!empty($plan) ? $plan->msg_limit : '')}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Period</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Period" name="period" maxlength="8" value="{{ (!empty($plan) ? $plan->period : '')}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                    @php
                                                    $status = ['Inactive','Active'];@endphp
                                                    @foreach($status as $skey =>$slist)
                                                    @php
                                                    $sel = "";
                                                    if(!empty($plan) && $plan->status == $skey ){
                                                        $sel = "selected";
                                                    }
                                                    @endphp
                                                    <option {{$sel}} value="{{ $skey }}">{{ $slist}}</option> 
                                                    @endforeach
                                                </select>
                                            </div>
                                            <span></span>
                                        </div> 
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Listed</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Listed" name="listed" maxlength="8" value="{{ (!empty($plan) ? $plan->listed : 0)}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Order By</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control " placeholder="Order By" name="order_by" maxlength="8" value="{{ (!empty($plan) ? $plan->order_by : 0)}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Provider</label>
                                                <select name="provider" class="form-control custom-select" required>
                                                    <option value="">Choose</option>
                                                    @foreach($provider as $skey =>$slist)
                                                    @php
                                                    $sel = "";
                                                    if(!empty($plan) && $plan->provider == $slist->short_code ){
                                                        $sel = "selected";
                                                    }
                                                    @endphp
                                                    <option {{$sel}} value="{{ $slist->short_code }}">{{ $slist->provider }}</option> 
                                                    @endforeach
                                                </select>
                                            </div>
                                            <span></span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Category</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Category" name="category" maxlength="8" value="{{ (!empty($plan) ? $plan->category : 0)}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Dealer</label>
                                                <select name="dealer_id" class="form-control custom-select" required>
                                                    <option value="">Choose</option>
                                                    @foreach($dealer as $skey =>$slist)
                                                    @php
                                                    $sel = "";
                                                    if(!empty($plan) && $plan->dealer_id == $slist->id ){
                                                        $sel = "selected";
                                                    }
                                                    @endphp
                                                    <option {{$sel}} value="{{ Crypt::encrypt($slist->id) }}">{{ $slist->first_name.''.$slist->last_name.' - '.$slist->promocode }}</option> 
                                                    @endforeach
                                                </select>
                                            </div>
                                            <span></span>
                                        </div>
                                        <div class=" col-md-12">
                                            <button type="submit" id="searchBtn" class="btn btn-primary ">{{ (!empty($plan)) ? 'Update' : 'Add' }} Plan</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                            
                                        </div>
                                    </div>
                                    </form>
                                </div>                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- page wrapper end -->

@endsection