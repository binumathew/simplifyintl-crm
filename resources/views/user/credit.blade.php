<div class="card m-b-20">
    <div class="card-body">
        <div id="accordion" class="parent-accor">
            <div class="card">
                <a href="#collapseOne" class="text-dark" data-toggle="collapse" aria-expanded="true" aria-controls="collapseOne">
                    <div class="card-header p-3" id="headingOne"><h6 class="m-0"> Add Credit </h6></div>
                </a>
                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                    <form id="pay-form" class="pay-form">
                        <input type="hidden" name="payment_for" value="add_credit">
                    <div class="card-body">                       
                        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                            @foreach($gateways as $gateway)                              
                            <li class="nav-item">
                                <a class="nav-link gateway {{ ($gateway->is_default)?'active':'' }}" data-gateway="{{ $gateway->id }}">
                                    <span class="d-none d-md-block">{{ $gateway->gateway }}</span><span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                                </a>
                            </li>                                
                            @endforeach
                            <li class="nav-item">
                                <a class="nav-link gateway {{ ($gateways->isEmpty()) ? 'active' : '' }}" data-toggle="tab" href="#cash" role="tab">
                                    <span class="d-none d-md-block">Cash Payment</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                                </a>
                            </li>                                                                           
                        </ul>

                        <div class="tab-content">
                            @foreach($gateways as $gateway)   
                                @if($gateway->is_default)
                                <div class="tab-pane {{ ($gateway->is_default)?'active':'' }} p-3" id="{{ $gateway->gateway }}" role="tabpanel">
                                
                                    <input type="hidden" name="credit_card" class="selected_card" value="new">
                                    <input type="hidden" name="gateway" value="{{ $gateway->gateway }}">
                                    <div class="row m-b-20">
                                        @foreach($gateway->cards as $card)
                                        <div class="col-md-4">
                                            <div class="card-body">
                                                <label for="card_1{{$card->id}}0">
                                                <input type="radio" required name="card_list" class="radio_card_list" id="card_1{{$card->id}}0" value="{{Crypt::encrypt($card->id)}}" {{($card->is_default)?'checked':''}}> {{ $card->card_type }} / {{ Helper::date_format($card->card_expiry, 'M Y') }} 
                                                </label>
                                                @if($card->is_default)
                                                <input type="hidden" name="credit_card" class="selected_card" value="{{ Crypt::encrypt($card->id) }}">
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach                                        
                                    </div>

                                    <div class="row m-b-20 {{ ($gateway->cards->isEmpty())?'d-none':'' }}">                                        
                                        <div class="col-md-12 d-flex justify-content-center">
                                            <a href="javascript:void(0);" class="btn btn-success waves-effect waves-light btn_add_new_card">Add New Card</a>
                                        </div>
                                    </div>
                                    @if($gateway->gateway != 'Stripe')                            
                                    <div class="m-b-20 card_form {{ ($gateway->cards->isEmpty()) ? '' : 'd-none' }}">
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
                                    @endif
                                </form>
                                    @if(($gateway->gateway != 'Stripe') || $gateway->cards->isNotEmpty())
                                    <div class="row m-b-20 ">                                        
                                        <div class="col-md-12">
                                           <button type="button" class="btn btn-success waves-effect waves-light pull-right confirm_payment">Submit</button>
                                        </div>
                                    </div>
                                    @endif

                                    @if($gateway->gateway == 'Stripe')
                                    <!-- <div class="col-md-12 justify-content-center card_form {{ ($gateway->cards->isEmpty()) ? '' : 'd-none' }}">
                                        <div class="row card-body">
                                            <div class="col-md-12">
                                                <h4 class="mt-0 header-title">Enter Card Details</h4>
                                            </div>
                                            <div class="col-md-6">
                                                <div id="card-element" class="field"></div>
                                                <div class="outcome">
                                                    <div class="error"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-error" id="payment-error"></div>
                                            </div>
                                            <div class="col-md-7">
                                                <button type="button" class="btn btn-success waves-effect waves-light pull-right confirm_payment" data-payment="stripe">Pay Now</button>
                                            </div>
                                        </div>
                                    </div> -->
                                    @endif
                                </div>
                                @endif
                            @endforeach
                            <div class="tab-pane p-3" id="cash" role="tabpanel">
                                <form id="cashpay-form" class="cashpay-form"> 
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Payment Method</label>
                                            <div class="input-group">
                                               <select class="form-control" name="payment_method" id="payment_method" required>
                                                <option value="">Choose</option>
                                                <option value="DirectCash">Direct Cash</option>
                                                @if($gateways->isNotEmpty())
                                                @foreach ($gateways as $mode)
                                                    <option  value="{{$mode->gateway}}">{{ $mode->gateway }}</option>
                                                @endforeach
                                                @endif

                                                </select>
                                            </div>
                                            <span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Transaction Reference ID</label>
                                            <div class="input-group">
                                               <input type="text" class="form-control" name="custom_txn_id" maxlength="25" placeholder="Transaction Reference ID">
                                            </div>
                                            <span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Description</label>
                                            <div class="input-group">
                                               <input type="text" class="form-control" name="custom_description" placeholder="Description">
                                            </div>
                                            <span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Credit Amount</label>
                                            <div class="input-group">
                                               <select class="form-control" name="credit_amount" id="credit_amount" required>
                                                <option value="">Choose</option>
                                                @if($credit_amount->isNotEmpty())
                                                @foreach ($credit_amount as $list)
                                                    <option  value="{{Crypt::encrypt($list->id)}}">{{ $list->amount }}</option>
                                                @endforeach
                                                @endif
                                                </select>
                                            </div>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>
                               </form>
                                <div class="row m-b-20 ">                                        
                                    <div class="col-md-12">
                                       <button type="button" class="btn btn-success waves-effect waves-light pull-right confirm_payment">Submit</button>
                                    </div>
                                </div>
                                <!-- <div class="row m-b-20">
                                    <div class="col-md-4"><div class="card-body"><input type="radio"> Direct Payment</div></div>
                                    
                                </div> -->
                            </div>
                        </div>
                                
                        <!-- <div class="alert alert-success" role="alert">Promocode <strong>WEB</strong> Applied</div> -->
                        <!-- <label>Vodafone Number</label>
                        <input type="search" class="form-control form-control-sm m-b-20" placeholder="9810004033" aria-controls="datatable">
                        <button type="button" class="btn btn-success waves-effect waves-light">Submit</button> -->
                    </div>
                </div>
            </div>
            <div class="card">
                <a href="#collapseTwo" class="text-dark collapsed" data-toggle="collapse" aria-expanded="false" aria-controls="collapseTwo">
                    <div class="card-header p-3" id="headingTwo"><h6 class="m-0"> Debit </h6></div>
                </a>
                
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                    <div class="card-body">My Statement</div>
                </div>
            </div>
            <div class="card">
                <a href="#collapseThree" class="text-dark collapsed" data-toggle="collapse" aria-expanded="false" aria-controls="collapseThree">
                    <div class="card-header p-3" id="headingThree"> <h6 class="m-0"> Deduct </h6></div>
                </a>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                    <div class="card-body">Recharge History</div>
                </div>
            </div>            
        </div>
    </div>
</div>