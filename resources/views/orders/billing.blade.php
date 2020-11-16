@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <!-- <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/> -->
            <!-- <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/> -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/new-order')}}">Order</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/select-plan')}}">Plans</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/bolt-ons')}}">Bolt-ons</a></li>
                                <li class="breadcrumb-item active">Billing</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Customer Details</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-9">
                    <div class="card m-b-20">
                        <div class="card-body" id="user_form_area">
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
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card m-b-20">
                        <div class="card-body right-nav">
                            <ul>
                                <li><a href="#"><b>Step 1</b><br/>Select voice and data</a></li>
                                <li><a href="#"><b>Step 2</b><br/>Configure voice and data</a></li>
                                <li><a href="#"><b>Step 3</b><br />Select bolt-ons</a></li>
                                <li><a href="#"><b>Step 4</b><br/>Provisioning information</a></li>
                                <li><a href="#"><b>Step 5</b><br/>Bill limits</a></li>
                                <li><a href="#"><b>Step 6</b><br/>Add Customer</a></li>
                                <li><a href="#" class="selected"><b>Step 7</b><br/>Summary</a></li>
                                <li><a href="#"><b>Step 8</b><br/>Payment</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script> -->

            <script type="text/javascript">
                $(document).ready(function () {
                    // $(document).on('keyup,paste', '.identifier', function(e){
                    //     $('.identifier').trigger('change');
                    // });

                    $(document).on('change keyup paste', '.identifier', function(e){
                        var phone = $('#phone').val();
                        var email = $('#email').val();
                        var country = $('#country_id').val();
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: base_url+'/search-user',
                            data: {phone:phone,email:email,country:country},
                            success:function(data){
                                if (data.user_exist) {
                                    $('#user_form_area').html(data.html);
                                } else {
                                    // $('#orderCustomLabel').text('User Details');
                                    // $('#orderCustombody').html('<div class="text-danger">New User</div>');
                                    // $('#orderCustomModal').modal('show');
                                }
                            }
                        });
                    });

                    $(document).on('click', '#manage_user', function(e){
                        if($("#user-form").valid()){
                            var formData = new FormData($('#user-form')[0]);
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',
                                url: base_url+'/billing',
                                data: formData,
                                cache: false,
                                contentType: false,
                                processData: false,
                                success:function(data){
                                    if(data.error){
                                        alert(data.error.message[0]);
                                    }else{
                                        location.href = base_url+'/summary';
                                    }
                                }
                            });
                        }
                    });

                    $.validator.addMethod(
                        'regex',
                        function(value, element, regexp) {
                            var re = new RegExp(regexp);
                            return this.optional(element) || re.test(value);
                        },
                        'Please check your input.'
                    );

                    $('#user-form').validate({
                        errorClass: 'text-danger',
                        errorElement: 'div',
                        errorPlacement: function(error, element) {
                            error.appendTo(element.parent().parent());
                           // error.addClass('error-box');
                        },
                        rules: {
                            first_name:'required',
                            last_name:'required',
                            email: {
                                required: true,
                                email: true
                            },
                            phone: {
                                number: true,
                                required: true,
                                //regex:/^(((07|02|01))[0-9]{9})$/,
                            },
                            postal_code: {
                                required:true,
                                // regex:/^([A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}|GIR ?0A{2})$/,
                                regex:/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9][A-Za-z]?))))\s?[0-9][A-Za-z]{2})$/
                            },
                            address: 'required',
                            city: 'required',
                            country: 'required',
                            ship_first_name: {
                                required: {
                                    depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
                                }
                            },
                            ship_last_name: {
                                required: {
                                    depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
                                }
                            },
                            ship_postal_code: {
                                required: {
                                    depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
                                }
                            },
                            ship_address: {
                                required: {
                                    depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
                                }
                            },
                            ship_city: {
                                required: {
                                    depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
                                }
                            },
                            ship_country: {
                                required: {
                                    depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
                                }
                            },
                        },
                        messages: {
                            first_name: "Enter your first name!",
                            last_name: "Enter your last name!",
                            phone: {
                                required:"Enter valid phone number!",
                                //regex:"Enter a valid phone number leading with 0",
                            },
                            postal_code: {
                                required:"Enter your postal code!",
                                regex:"Invalid Postalcode",
                            }
                        }
                    });

                    $(document).on('click', '#shipping_address', function(e){
                        $('.shipping').toggleClass('d-none');
                        $('#shipp_default').prop('disabled', function(i, v) { return !v; });
                    });

                    $(document).on('change', '#country_id', function(e){
                        var dial_code = $('#country_id option:selected').data('dial_code');
                        $('#dial_code').text(dial_code);
                    });

                    $(document).on('click', '#resetBtn', function(e){
                        $('#user-form input').val('');
                    });

                    $(document).on('click', '.find_address', function(e){
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
                                    $this.removeAttr('disabled', false);
                                    if(response.error){
                                        if (response.type == 1) {
                                            console.log(pc_type);
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

                    $(document).on('change','.postcode_list', function() {
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
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection
