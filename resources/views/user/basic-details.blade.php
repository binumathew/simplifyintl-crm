<div class="col-md-12">
    <div class="card m-b-20">
        <div class="card-body">
            <h4 class="mt-0 header-title">Edit Customer Details</h4>
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#details" role="tab">
                        <span class="d-none d-md-block">Basic Details</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                    </a>
                </li>
                @if(Helper::get_option('enable_switch_support'))
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#app_settings" role="tab">
                        <span class="d-none d-md-block">Mobile App</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-cellphone-iphone h5"></i></span>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#card-setting" role="tab">
                        <span class="d-none d-md-block">Card Settings</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                    </a>
                </li>
            </ul>
            <div class="tab-content edit-details-content">
                <div class="tab-pane active p-3" id="details" role="tabpanel">
                    <form action="#" class="form" id="user_details_form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="first_name" class="col-form-label">First Name</label>
                                    <input id="first_name" name="first_name" type="text" class="form-control" value="{{ $user->first_name }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="last_name" class="col-form-label">Last Name</label>
                                    <input id="last_name" name="last_name" type="text" class="form-control" value="{{ $user->last_name }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="business_name" class="col-form-label">Company</label>
                                    <input id="business_name" name="business_name" type="text" class="form-control" value="{{ $user->userDetail->business_name }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="email" class="col-form-label">Email</label>
                                    <input id="email" name="email" type="email" class="form-control" value="{{ $user->email }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="phone" class="col-form-label">Phone</label>
                                    <input id="phone" name="phone" type="text" class="form-control" value="{{ str_replace($user->country->dial_code, 0, $user->phone) }}" autocomplete="off" disabled>
                                </div>
                            </div>                                                                    
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="alt_phone" class="col-form-label">Alt. Phone</label>
                                    <input id="alt_phone" name="alt_phone" type="text" class="form-control" value="{{ $user->alt_phone }}" autocomplete="off">
                                </div>
                            </div>                            
                            <div class="col-md-2">
                                <div class="form-group">                                                                                 
                                    <label for="bill_house_no" class="col-form-label">House No</label>
                                    <input id="bill_house_no" name="house_number" type="text" class="form-control" value="{{ $user->userDetail->house_no }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="postal_code" class="col-form-label">Postal Code</label>
                                <div class="input-group">                                            
                                    <input id="bill_postal_code" name="postal_code" type="text" class="form-control" value="{{ $user->userDetail->postal_code }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success waves-effect waves-light find_address bill" >Find</button>
                                    </div>
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
                                    <input id="address" name="address" type="text" class="form-control" value="{{ $user->userDetail->address }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="city" class="col-form-label">City</label>
                                    <input id="city" name="city" type="text" class="form-control" value="{{ $user->userDetail->city }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="state" class="col-form-label">Country</label>
                                    <input id="state" name="state" type="text" class="form-control" value="{{ $user->country->country_name }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="m-t-10 text-muted shipping d-none"> Shipping Address</div>
                        @php $shipping = json_decode($user->userDetail->shipping_address); @endphp
                        @if($shipping)
                        <div class="row shipping d-none">
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="shipping_first_name" class="col-form-label">First Name</label>
                                    <input id="shipping_first_name" name="shipping_first_name" type="text" class="form-control" value="{{ isset($shipping->first_name)?$shipping->first_name: $user->first_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="shipping_last_name" class="col-form-label">Last Name</label>
                                    <input id="shipping_last_name" name="shipping_last_name" type="text" class="form-control" value="{{ isset($shipping->last_name)?$shipping->last_name: $user->last_name }}">
                                </div>
                            </div>                             
                            <div class="col-md-2">
                                <div class="form-group">                                                                                 
                                    <label for="ship_house_no" class="col-form-label">House No</label>
                                    <input id="ship_house_no" name="shipping_house_number" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="ship_postal_code" class="col-form-label">Postal Code</label>
                                <div class="input-group">
                                    <input id="ship_postal_code" name="ship_postal_code" type="text" class="form-control" value="{{ $shipping->postal_code }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success waves-effect waves-light find_address ship">Find</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="ship_delivery_address">
                                <label for="ship_postcode_list" class="col-form-label">Address Line</label>
                                <select  class="form-control postcode_list ship" id="ship_postcode_list"></select>
                                <div class="text-danger" id="ship_address_error"></div>  
                            </div>                              
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="shipping_street" class="col-form-label">Street</label>
                                    <input id="shipping_street" name="shipping_street" type="text" class="form-control" value="{{ $shipping->street }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="shipping_city" class="col-form-label">City</label>
                                    <input id="shipping_city" name="shipping_city" type="text" class="form-control" value="{{ $shipping->city }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="shipping_country" class="col-form-label">Country</label>
                                    <input id="shipping_country" name="shipping_country" type="text" class="form-control" value="{{ $shipping->country }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label for="referralcode" class="col-form-label">Referral Code</label>
                                    <input id="referralcode" name="referralcode" type="text" class="form-control" value="{{ $user->userDetail->referralcode }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="postal_code" class="col-form-label">Recent OTP</label>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <input type="text" class="form-control text-center" value="{{($otp)?$otp->otp:'-'}}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <input type="text" id="no_otp_try" class="form-control text-center" value="{{($otp)?$otp->no_try:'-'}}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <button type="button" id="reset_otp_limit" class="btn btn-success waves-effect waves-light">Reset Limit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label class="col-form-label">Created On</label>
                                    <input type="text" class="form-control" value="{{ Helper::date_format($user->created_at) }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                                                 
                                    <label class="col-form-label">Email Verified</label>
                                    <input type="text" class="form-control" value="{{(!is_null($user->email_verified_at))?Helper::date_format($user->email_verified_at):'-'}}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row pull-right">
                            <button type="button" class="btn btn-secondary m-r-10">Cancel</button>
                            <button type="button" id="save_user_details" class="btn btn-success waves-effect waves-light">Save</button>                            
                        </div>
                    </form>
                </div>

                @if(Helper::get_option('enable_switch_support'))
                @php $call_settings = json_decode($user->userDetail->call_settings); @endphp
                <div class="tab-pane p-3" id="app_settings" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">                                                                                 
                                <label class="col-form-label">Account ID</label>
                                <input type="text" class="form-control" value="{{ $user->userDetail->i_account }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">                                                                                 
                                <label class="col-form-label">Auth Name</label>
                                <input type="text" class="form-control" value="{{ $user->userDetail->auth_name }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">                                                                                 
                                <label class="col-form-label">Balance</label>
                                <input type="text" class="form-control" value="{{ $user->balance->balance_amount }}" disabled>
                            </div>
                        </div>          
                        <div class="col-md-6">                                                    
                            <label for="postal_code" class="col-form-label">Int. Minutes</label>
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">                                                                                 
                                        <input type="text" class="form-control" value="{{ $user->balance->balance_minutes }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-success waves-effect waves-light">Update</button>
                                    </div>
                                </div>
                            </div>
                        </div> 
                    </div>
                                        
                    <div class="card">
                        <div class="card-body">
                            <form id="call_settings">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">                                                                                 
                                            <label class="col-form-label">Access Number Support</label>
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="accessnumber_support" value="0">
                                                <input type="checkbox" class="custom-control-input" id="access_support" name="accessnumber_support" value="1" {{ ($call_settings->accessnumber_support)? 'checked':'' }}>
                                                <label class="custom-control-label" for="access_support"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="col-form-label">Callback Support</label>
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="callback_support" value="0">
                                                <input type="checkbox" class="custom-control-input" id="callback_support" name="callback_support" value="1" {{ ($call_settings->callback_support)? 'checked':'' }}>
                                                <label class="custom-control-label" for="callback_support"></label>
                                            </div>                                 
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="col-form-label">Wifi Support</label>
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="wifi_support" value="0">
                                                <input type="checkbox" class="custom-control-input" id="wifi_support" name="wifi_support" value="1" {{ ($call_settings->wifi_support)? 'checked':'' }}>
                                                <label class="custom-control-label" for="wifi_support"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="col-form-label">Conference Support</label>
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="conference_support" value="0">
                                                <input type="checkbox" class="custom-control-input" id="conference_support" name="conference_support" value="1" {{ ($call_settings->conference_support)? 'checked':'' }}>
                                                <label class="custom-control-label" for="conference_support"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="col-form-label">0870 Support</label>
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="bundle_o" value="0">
                                                <input type="checkbox" class="custom-control-input" id="bundle_o" name="bundle_o" value="1" {{ ($call_settings->bundle_o)? 'checked':'' }}>
                                                <label class="custom-control-label" for="bundle_o"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row pull-right">
                                    <button type="button" class="btn btn-success waves-effect waves-light update_call_settings">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row m-t-10">    
                        <div class="col-md-6">
                            <div class="card m-b-20">
                                <div class="card-body">
                                    <h4 class="mt-0 header-title">Trusted Numbers</h4>                                   
                                    @foreach($user->trusted as $trusted)
                                    <div class="row cus-bor-btm cus-right-box">                                        
                                        <div class="col-md-1 col-xs-1"><i class="mdi mdi-sim sim-color"></i></div>
                                        <div class="col-md-10 col-xs-10"> {{ $trusted->trusted_number }}</div>
                                        <div class="col-md-1 col-xs-1">
                                            @if(!$trusted->default_number)<i class="text-danger mdi mdi-delete mdi-24px"></i>@endif
                                        </div>
                                    </div>
                                    @endforeach                                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card m-b-20">
                                <div class="card-body">
                                    <h4 class="mt-0 header-title">Auto Recharge</h4><br />
                                    <div class="row cus-bor-btm cus-right-box">                                        
                                        <div class="col-md-3">Reference ID</div>
                                        <div class="col-md-1 text-right">Amount</div>
                                        <div class="col-md-1 text-right">Tax</div>
                                        <div class="col-md-1 text-right">Total Amount</div>
                                        <div class="col-md-2">Card Type</div>
                                        <div class="col-md-2">Card Expiry</div>
                                        <div class="col-md-1">Action</div>
                                    </div>
                                    @foreach($user->recharge as $recharge)
                                    <div class="row cus-bor-btm cus-right-box">                                        
                                        <div class="col-md-3">{{ $recharge->transaction_id }}</div>
                                        <div class="col-md-1 text-right">{{ $currency.Helper::number_format($recharge->amount) }}</div>
                                        <div class="col-md-1 text-right">{{ $currency.Helper::number_format($recharge->tax) }}</div>
                                        <div class="col-md-1 text-right">{{ $currency.Helper::number_format($recharge->total_amount) }}</div>
                                        <div class="col-md-2">{{ $recharge->card_type }}</div>
                                        <div class="col-md-2">{{ Helper::date_format($recharge->card_expiry, 'M d, Y') }}</div>
                                        <div class="col-md-1">                                            
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input auto_recharge" id="recharge_{{$recharge->id}}" data-id="{{$recharge->id}}" {{ ($recharge->status)? 'checked':'' }}>
                                                <label class="custom-control-label" for="recharge_{{$recharge->id}}"></label>
                                            </div>                                            
                                        </div>
                                    </div>
                                    @endforeach 
                                </div>
                            </div>
                        </div>                            
                    </div>  
                </div>
                @endif

                <div class="tab-pane p-3" id="card-setting" role="tabpanel">
                    <div class="col-md-6">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <h4 class="mt-0 header-title">Credit/Debit Card</h4><br />
                                <div class="row cus-bor-btm cus-right-box">                                        
                                    <div class="col-md-4">Reference ID</div>
                                    <div class="col-md-3">Card Type</div>
                                    <div class="col-md-3">Card Expiry</div>
                                    <div class="col-md-2">Action</div>
                                </div>
                                @foreach($user->cards as $card)
                                <div id="card_item_{{$card->id}}" class="row cus-bor-btm cus-right-box">                                        
                                    <div class="col-md-4 col-xs-5">{{ $card->transaction_id }}</div>
                                    <div class="col-md-3 col-xs-5">{{ $card->card_type }}</div>
                                    <div class="col-md-3 col-xs-5">{{ Helper::date_format($card->card_expiry, 'M d, Y') }}</div>
                                    <div class="col-md-2">
                                        @if(!$card->is_default)
                                            <a href="javascript:void(0);" class="delete_card_data" data-id="{{Crypt::encrypt($card->id)}}"><i class="text-danger mdi mdi-delete mdi-24px"></i></a>
                                        @endif
                                    </div>
                                </div>
                                @endforeach 
                            </div>
                        </div>                            
                    </div>                    
                </div>
            </div>            
        </div>
    </div>
</div>

<script type="text/javascript">
    
    $(document).ready(function(){
        $('.find_address').on('click', function() {
            var $this = $(this);
            var pc_type = ($this.hasClass('bill'))?'bill':'ship';
            var postal_code = $('#'+ pc_type +'_postal_code').val();
            var house_no = $('#'+ pc_type +'_house_no').val();                      
            if(postal_code.length > 3){
                $this.prop('disabled', true);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    data: {postal_code:postal_code,house_no:house_no},
                    url: base_url+'/find-address',
                    success: function(response){
                        $this.removeAttr('disabled');
                        if(response.error){
                            if (response.type == 1) {
                                $('#'+ pc_type +'_postcode_list').html(response.list);
                                $('#'+ pc_type +'_address_error').html(response.message);
                                $('#'+ pc_type +'_delivery_address').removeClass('d-none');
                            } else if (response.type == 2) {
                                $('#'+ pc_type +'_postalcode_error').html('<div class="text-danger">'+ response.message +'</div>');
                            }
                        }else{                                     
                            var category = (pc_type == 'ship')?'ship_':'';
                            $('#'+ pc_type +'_delivery_address').addClass('d-none');
                            $('#'+ pc_type +'_postalcode_error').text('');
                            var address = response.address.split(' , ');
                            $('#'+category+'address').val(address[0]);
                            $('#'+category+'city').val(address[1]);  
                            $('#'+category+'country').val(address[2]);
                            $('#'+ pc_type +'_postal_code').val(address[3]);   
                        }                                   
                    }
                });
            } else {
                $('#'+ pc_type +'_postal_code').focus();
            }
        });

        $('.postcode_list').on('change', function() {        
            var pc_type = ($(this).hasClass('ship'))?'ship_':'';
            var category = (pc_type == 'ship_')?'ship':'bill';
            var address = $(this).val().split(' , ');
            $('#'+ pc_type +'address').val(address[0]);
            $('#'+ pc_type +'city').val(address[1]);
            $('#'+ pc_type +'country').val(address[2]);
            $('#'+ pc_type +'postal_code').val(address[3]);
            $('#'+ category +'_postalcode_error').text('');
            $('#'+ category +'_delivery_address').addClass('d-none');       
        });

        $('#save_user_details').on('click', function() {
            var $this = $(this);
            if($('#user_details_form').valid()){
                $this.prop('disabled', true);
                var formData = new FormData($('#user_details_form')[0]);
                formData.append('user_id', $('#user_id').val());
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: base_url+'/update-user',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(response){
                        $this.removeAttr('disabled');
                        if(response.success){
                            alert(response.message); 
                        }else{                                     
                            alert(response.message);
                        }
                    }
                });
            }
        });

        $('.delete_card_data').on('click', function() {
            var card_id = $(this).data('id');
            $('#orderCustomLabel').text('Delete Card Details');
            $('#orderCustombody').html('<div class="form-group">Do you really want to delete this card? <div id="custom_status"> </div> </div> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="confirm_delete_card" data-id="'+ card_id +'" class="btn btn-danger pull-right">Delete</button>'); 
            $('#orderCustomModal').modal('show');                      
        });
        
        $('.update_call_settings').on('click', function() {
            var $this = $(this);
            $this.prop('disabled', true);
            var formData = new FormData($('#call_settings')[0]);
            formData.append('user_id', $('#user_id').val());
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: base_url+'/update-call-settings',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response){
                    $this.prop('disabled', false);
                    if(response.success){
                        alert(response.message); 
                    }else{                                     
                        alert(response.message);
                    }
                }
            });
        });

        $('#user_details_form').validate({
            errorClass: 'text-danger',
            highlight: function ( element, errorClass, validClass ) {
                $( element ).addClass('border border-danger').removeClass('border border-success');
            },
            unhighlight: function (element, errorClass, validClass) {
                $( element ).addClass('border border-success').removeClass('border border-danger');
            },
            rules: {
                first_name: 'required',
                email: {
                    required: true,
                    email: true
                },
                last_name: 'required',
                address: 'required',
                city: 'required',
                state: 'required'                
            },
            messages: {
                phone: "Enter valid phone number"
            }
        }); 
    });

</script>