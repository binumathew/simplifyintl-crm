@extends('layouts.home')
@section('content')
<style type="text/css">
    .stripe-button-el { display: none };
</style>
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
                                <li class="breadcrumb-item"><a href="{{url('/billing')}}">Billing</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/summary')}}">Summary</a></li>
                                <li class="breadcrumb-item active">Payment</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Payment</h4>
                    </div>
                </div>
            </div>
            <form id="pay-form" class="pay-form">
                <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card m-b-20">
                            <div class="card-body">
                            @if (\Session::has('error'))
                                <div class="alert alert-danger">
                                    <ul>
                                        <li>{!! \Session::get('error') !!}</li>
                                    </ul>
                                </div>
                            @endif
                                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                    @foreach($gateways as $gateway)
                                    <li class="nav-item">
                                        <a class="nav-link gateway {{ ($gateway->is_default)?'active':'' }}" data-gateway="{{ $gateway->id }}">
                                            <span class="d-none d-md-block">{{ $gateway->gateway }}</span><span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                                        </a>
                                    </li>
                                    @endforeach
                                    <!-- <li class="nav-item">
                                        <a class="nav-link gateway {{ ($gateways->isEmpty()) ? 'active' : '' }}" data-toggle="tab" href="#cash" role="tab">
                                            <span class="d-none d-md-block">Cash Payment</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                                        </a>
                                    </li> -->
                                </ul>

                                <div class="tab-content">
                                    @foreach($gateways as $gateway)
                                        @if($gateway->is_default)
                                        <div class="tab-pane {{ ($gateway->is_default)?'active':'' }} p-3" id="{{ $gateway->gateway }}" role="tabpanel">
                                            <input type="hidden" name="credit_card" class="selected_card" value="new">
                                            <input type="hidden" name="gateway" value="{{ $gateway->gateway }}">
                                            <div class="row m-b-20">
                                                @foreach($gateway->cards as $ckey => $card)
                                                <div class="col-md-4">
                                                    <div class="card-body">
                                                        <label for="card_1{{$card->id}}0">
                                                        <input type="radio" name="card_list" class="radio_card_list" id="card_1{{$card->id}}0" value="{{Crypt::encrypt($card->id)}}" {{($card->is_default || $ckey == 0)?'checked':''}}> {{ $card->card_type }} / {{ Helper::date_format($card->card_expiry, 'M Y') }}
                                                        </label>
                                                        @if($card->is_default || $ckey == 0)
                                                        <input type="hidden" name="credit_card" class="selected_card" value="{{ Crypt::encrypt($card->id) }}">
                                                        @endif
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>

                                            <div class="row m-b-20 {{ ($gateway->cards->isEmpty())?'d-none':'' }}">
                                                <!-- <div class="col-md-12 text-center"><b>OR</b></div> -->
                                                @if($gateway->gateway != 'Stripe')
                                                <div class="col-md-12 d-flex justify-content-center">
                                                    <a href="javascript:void(0);" class="btn btn-success waves-effect waves-light btn_add_new_card">Add New Card</a>
                                                </div>
                                                @endif
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
                                                            <input type="text" name="expiry_month" class="form-control card_details number" placeholder="MM" maxlength="2">

                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Expiry Year</label>
                                                            <input type="text" name="expiry_year" class="form-control card_details number" placeholder="YY" maxlength="2">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>CVV</label>
                                                            <input type="text" name="card_cvv" class="form-control card_details number" placeholder="Card secuity number" minlength="3" maxlength="4">
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
                                            @if($gateway->gateway == 'Stripe' && $purchase->total_amount != 0)
                                            <div class="col-md-12 d-flex justify-content-center">

                                                @php $amount = ($purchase->total_amount * 100);
                                                $currency    = strtolower($purchase->currency);
                                                @endphp
                                                <form action="{{url('/payment')}}" method="POST">
                                                    <input type="hidden" name="gateway" value="{{ $gateway->gateway }}">
                                                    @csrf
                                                    <script
                                                        src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                                        data-key="{{ config('services.stripe.key') }}"
                                                        data-amount="{{ $amount }}"
                                                        data-name="Order Payment"
                                                        data-description=""
                                                        data-image="{{ asset('images/logo.png') }}"
                                                        data-locale="auto"
                                                        data-currency="{{$currency}}">

                                                    </script>
                                                    <button type="submit" class="btn btn-success waves-effect waves-light stripePayBtn">Add New Card</button>
                                                </form>
                                            </div>
                                            @endif
                                            @if(($gateway->gateway != 'Stripe') || $gateway->cards->isNotEmpty() || $purchase->total_amount == 0)
                                            <div class="row">
                                                <div class="col-md-12 m-t-20">
                                                	<span id="payment_status" class="text-danger"></span>
                                                    <button type="button" class="btn btn-success waves-effect waves-light pull-right confirm_payment">Submit</button>
                                                </div>
                                            </div>
                                            @endif

                                        </div>
                                        @endif
                                    @endforeach
                                    <div class="tab-pane {{ ($gateways->isEmpty()) ? 'active' : '' }} p-3" id="cash" role="tabpanel">
                                         <div class="row m-b-20">
                                            <div class="col-md-4">
                                                <div class="card-body">
                                                    <input type="radio"> Direct Payment
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-success" role="alert">
                                    Promocode <strong>{{Auth::user()->promocode}}</strong> Applied
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <table id="datatable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td class="text-right" colspan="3">Plan</td>
                                            <td class="text-right"><b>{{ $purchase->currency_symbol.''.number_format($purchase->net_amount,2) }}</b></td>
                                        </tr>
                                        @if($purchase->credit != 0)
                                        <tr>
                                            <td class="text-right" colspan="3">Credit</td>
                                            <td class="text-right"><b>{{ $purchase->currency_symbol.''.number_format($purchase->credit,2) }}</b></td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="text-right" colspan="3">Sub Total</td>
                                            <td class="text-right"><b>{{ $purchase->currency_symbol.''.number_format($purchase->net_amount+$purchase->credit,2) }}</b></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" colspan="3">VAT</td>
                                            <td class="text-right"><b>{{ $purchase->currency_symbol.''.number_format($purchase->vat_amount,2) }}</b></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" colspan="3">Total</td>
                                            <td class="text-right"><b>{{ $purchase->currency_symbol.''.number_format($purchase->total_amount,2) }}</b></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-body right-nav">
                                <ul>
                                    <li><a href="#"><b>Step 1</b><br/>Select voice and data</a></li>
                                    <li><a href="#"><b>Step 2</b><br/>Configure voice and data</a></li>
                                    <li><a href="#"><b>Step 3</b><br />Select bolt-ons</a></li>
                                    <li><a href="#"><b>Step 4</b><br/>Provisioning information</a></li>
                                    <li><a href="#"><b>Step 5</b><br/>Bill limits</a></li>
                                    <li><a href="#"><b>Step 6</b><br/>Add Customer</a></li>
                                    <li><a href="#"><b>Step 7</b><br/>Summary</a></li>
                                    <li><a href="#" class="selected"><b>Step 8</b><br/>Payment</a></li>
                                </ul>
                            </div>
                    </div>
                    <!-- <div class="col-md-3">
                        <div class="card m-b-20">
                            <div class="card-body right-nav">
                                <ul>
                                    <li><a href="#"><b>Step 1</b><br/>Select voice and data</a></li>
                                    <li><a href="#"><b>Step 2</b><br/>Configure voice and data</a></li>
                                    <li><a href="#"><b>Step 3</b><br />Select bolt-ons</a></li>
                                    <li><a href="#"><b>Step 4</b><br/>Provisioning information</a></li>
                                    <li><a href="#"><b>Step 5</b><br/>Bill limits</a></li>
                                    <li><a href="#"><b>Step 6</b><br/>Add Customer</a></li>
                                    <li><a href="#"><b>Step 7</b><br/>Summary</a></li>
                                    <li><a href="#" class="selected"><b>Step 8</b><br/>Payment</a></li>
                                </ul>
                            </div>
                        </div>
                    </div> -->
                </div>


            <!-- <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script> -->
            <script src="{{ asset('js/jquery.creditCardValidator.js') }}"></script>
            <script src="{{ asset('js/jquery.mask.js') }}"></script>

            <script type="text/javascript">
                $(document).ready(function () {
                    window.onbeforeunload = function(e) {
                        $("#preloader,#status").show();
                    }
                    $(document).on( 'keypress', '.number', function(event){
                        if(event.charCode >= 48 && event.charCode <= 57){
                            return true;
                        }else{  return false; }
                    });
                    $(document).on('click', '.stripePayBtn', function(e){
                        $("input[name='card_list']").prop('checked',false);
                        $("input[name='credit_card']").val('');
                    });

                    $(document).on('click', '.btn_add_new_card', function(e){
                        $('.card_form').toggleClass('d-none');
                        $("input[name='card_list']").prop('checked',false);
                        if(!$('.card_form').hasClass('d-none')){
                            $("input[name='credit_card']").val('new');
                        }
                    });

                    $(document).on('click', '.gateway', function(e){
                        $('.gateway').removeClass('active');
                        $(this).addClass('active');
                        var gateway = $(this).data('gateway');
                        var user_id = $('#user_id').val();
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: base_url+'/select-gateway',
                            data: {gateway:gateway,user_id:user_id},
                            success:function(data){
                                $('.tab-content').html(data.html);
                            }
                        });
                    });


                    $('.card_number').mask('0000-0000-0000-0000');

                    $(document).on( 'change', '#card_number', function(){
				        var result = $("#card_number").validateCreditCard();
				        if(result.card_type !== null){
				            $('#card_type').val(result.card_type.name);
				        }else{
				            $('#card_type').val('Card');
				        }
				    });

                    $(document).on('click', '.confirm_payment', function(e){
                        e.preventDefault();
                        $this = $(this);
                        $this.prop('disabled',true);
                        $('#payment_status').html('')
                        if($('#pay-form').valid()){
                            var formData = new FormData($('#pay-form')[0]);
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',
                                url: base_url+'/payment',
                                data: formData,
                                cache: false,
                                contentType: false,
                                processData: false,
                                beforeSend: function(){
                                    $(".confirm_payment").html('Processing..');
                                },
                                complete: function(){
                                    $(".confirm_payment").html('Submit');
                                },
                                success:function(data){
                                    if(data.error){
                                    	$this.prop('disabled',false);
                                       $('#payment_status').html(data.message);
                                    }else{
                                        location.href = base_url+'/success';
                                    }
                                }
                            });
                        }
                    });

                    $("#pay-form").validate({
                        // errorClass: "invalid form-error",
                        // errorElement: 'div',
                        errorPlacement: function(error, element) {
                            element.addClass('border border-danger');
                            error.insertAfter(element);
                        },
                        unhighlight: function(element) {
					        $(element).removeClass('error border border-danger');
					        $(element).addClass('border border-success');
					    },
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
                            card_list: {
                                required: {
                                    depends: function () {
                                      return ($("input[name='credit_card']"). val() == 'new')?false:true;
                                    }
                                },
                            }
                        },
                        messages: {
                            card_holder:{
                                regex:"Enter a valid card holder name!",
                            },
                            card_postcode: {
                                required:"Enter your postal code!",
                                regex:"Invalid Postalcode",
                            },
                            expiry_year: "Enter card expiry year!",
                            expiry_month: "Enter card expiry month!",
                            terms_cond: 'Agree the Terms and Conditions to proceed!.',
                            card_list: 'Please choose a credit card or add a new one'
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

                    $(document).on('change', '.card_details', function(){
                        $('.selected_card').val('new');
                        $('.radio_card_list').prop('checked',false);
                    });

                    $(document).on('click', '.radio_card_list', function(){
                        var card = $(this).val();
                        $('.selected_card').val(card);
                        $('.card_form').addClass('d-none');
                        $(".confirm_payment").prop('disabled',false);
                    });

                    $(document).on('change','.ext_card',function(){
                        if($(this).val() == 'new'){
                            $('.pay_card').removeClass('hidden');
                        }else{
                            $('.pay_card').addClass('hidden');
                        }
                    });
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection
