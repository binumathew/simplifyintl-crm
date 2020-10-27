@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <!-- <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/> -->
            <!-- <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/> -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <!-- <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/new-order')}}">Order</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/select-plan')}}">Plans</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/bolt-ons')}}">Bolt-ons</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/billing')}}">Billing</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/summary')}}">Summary</a></li>
                                <li class="breadcrumb-item active">Payment</li>
                            </ol> -->
                        </div>
                        <h4 class="page-title">Payment Status</h4>
                    </div>
                </div>
            </div>
 
            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div class="row m-b-20">
                                <div class="col-md-3"></div>
                                <div class="col-md-6"><div class="alert alert-success text-center" role="alert">
                                    <h2><strong><i class="mdi mdi-check-circle"></i></strong> Payment Successful!</h2>
                                </div></div>
                                <div class="col-md-3"></div>
                            </div>
                            <div class="row m-b-20">
                                <div class="col-md-12 text-center">
                                    <p><b>Dear {{$user->first_name}} {{$user->last_name}},</b><br />
                                    Thank you! Your Payment of <b> {{$currency}}{{$payment->total_amount }}</b> has been received,<br />
                                    A Confirmation email has been sent to <b>{{$user->email}}</b></p>
                                    <p><b><h1>Order ID: #{{$order[0]->order_id}}</h1></b></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card m-b-20">
                        <div class="card-body">                           
                            <h6>Order summary</h6>
                             @php  $product = $extra = $premium = ''; $premium_cost = 0;
                                foreach($order as $item) {   
                                    foreach($item->sim_data as $sim) {        
                                        $plan_name = $sim[0]['plan_name'];
                                        $plan_price = $sim[0]['plan_price'];
                                        
                                        $product .='<tr><td>Sim Purchased with '. $plan_name; 
                                        foreach($sim as $list) {
                                            $product .= '<i>0'.ltrim($list['phone_number'],'44').'</i>';
                                        }                                            
                                        $product .= '</td><td>'.count($sim).'</td><td>'.$currency.''.number_format($plan_price, 2, '.', "").'</td></tr>'; 
                                    
                                        foreach($sim as $list) {                                               
                                            if($list['extra_credit'] > 0 ) {
                                                $extra .='<tr><td>Topup <i> 0'. ltrim($list['phone_number'],'44') .'</i></td><td></td><td>' .$currency.''.number_format($list['extra_credit'], 2, '.', "").'</td></tr>';
                                            }
                                            $premium_cost += $list['sim_cost'];
                                        }
                                    }
                                }
                                if($premium_cost > 0){
                                    $premium = '<tr><td>Premium Numbers Charge</td><td></td><td>'.$currency .''. number_format($premium_cost, 2, '.', "") .'</td></tr>';
                                }
                            @endphp
                            <table id="datatable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                <thead>
                                    <tr>                                        
                                        <th>Item</th>
                                        <th width="180">Quantity</th>
                                        <th width="100">Price</th>
                                    </tr>
                                </thead>
                               <tbody>
                                    <?php echo $product; ?>
                                    <?php echo $extra; ?>
                                    <?php echo $premium; ?>
                                    <tr>
                                        <td></td>
                                        <td>Sub Total</td>
                                        <td>{{$currency}}{{Helper::number_format($payment->amount)}}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>VAT</td>
                                        <td>{{$currency}}{{Helper::number_format($payment->tax_amount)}}</td>
                                    </tr>
                                    @if($payment->discount_amount != 0)
                                    <tr>
                                        <td></td>
                                        <td>Discount</td>
                                        <td>- {{$currency}}{{Helper::number_format($payment->discount_amount)}}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td></td>
                                        <td><b>Order Total</b></td>
                                        <td><b>{{$currency}}{{Helper::number_format($payment->total_amount)}}</b></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-md-2"></div>
                                <div class="col-md-4">
                                    @php $address = json_decode($order[0]->billing_address); @endphp
                                    <h6><b>Billing Address</b></h6>                               
                                    {{$address->first_name}} {{$address->last_name}}<br/>
                                    {{$address->street}}<br/>
                                    {{$address->city}}<br/>
                                    {{$address->country}}<br/>
                                    {{$address->postal_code}}<br/>
                                </div>
                                <div class="col-md-2"></div>
                                <div class="col-md-4 pull-right">
                                     @php $address = json_decode($order[0]->shipping_address); @endphp
                                    <h6><b>Shipping Address</b></h6>
                                    {{$address->first_name}} {{$address->last_name}}<br/>
                                    {{$address->street}}<br/>
                                    {{$address->city}}<br/>
                                    {{$address->country}}<br/>
                                    {{$address->postal_code}}<br/>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

                                
       
                    
    

            <!-- <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script> -->
            <script src="{{ asset('public/js/jquery.creditCardValidator.js') }}"></script>  
            <script src="{{ asset('public/js/jquery.mask.js') }}"></script> 

            <script type="text/javascript">
                $(document).ready(function () {
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
                                success:function(data){
                                    if(data.error){
                                    	$this.prop('disabled',false);
                                       $('#payment_status').html(data.message); 
                                    }else{
                                        // location.href = base_url+'/success';
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