@extends('layouts.home')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Bulk Activation</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Bulk Activation</h4>
                    </div>
                </div>
            </div>
            <div class="row">
<!--                 <div class="col-md-2">

                    <div class="card m-b-20">

                        <div class="card-body right-nav">

                            <ul>

                                <li><a href="{{ url('/products') }}" class="selected">List</a></li>                                   
                            </ul>                            

                        </div>

                    </div>

                </div> -->
                <div class="col-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div class="order-search">
                                <form action="{{ url('eSim-bulk-activation-actions') }}" id="eSim-bulk-activation-actions-form" method="POST">
                                    @csrf
                                <h5>Bulk Activation</h5>
                                <div class="row">
                                	<div class="col-md-2">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control " placeholder="First Name" name="first_name" id="first_name" value="" autocomplete="none">
                                        </div>
                                        <span></span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control " placeholder="Last Name" name="last_name" id="last_name" value="" autocomplete="none"> 
                                        </div>
                                        <span></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Country</label>
                                        <select name="country_id" class="form-control custom-select" id="country_id">
                                            <option value="">Choose</option>
                                            @foreach($countries as $akey =>$country)
                                            <option  value="{{ $country->id }}">{{ $country->country_name}}</option> 
                                            @endforeach
                                        </select>
                                    </div>
                                    <span></span>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Mobile</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control " placeholder="Mobile" name="phone" id="phone" value="" autocomplete="none">
                                        </div>
                                        <span></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control " placeholder="Email" name="email" id="email" value="" autocomplete="none">
                                        </div>
                                        <span></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Sim</label>
                                        <select multiple name="stock_id[]" class="form-control custom-select" id="stock_id">
                                            <option value="">Choose</option>
                                            @foreach($stocks as $akey =>$stock)
                                            <option  value="{{ $stock->id }}">{{ $stock->sim_number}}</option> 
                                            @endforeach
                                        </select>
                                    </div>
                                    <span></span>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Plan</label>
                                        <select name="plan_id" class="form-control custom-select" id="plan_id">
                                            <option value="">Choose</option>
                                            @foreach($plans as $akey =>$plan)
                                            <option  value="{{ $plan->id }}">{{ $plan->plan_name.' '.$plan->description.' '.$plan->data_limit.' GB  '.$currency.$plan->sell_price }}</option> 
                                            @endforeach
                                        </select>
                                    </div>
                                    <span></span>
                                </div>
                                
                                    <div class=" col-md-12">
                                        <button type="submit" id="searchBtn" class="btn btn-primary ">Submit</button>
                                        <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                        
                                    </div>
                                </div>
                                </form>
                                <br>
                            </div>                                
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- page wrapper end -->
<script type="text/javascript">
	$("#plan_id").select2({
        selectOnClose: true,
    });
    $("#country_id").select2({
        selectOnClose: true,
    });
    $("#stock_id").select2({
        closeOnSelect: false,
        allowClear: true
    });
    $('#resetBtn').on('click', function(e) {
       $('#eSim-bulk-activation-actions-form')[0].reset();
       $('#plan_id,#stock_id').val('').trigger('change');    
    });
</script>
@endsection