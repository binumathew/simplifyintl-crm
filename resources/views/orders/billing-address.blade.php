<form id="user-form">
    <div class="text-muted">Billing Address</div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="first_name" class="col-form-label">First Name</label>
                <input id="first_name" name="first_name" type="text" class="form-control" autocomplete="off" value="{{ ($user)?$user->first_name:'' }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="last_name" class="col-form-label">Last Name</label>
                <input id="last_name" name="last_name" type="text" class="form-control" autocomplete="off" value="{{ ($user)?$user->last_name:'' }}">
            </div>
        </div>                                                                        
        <div class="col-md-2">
            <div class="form-group">                                                                                 
                <label for="country_id" class="col-form-label">Country</label>
                <select name="country_id" id="country_id" class="form-control custom-select identifier">
                    <option value="" data-dial_code="">Choose</option>
                    @foreach (Helper::getCountry() as $country)
                    @php $selected = '';
                    if($user && $user->country_id == $country->id){
                        $selected = 'selected'; 
                        $dial_code = $country->dial_code;
                    }
                    if(!$user && $country->id == 238){
                        $selected = 'selected'; 
                        $dial_code = '+44';
                    }                                                
                    @endphp
                    <option value="{{$country->id}}" data-dial_code="{{$country->dial_code}}" {{$selected}}> {{$country->country_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <label for="phone" class="col-form-label">Phone</label>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="dial_code">{{ $dial_code }}</span>
                </div>
                <input id="phone" name="phone" type="text" class="form-control identifier" autocomplete="off" value="{{ ($user)? str_replace($user->country->dial_code, '', $user->phone):'' }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="email" class="col-form-label">Email</label>
                <input id="email" name="email" type="email" class="form-control identifier" autocomplete="off" value="{{ ($user)?$user->email:'' }}">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">                                                                                 
                <label for="bill_house_no" class="col-form-label">House No</label>
                <input id="bill_house_no" name="house_number" type="text" class="form-control" value="{{ ($user)?$user->userDetail->house_no:'' }}">
            </div>
        </div>
        <div class="col-md-4">
            <label for="bill_postal_code" class="col-form-label">Postal Code</label>
            <div class="input-group">
                <input id="bill_postal_code" name="postal_code" type="text" class="form-control text-uppercase" value="{{ ($user)?$user->userDetail->postal_code:'' }}">
                <div class="input-group-append">
                    <button type="button" class="btn btn-success waves-effect waves-light find_address bill">Find</button>
                </div>
                <div id="bill_postalcode_error"></div>
            </div>
        </div>
        <div class="col-md-6 d-none" id="bill_delivery_address">
            <label for="bill_postcode_list" class="col-form-label">Address Line</label>
            <select  class="form-control custom-select postcode_list bill" id="bill_postcode_list"></select>
            <div class="text-danger" id="bill_address_error"></div>  
        </div>
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="address" class="col-form-label">Street</label>
                <input id="address" name="address" type="text" class="form-control" autocomplete="off" value="{{ ($user)?$user->userDetail->address:'' }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="city" class="col-form-label">City</label>
                <input id="city" name="city" type="text" class="form-control" autocomplete="off" value="{{ ($user)?$user->userDetail->city:'' }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="country" class="col-form-label">Country</label>
                <input id="country" name="country" type="text" class="form-control" autocomplete="off" value="{{ ($user)?$user->userDetail->state:'' }}">
            </div>
        </div>
    </div>
    <div class="py-3">
        <div class="custom-control custom-checkbox">
            <input type="hidden" id="shipp_default" name="shipping_address" value="0" disabled>
            <input type="checkbox" class="custom-control-input" name="shipping_address" id="shipping_address" value="1" checked>
            <label class="custom-control-label" for="shipping_address">Shipping address is same as billing address</label>
        </div>
    </div>
    <div class="text-muted shipping d-none"> Shipping Address</div>
    @php $shipping = ($user)?json_decode($user->userDetail->shipping_address):''; @endphp
    <div class="row shipping d-none">
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="shipping_first_name" class="col-form-label">First Name</label>
                <input id="shipping_first_name" name="ship_first_name" type="text" class="form-control" value="{{ isset($shipping->first_name)?$shipping->first_name: (($user)?$user->first_name:'') }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="shipping_last_name" class="col-form-label">Last Name</label>
                <input id="shipping_last_name" name="ship_last_name" type="text" class="form-control" value="{{ isset($shipping->last_name)?$shipping->last_name: (($user)?$user->last_name:'') }}">
            </div>
        </div>                             
        <div class="col-md-2">
            <div class="form-group">                                                                                 
                <label for="ship_house_no" class="col-form-label">House No</label>
                <input id="ship_house_no" name="ship_house_no" type="text" class="form-control">
            </div>
        </div>
        <div class="col-md-4">
            <label for="ship_postal_code" class="col-form-label">Postal Code</label>
            <div class="input-group">                                            
                <input id="ship_postal_code" name="ship_postal_code" type="text" class="form-control" value="{{ ($shipping)?$shipping->postal_code:'' }}">
                <div class="input-group-append">
                    <button type="button" class="btn btn-success waves-effect waves-light find_address ship">Find</button>
                </div>
                <div id="ship_postalcode_error"></div>
            </div>
        </div>
        <div class="col-md-6 d-none" id="ship_delivery_address">
            <label for="ship_postcode_list" class="col-form-label">Address Line</label>
            <select  class="form-control postcode_list ship" id="ship_postcode_list"></select>
            <div class="text-danger" id="ship_address_error"></div>  
        </div>                                   
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="ship_address" class="col-form-label">Street</label>
                <input id="ship_address" name="ship_address" type="text" class="form-control" autocomplete="off" value="{{ ($shipping)?$shipping->street:'' }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="ship_city" class="col-form-label">City</label>
                <input id="ship_city" name="ship_city" type="text" class="form-control" autocomplete="off" value="{{ ($shipping)?$shipping->city:'' }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">                                                                                 
                <label for="ship_country" class="col-form-label">Country</label>
                <input id="ship_country" name="ship_country" type="text" class="form-control" autocomplete="off" value="{{ ($shipping)?$shipping->country:'' }}">
            </div>
        </div>
    </div>
    <div>
        <a href="{{ url('/bolt-ons') }}" class="btn btn-secondary waves-effect waves-light"><strong>Back</strong></a>
        <button type="button" class="btn btn-light" id="resetBtn"><strong>Reset</strong></button>
        <a href="javascript:void(0);" class="btn btn-success waves-effect waves-light pull-right" id="manage_user"><strong>Continue</strong></a>
    </div>                               
</form>   