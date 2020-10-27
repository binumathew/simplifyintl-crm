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
                                    <li class="breadcrumb-item active">Conference Plans</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Conference Plans</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">

                        <div class="card m-b-20">

                            <div class="card-body right-nav">

                                <ul>
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
                                    <form action="{{ url('conf-plans-actions') }}" id="conf-plans-actions-form" method="POST">
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
                                                    <input type="text" class="form-control " placeholder="Switch Billing Plan" name="switch_billing_plan" maxlength="10" value="{{ (!empty($plan) ? $plan->switch_billing_plan : '')}}">
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
                                                <label>Minutes</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Minutes" name="minutes" maxlength="12" value="{{ (!empty($plan) ? $plan->minutes : '')}}">
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
                                                <label>Provider</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Provider" name="provider" maxlength="25" value="{{ (!empty($plan) ? $plan->provider : '')}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Flag</label>
                                               <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Flag" name="flag" maxlength="20" value="{{ (!empty($plan) ? $plan->flag : '')}}">
                                                </div>
                                                <span></span>
                                            </div>
                                            
                                        </div> 
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Switch ID</label>
                                                <div class="input-group">
                                                <select name="switch_id" class="form-control custom-select">
                                                <option value="">Choose</option>
                                                @if(!empty($switchtemplate))
                                                @foreach($switchtemplate as $skey =>$slist)
                                                @php
                                                    $sel = "";
                                                    if(!empty($plan) && $plan->switch_id == $slist->id ){
                                                        $sel = "selected";
                                                    }
                                                    @endphp
                                                    <option {{$sel}} value="{{ $slist->id }}">{{ $slist->currency}}</option>
                                                @endforeach
                                                @endif
                                                </select>
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>APP ID</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="APP ID" name="in_app_id" maxlength="25" value="{{ (!empty($plan) ? $plan->in_app_id : 0)}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Participant Limit</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Participant Limit" name="participant_limit" maxlength="5" value="{{ (!empty($plan) ? $plan->participant_limit : 0)}}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Plan Term Annual</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control " placeholder="Plan Term Annual" name="plan_term_annual" maxlength="10" value="{{ (!empty($plan) ? $plan->plan_term_annual : 0)}}">
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