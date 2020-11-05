<div class="card m-b-20">
    <div class="card-body">
        <div class="card-header p-3" id="headingOne"><h6 class="m-0"> Credit/Debit </h6></div>
        <div id="creditdebitWizard">
            <ul class="nav">
               <li><a class="nav-link" href="#step-1">Payment For</a></li>
               <li><a class="nav-link" href="#step-2">Amount</a></li>
               <li><a class="nav-link" href="#step-3">Payment</a></li>
            </ul>
 
    <div class="tab-content">
        <form id="pay-form" class="pay-form">
           <div id="step-1" class="tab-pane" role="tabpanel">
              <br>
                <div class="row" style="margin-left: 2%;">
                    <div class="col-md-2">
                        <div class="form-check-inline">
                          <label class="form-check-label">
                            <input type="radio" class="form-check-input payment_for" name="payment_for" value="addcredit" checked>Add Credit
                          </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check-inline">
                          <label class="form-check-label">
                            <input type="radio" class="form-check-input payment_for" name="payment_for" value="debit">Debit
                          </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check-inline disabled">
                          <label class="form-check-label">
                            <input type="radio" class="form-check-input payment_for" name="payment_for" value="deduct">Deduct
                          </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check-inline">
                          <label class="form-check-label">
                            <input type="radio" class="form-check-input payment_for" name="payment_for" value="addcard">Add Card
                          </label>
                        </div>
                    </div>
                </div>
           </div>
           <div id="step-2" class="tab-pane" role="tabpanel" style="height: 400px;">
              <br>
                <div class="row tax_div" style="margin-left: 2%;">
                 <div class="col-md-3">
                      <div class="form-check-inline">
                        <label class="form-check-label">
                          <input type="radio" class="form-check-input collect_amount" name="tax_type" value="1" checked>Tax Included
                        </label>
                      </div>
                 </div>
                 <div class="col-md-3">
                      <div class="form-check-inline">
                        <label class="form-check-label">
                          <input type="radio" class="form-check-input collect_amount" name="tax_type" value="2">Tax Excluded
                        </label>
                      </div>
                 </div>
                 <div class="col-md-3">
                      <div class="form-check-inline">
                        <label class="form-check-label">
                          <input type="radio" class="form-check-input collect_amount" name="tax_type" value="3">No Tax
                        </label>
                      </div>
                 </div>
                </div>
                <br>
                <div class="row amount_div" style="margin-left: 2%;">
                        <div class="col-md-2">
                            <div class="form-check-inline">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input custom_amount_check" value="0" id="custom_amount_check" name="custom_amount_check">Custom
                              </label>
                            </div>
                        </div> 
                        <div class="col-md-3 dropdwn_amt">
                            <div class="form-group">
                                <label>Amount</label>
                                <div class="input-group">
                                   <select class="form-control collect_amount " name="credit_amount" id="credit_amount" required>
                                    <option value="">Choose</option>
                                    @if($credit_amount->isNotEmpty())
                                    @foreach ($credit_amount as $list)
                                        <option  value="{{Crypt::encrypt($list->id)}}">{{ $list->amount }}</option>
                                    @endforeach
                                    @endif
                                    </select>
                                </div>
                                <span class="error amt_error"></span>
                            </div>
                        </div>
                        <div class="col-md-3 cust_amt" style="display: none;">
                            <div class="form-group">
                                <label>Amount</label>
                                <div class="input-group">
                                    <input type="text" class="form-control collect_amount " name="custom_amount" id="custom_amount" placeholder="Custom Amount" required maxlength="8">
                                </div>
                                <span class="error amt_error"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Description</label>
                                <div class="input-group">
                                   <input type="text" class="form-control" name="custom_description" placeholder="Description">
                                </div>
                                <span></span>
                            </div>
                        </div>
                        <div class="col-md-1 debit_div" style="display: none;">
                            <div class="form-check-inline">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input notify" name="notify" value="0">Notify
                              </label>
                            </div>
                        </div>
                        <div class="col-md-3 debit_msg_div" style="display: none;">
                            <div class="form-group">
                                <label>Message</label>
                                <div class="input-group">
                                   <input type="text" class="form-control" name="custom_message" placeholder="Description" maxlength="25">
                                </div>
                                <span class="error msg_error"></span>
                            </div>
                        </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12 calculated_div">

                    </div>
                </div>  
           </div>
           <br>
           <div id="step-3" class="tab-pane" role="tabpanel" style="height: 500px;">
              <ul class="nav nav-tabs nav-tabs-custom" role="tablist" >
              @foreach($gateways as $gateway)                              
              <li class="nav-item">
                  <a class="nav-link gateway {{ ($gateway->is_default)?'active':'' }}" data-gateway="{{ $gateway->id }}" gateway-name="{{$gateway->gateway}}">
                      <span class="d-none d-md-block">{{ $gateway->gateway }}</span><span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                  </a>
              </li>                                
              @endforeach
              <li class="nav-item cash_pay_div">
                  <a class="nav-link gateway {{ ($gateways->isEmpty()) ? 'active' : '' }}" data-toggle="tab" href="#cash" role="tab" data-gateway="Cash">
                      <span class="d-none d-md-block">Cash Payment</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                  </a>
              </li>                                                                           
              </ul>
              <div class="tab-content payform_div">
                @foreach($gateways as $gateway)   
                    @if($gateway->is_default)
                      <div class="tab-pane {{ ($gateway->is_default)?'active':'' }} p-3" id="{{ $gateway->gateway }}" role="tabpanel">
                                    <input type="hidden" name="gateway" value="{{ $gateway->gateway }}">
                                    @if(count($gateway->cards) > 0)
                                    
                                    <div class="row m-b-20 exist_card">
                                        @foreach($gateway->cards as $key =>$card)
                                        <div class="col-md-4">
                                            <div class="card-body">
                                                <label for="card_1{{$card->id}}0">
                                                <input type="radio" required name="card_list" class="radio_card_list" id="card_1{{$card->id}}0" value="{{Crypt::encrypt($card->id)}}" {{($card->is_default || $key == 0)?'checked':''}}> {{ $card->card_type }} / {{ Helper::date_format($card->card_expiry, 'M Y') }} 
                                                </label>
                                                @if($card->is_default || $key == 0)
                                                <input type="hidden" name="credit_card" class="selected_card" value="{{ Crypt::encrypt($card->id) }}">
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach                                        
                                    </div>
                                    @else
                                    <input type="hidden" name="credit_card" class="selected_card" value="new">
                                    @endif

                                    <div class="row m-b-20 {{ ($gateway->cards->isEmpty())?'d-none':'' }}">                                        
                                        <div class="col-md-12 d-flex justify-content-center">
                                            <a href="javascript:void(0);" class="btn btn-success waves-effect waves-light btn_add_new_card" data-payment="{{$gateway->gateway}}">Add New Card</a>
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
                                                    <label>First Name</label>
                                                    <input type="text" name="card_first_name" class="form-control card_details" placeholder="First Name" value="{{$user->first_name}}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Last Name</label>
                                                    <input type="text" name="card_last_name" class="form-control card_details" placeholder="Last Name" value="{{$user->last_name}}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Street Address</label>
                                                    <input type="text" name="card_street" class="form-control card_details" placeholder="Street Address" value="{{$user->userDetail->address}}">
                                                </div>
                                            </div>  
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>City</label>
                                                    <input type="text" name="card_city" class="form-control card_details" placeholder="City" value="{{$user->userDetail->city}}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>State</label>
                                                    <input type="text" name="card_state" class="form-control card_details" placeholder="State" value="{{$user->userDetail->state}}">
                                                </div>
                                            </div>                          
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Postal Code</label>
                                                    <input type="text" name="card_postcode" class="form-control card_details" placeholder="Postcode" value="{{$user->userDetail->postal_code}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($gateway->gateway == 'Stripe')
                                    <div class="col-md-12 justify-content-center card_form {{ ($gateway->cards->isEmpty()) ? '' : 'd-none' }}">
                                        <div class="row card-body">
                                            <div class="col-md-12">
                                                <h4 class="mt-0 header-title">Enter Card Details</h4>
                                            </div>
                                            <div class="col-md-6">
                                                <div id="card-element" class="field"></div>
                                                <div class="outcome">
                                                    <div class="stripe-error error"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-error" id="payment-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                      </div>
                    @endif
                @endforeach
                <div class="tab-pane p-3" id="cash" role="tabpanel">
                  <div class="row">
                      <div class="col-md-4">
                          <div class="form-group">
                              <label>Payment Method</label>
                              <div class="input-group">
                                 <select class="form-control" name="payment_method" id="payment_method" required>
                                  <option value="">Choose</option>
                                  <option value="DirectCash" selected>Direct Cash</option>
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
                  </div>
                </div>
              </div>
           </div>
        </form>
    </div>
</div>
    </div>
</div>