@if(isset($getamount))
<div class="table-responsive-sm">
    <table class="table table-striped">
        <thead>
            <tr>
            <th>Item</th>
            <th>Description</th>
            <th class="right">Amount</th>
            <th class="right">Total</th>
            </tr>
        </thead>
    <tbody>
        <tr>
        <td class="left strong">{{ ucwords($request->payment_for) }}</td>
        <td class="left">{{ ucfirst($request->custom_description) }}</td>

        <td class="right">{{ $user->country->currency_symbol.$getamount->amount}}</td>
        <td class="right">{{ $user->country->currency_symbol.$getamount->total_amount}}</td>
        </tr>
    </tbody>
    </table>
</div>
<div class="col-lg-5 col-sm-6 ml-auto">
    <table class="table table-clear">
        <tbody>
        <tr>
            <td class="left">
            <strong>Subtotal</strong>
            </td>
            <td class="right">{{ $user->country->currency_symbol.$getamount->amount}}</td>
        </tr>
        <tr>
            <td class="left">
             <strong>VAT ({{$user->country->tax}}%)</strong>
            </td>
            <td class="right">{{ $user->country->currency_symbol.$getamount->tax_amount}}</td>
        </tr>
        <tr>
            <td class="left">
            <strong>Total</strong>
            </td>
            <td class="right">
            <strong>{{ $user->country->currency_symbol.$getamount->total_amount}}</strong>
            </td>
        </tr>
    </tbody>
</table>
</div>
@endif
@if(isset($gateway))
<div class="tab-pane active p-3"  role="tabpanel">  
    @if($cards->isEmpty())                    
    <input type="hidden" name="credit_card" class="selected_card" value="new">
    @endif
    <input type="hidden" name="gateway" value="{{ $gateway->gateway }}">
    <div class="row m-b-20">
        @foreach($cards as $key=>$card)
        <div class="col-md-4">
            <div class="card-body">
                <label for="card_1{{$card->id}}0">
                <input type="radio" name="card_list" class="radio_card_list" id="card_1{{$card->id}}0" value="{{Crypt::encrypt($card->id)}}" {{($card->is_default || $key == 0)?'checked':''}}> {{ $card->card_type }} / {{ Helper::date_format($card->card_expiry, 'M Y') }} 
                </label>
                @if($card->is_default || $key == 0)
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
    @if($gateway->gateway == 'Stripe')
    <div class="m-b-20 card_form {{ ($cards->isEmpty()) ? '' : 'd-none' }}">
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
    @elseif($gateway->gateway == 'Cash')
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
    @else                         
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
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.card_number').mask('0000-0000-0000-0000');

        $(document).on( 'change', '#card_number', function(){                  
        var result = $("#card_number").validateCreditCard();
        if(result.card_type !== null){
            $('#card_type').val(result.card_type.name);
        }else{
            $('#card_type').val('Card');
        }
    });
    });
</script>
@endif 