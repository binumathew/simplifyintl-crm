@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<form action="{{ url('/payment') }}" method="post" id="billing-form">
			@csrf
			<div class="row">
				<div class="col-md-6">
						<h1>Cart <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#add_cart_popup">Add</button></h1>
						<div class="ctablebg clearfix">
							<div id="cart_wrapper">
								@php $amount = 0; @endphp
								@foreach($cart as $item)
								<div class="paymentwbg cart_item_{{ $item->id}}">
									<div class="buybox cart_detail_holder">
										<div class="row">
											<div class="col-md-8 col-xs-7">
												<b>{{ $item->product->plan_name }}</b>
												<i>{{ $item->product->period }} Days</i>
												<i>{{ $item->product->in_call_limit }} International Mins</i>
											</div>
											<input type="hidden" class="product_price" name="item_price[{{$item->id}}]" value="{{ $item->product->sell_price }}">
											<div class="col-md-4 col-xs-5 text-right">
												<h3>{{ $currency }}{{ $item->product->sell_price }}</h3>
											</div>
										</div>
									</div>

									<div class="buy_number">
										<div class="row number_header">
											<div class="col-xs-3">
											</div>
											<div class="col-xs-2">
												<i>Timer</i>
											</div>
											<div class="col-xs-6">
												Add International Credit
											</div>
											<div class="col-xs-1">
											</div>
										</div>
										@foreach($item->list as $list)
										<div class="row number_holder selected_sim_{{$list->stock_id}}">
											<div class="col-xs-3 p-t-8">
												@if($item->product->provider != 'EE')
												<strong>0759xxxxxxx</strong>
												@else
												{{ '0'.ltrim($list->stock->phone_number,'44') }}
												<span class="fa fa-edit change-number" data-cart_id="{{$item->id}}"></span>
												@endif
											</div>
											<div class="col-xs-2 p-t-8">
												@php
													$expire_at = Carbon::parse($list->expire_at)->subMinutes(10);
												@endphp
												<i class="timer" data-time="{{ $expire_at }}" data-stock_id="{{ $list->stock_id }}">00m 00s</i>
											</div>
											<div class="col-xs-4">
												<div class="check-group">										<input type="checkbox" id="option-one{{$list->id}}" value="5"
													class="extra-credit credit_{{$list->id}}" data-list_id="{{$list->id}}" name="add_credit[{{$list->id}}]"  {{($list->credit == 5 )?'checked':''}} >
													<label for="option-one{{$list->id}}">{{$currency}}5</label>
													<input type="checkbox" id="option-two{{$list->id}}" class="extra-credit credit_{{$list->id}}" {{($list->credit == 10 )?'checked':''}} name="add_credit[{{$list->id}}]" value="10" data-list_id="{{$list->id}}">
													<label for="option-two{{$list->id}}">{{$currency}}10</label>
													<input type="checkbox" id="option-three{{$list->id}}" class="extra-credit credit_{{$list->id}}" {{($list->credit == 15 )?'checked':''}} name="add_credit[{{$list->id}}]" value="15" data-list_id="{{$list->id}}">
													<label for="option-three{{$list->id}}">{{$currency}}15</label>
													<input type="checkbox" id="option-four{{$list->id}}" class="extra-credit credit_{{$list->id}}" {{($list->credit == 20 )?'checked':''}} name="add_credit[{{$list->id}}]" value="20" data-list_id="{{$list->id}}">
													<label for="option-four{{$list->id}}">{{$currency}}20</label>
												</div>
											</div>
											<div class="col-xs-2 port_wrapper port_label">
												@if($list->stock->category == 'normal')
												<div class="checkbox-group">
													<label>Porting
													<input type="checkbox" class="port_request" data-list_id="{{$list->id}}" value="1" {{($list->port)?'checked':''}} name="port[{{$list->id}}]"></label>
												</div>
												<span class="port_label_left" ></span>
												@endif
											</div>
											<div class="col-xs-1 p-t-8">
												<a href="#" id="remove_sim_{{$list->id}}" class="closeround remove_selected {{($list->port)?'hidden':''}}" data-stock_id="{{$list->stock_id}}">X</a>
											</div>
											<input type="hidden" class="sim_price" name="sim_price[]" value="{{$list->stock->price}}">
										</div>

										@php $amount += $list->stock->price + $list->credit; @endphp
										@endforeach

										<div class="cart_btn_holder">
											<!-- <button type="button" class="btn btn-sm pull-left change-number"  data-cart_id="{{$item->id}}">CHANGE</button> -->
											<button type="button" class="btn btn-sm pull-right remove_item" data-cart_id="{{$item->id}}">REMOVE</button>
										</div>
									</div>
								</div>
								@php $amount += $item->amount; @endphp
								@endforeach
							</div>
							<div class="cart_amount_holder">
								<div class="row">
									<div class="col-xs-10">
										<span class="cart_amount">Sub Total :</span>
									</div>
									<div class="col-xs-2">
										<span class="cart_amount sub_total">{{$currency}}{{$amount}}</span>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-10">
										Delivery Charge :
									</div>
									<div class="col-xs-2">
										free
									</div>
								</div>
								<div class="row">
									<div class="col-xs-10">
										<span class="cart_amount">Grand Total :</span>
									</div>
									<div class="col-xs-2">
										<span class="cart_amount grand_total">{{$currency}}{{$amount}}</span>
									</div>
								</div>
							</div>
							</div>
				</div>
				@php $payment_mode = json_decode(Auth::user()->payment_mode); @endphp
				<div class="col-md-6">
					<div class="Cleftpart">
						<h1>Payment Information</h1>
						@if(in_array('paypal',$payment_mode))
						<div class="ctablebg clearfix">
						<ul class="payinfotab clearfix">
							<li class="active"><a href="#">CREDIT CARD</a></li>
							<li>
								<a href="#"><img src="{{ asset('images/paypal.png') }}" alt=""/></a>
							</li>
						</ul>
						@if($credit_cards)
						<div class="paymentwbg addresssec">
							<div class="cpad">
								<div class="row gutter5px">
									<div class="col-xs-8"><h3>Choose Credit Card</h3></div>
								</div>
							</div>

							<div class="row gutter5px">
								<div style="padding: 5px 25px;">
									@foreach($credit_cards as $cards)
										<div class="radio">
											<label><input type="radio" name="credit_card" value="{{ $cards->id }}">{{ $cards->card_type }}</label>
										</div>

									@endforeach
									<div style="font-size: 20px; font-weight:bold; text-align: center">OR</div>

									<div class="radio">
										<label><input id="new-card-radio" type="radio" name="credit_card" value="new" {{ !isset($credit_cards) ? 'checked' : '' }}>Add New Card</label>
									</div>
								</div>

							</div>

						</div>
						@endif
						<div class="paymentwbg addresssec newcreditcard" style="display: {{ isset($credit_cards) ? 'none' : 'block' }}">
							<div class="cpad">
								<div class="row gutter5px">
									<div class="col-xs-8"><h3>Card Details</h3></div>
									<div class="col-xs-4 text-right" id="change-address"><a href="#"><i>Edit Address</i></a></div>
								</div>
							</div>
							<div class="cpad noborder" id="existing-address">
								<div class="row gutter5px">
									<div class="col-md-12">
										<ul>
											<li>{{$card_address->first_name}} {{$card_address->last_name}}</li>
											<li>{{$card_address->email}}</li>
											<li>{{$card_address->street}} {{$card_address->city}}</li>
											<li>{{$card_address->postal_code}}</li>
										</ul>
									</div>
								</div>
							</div>
							<span id="edit-address" class="hidden">
								<p class="half">
									<span><input type="text" name="first_name" placeholder="First Name" value="{{$card_address->first_name }}"></span>
									<span><input type="text" name="last_name" placeholder="Last Name" value="{{$card_address->last_name}}"></span>
								</p>
								<p>
									<input type="email" name="user_email" placeholder="Email Address" value="{{ $card_address->email }}">
								</p>
								<div class="pcode clearfix">
									<span>
										<input type="text" id="bill_house_no" name="house_no" placeholder="House No" value="{{$card_address->house_no}}">
									</span>
									<span>
										<input type="text" id="bill_postal_code" name="postal_code" placeholder="Postal Code" value="{{$card_address->postal_code}}">
										<div id="bill_postalcode_error"></div>
									</span>
									<a class="faddress find_address bill">Find Address</a>
								</div>
								<p class="bill_postcode_list hidden"></p>
								<p>
									<input type="text" id="street" name="street" placeholder="Address" value="{{$card_address->street}}">
								</p>
								<p>
									<input type="text" id="city" name="city" placeholder="City" value="{{$card_address->city}}">
								</p>
								<p>
									<input type="text" id="country" name="country" placeholder="Country" value="{{$card_address->country}}">
								</p>
							</span>
						</div>
						<div class="paymentwbg cardsec newcreditcard" style="display: {{ isset($credit_cards) ? 'none' : 'block' }}">
							<div class="cpad">
								<div class="row gutter5px">
									<div class="col-md-12">
										<div class="Cno">
											<input type="text" id="card_number" name="card_number"  placeholder="Card number">
											<input type="hidden" id="card_type" name="card_type" value="Card">
										</div>
									</div>
								</div>
							</div>
							<div class="cpad">
								<div class="row gutter5px">
									<div class="col-md-4 col-xs-4">
										<select name="expiry_month" class="cardyd">
											<option value="0">Select Month</option>
											<option value="01">01/Jan</option>
											<option value="02">02/Feb</option>
											<option value="03">03/Mar</option>
											<option value="04">04/Apr</option>
											<option value="05">05/May</option>
											<option value="06">06/Jun</option>
											<option value="07">07/Jul</option>
											<option value="08">08/Aug</option>
											<option value="09">09/Sep</option>
											<option value="10">10/Oct</option>
											<option value="11">11/Nov</option>
											<option value="12">12/Dec</option>
										</select>
									</div>
									<div class="col-md-4 col-xs-4">
										<select name="expiry_year" class="cardyd">
											<option value="0">Select Year</option>
											<option value="2019">2019</option>
											<option value="2020">2020</option>
											<option value="2021">2021</option>
											<option value="2022">2022</option>
											<option value="2023">2023</option>
											<option value="2024">2024</option>
											<option value="2025">2025</option>
											<option value="2026">2026</option>
											<option value="2027">2027</option>
											<option value="2028">2028</option>
											<option value="2029">2029</option>
											<option value="2030">2030</option>
											<option value="2031">2031</option>
											<option value="2032">2032</option>
											<option value="2033">2033</option>
											<option value="2034">2034</option>
											<option value="2035">2035</option>
										</select>
									</div>
									<div class="col-md-4 col-xs-4">
										<input type="text" name="card_cvv" placeholder="CVC" minlength="3" maxlength="4">
									</div>
								</div>
								<div class="row">
									<div class="alert-status">
										@if(Session()->has('error'))
										<div class="alert alert-danger">
											{{ Session()->get('error') }}
										</div>
										@endif
									</div>
								</div>
							</div>
							<div class="cpad">
								<div class="row gutter5px">
									<div class="col-md-12">
										<div class="checkline">
											<span><input type="checkbox" name="is_default" value="1"></span>
											Make this card my default credit card
										</div>
									</div>
								</div>
							</div>
						</div>
						@endif
						<!-- <div class="paymentwbg cardsec">
							<div class="cpad">
								<div class="row gutter5px">
									<div class="col-md-12">
										<input type="text" id="referral_code" name="referral_code" maxlength="6"placeholder="Referral Code">
										<span class="validation-error msg-span" id="ref-error"></span>
										<span class="validation-success msg-span" id="ref-success"></span>
									</div>
									<div class="col-md-12">
										<button type="button" class="btn btn-sm pull-left ref-action"  data-action="apply">Apply</button>
										<button type="button" class="btn btn-sm pull-right ref-action" data-action="remove">Remove</button>
									</div>
								</div>
							</div>
						</div> -->

						@if(in_array('cash',$payment_mode))
							<div class="ctablebg clearfix">
								<ul class="payinfotab clearfix">
									<li class="active"><a href="#">CASH PAYMENT</a></li>
									<li>
										<a href="#"><i class="fa fa-money fa-2x" ></i></a>
									</li>
								</ul>
								<div class="radio">
									<label><input type="radio" name="credit_card" value="cash">Direct Payment</label>
								</div>
							</div>
						@endif

						<div class="paymentwbg cardsec promoDiv">
							<div class="row">
								<div class="col-md-12">
									<span class="" id="">Promocode - <b>{{Auth::user()->promocode}}</b> Applied</span>
								</div>
							</div>
						</div>

						@if($allowed)
						<div class="paymentwbg cardsec">
							<div class="cpad">
								<div class="row gutter5px">
									<div class="col-md-12">
										<input type="text" id="discount_code" name="discount_code" maxlength="10" placeholder="Discount Coupon">
										<span class="validation-error msg-span" id="disc-error"></span>
										<span class="validation-success msg-span" id="disc-success"></span>
									</div>
									<div class="col-md-12">
										<button type="button" class="btn btn-sm pull-left disc-action"  data-action="apply">Apply</button>
										<button type="button" class="btn btn-sm pull-right disc-action" data-action="remove">Remove</button>
									</div>
								</div>
							</div>
						</div>
						@endif
						<div class="Guaranteed">
							<div class="row">
								<div class="col-md-4">
									<b>Guaranteed</b><em>safe checkout</em>
								</div>
								<div class="col-md-8">
									<ul>
										<li>
											<img src="{{ asset('images/ASE.png') }}" class="img-responsive center-block" alt=""/>
										</li>
										<li>
											<img src="{{ asset('images/paypal_v.png') }}" class="img-responsive center-block" alt=""/>
										</li>
										<li>
											<img src="{{ asset('images/cards.png') }}" class="img-responsive center-block" alt=""/>
										</li>
										<li>
											<img src="{{ asset('images/secured.png') }}" class="img-responsive center-block" alt=""/>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="checkline">
							<span><input type="checkbox" id="terms_cond" name="terms_cond"></span>
							I agree the <a href="#" data-toggle="modal" data-target="#terms_popup">Terms and Conditions.</a>
						</div>
						<!-- if(allowed) -->
						<button class="btn btn-primary" type="button" id="self-payment-btn" data-userid="{{$user_id}}">Generate Payment Link</button>
						<span class="validation-error msg-span" id="link-error"></span>
						<span class="validation-success msg-span" id="link-success"></span>
						<!-- endif -->
						<button type="button" id="buy_now_btn" class="activatenow disabled" disabled>BUY NOW!</button>
						</div>
					</div>
				</div>
			</div>
		</form>
<input type="hidden" id="currency_symbol" name="currency" value="{{ $currency }}">
<div class="modal fade" id="terms_popup" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4>Terms & Conditions</h4>
			</div>
			<div class="nchangepop clearfix">
				<div class="tab-content clearfix">
					<div class="agtxt">
						<p>By clicking Activate, you agree to pay the amount Due Today. Your Avoo mobile service will not begin until your device ships. Orders placed after 3pm GMT will ship the following business day. Certain orders may require up to two additional business days for processing. your service will automatically renew at the provided rate found in your shopping cart of £15.98 every month. Additional taxes and surcharges may apply. Downgrade or cancel service by logging into your avoo mobile account. Avoo mobile app service currently only supports Android and Apple devices running Android 4.1+ and iOS 8.2+.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="add_cart_popup" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4>Select Your Plans & Packages</h4>
			</div>
			<div class="nchangepop clearfix">
				<div class="">
					<div class="cart_wrapper">
						<header>

							<nav>
								<div class="tab tab1 active">
									<div class="block">Packages</div>
								</div>
								<div class="tab tab2">
									<div class="block">Plans</div>
								</div>
								<div class="indicator"></div>
							</nav>
						</header>
						<main>
							<div class="tab_reel">
								<div class="tab_panel1">
									@foreach($bundles as $bundle)
									@php
									$item = serialize(['cat'=>'bundle','cat_id'=>$bundle->id,'count'=>$bundle->sim_count, 'amount' => $bundle->sell_price]); @endphp
									<div class="card card_list">
										<div class="desc">
											<div class="block">
												{{ $bundle->plan_name }}
												<div class="pull-right">{{ $currency }}{{ $bundle->sell_price }}</div>
											</div>
											<div class="dec_content">
												<button class="btn btn-primary btn-sm add_crt_btn" data-item="{{$item}}">Add to cart</button>
												<ul>
													<li>{{ $bundle->sim_count }} SIM Pack</li>
													<li>{{ $bundle->period }}Days subscription</li>
													<li>Unlimited UK mins & texts</li>
													<li>{{ $bundle->in_call_limit }} International mins(via mobile app)</li>
												</ul>
											</div>
										</div>
									</div>
									@endforeach
								</div>
								<div class="tab_panel2">
									@foreach($plans as $plan)
									@php $item = serialize(['cat'=>'plan','cat_id'=>$plan->id,'count'=>1, 'amount' => $plan->sell_price]); @endphp
									<div class="card card_list">
										<div class="desc">
											<div class="block">
												{{ $plan->plan_name }}
												<div class="pull-right">{{ $currency }}{{ $plan->sell_price }}</div>
											</div>
											<div class="dec_content">
												<button class="btn btn-primary btn-sm add_crt_btn" data-item="{{ $item }}">Add to cart</button>
												<ul>
													<li>{{ $plan->period }} Days subscription</li>
													<li>Unlimited UK mins & texts</li>
													<li>{{ $plan->in_call_limit }} International mins(via mobile app)</li>
												</ul>
											</div>
										</div>
									</div>
									@endforeach
								</div>
							</div>
						</main>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="changepop" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Select Your SIM</h4>
			</div>
			<!-- <div class="modal-body"> -->
				<div class="modal-body nchangepop clearfix">
					<ul id="stimer" class="selected_sim">
						<p class="max_selected_sim form-error"></p>
					</ul>

					<ul class="clearfix nav nav-pills">
						<li class="active"><a href="#Normal" data-toggle="tab">Standard</a></li>
						<li><a href="#Silver" data-toggle="tab">Silver</a></li>
						<li><a href="#Gold" data-toggle="tab">Gold</a></li>
						<!-- if allowed  -->
						<li><a href="#Custom" data-toggle="tab">Custom</a></li>
						<!-- endif	 -->
					</ul>
					<input type="hidden" id="cart_id" name="cart_id" value="0">
					<div class="tab-content clearfix">
						<div class="noptions tab-pane active" id="Normal">
							<ul id="normal_option">
								@foreach($normal as $no_sim)
								<label>
									<li>
										<span>
											<input type="checkbox" class="available_sim" id="available_sim_{{ $no_sim->id }}" name="available_sim[]" value="{{ $no_sim->id }}" data-phone="{{$no_sim->phone_number}}">
										</span>
										{{ $no_sim->phone_number }}
									</li>
								</label>
								@endforeach
							</ul>
							<p class="form-error">No Hidden charges ever</p>
							<a class="popup_button reload_sim" href="#" data-type="normal">Reload</a>
							<a href="#" class="popup_button" data-dismiss="modal">OK</a>
						</div>
						<div class="noptions tab-pane" id="Silver">
							@if(!$silver->isEmpty())
							<ul id="silver_option">
								@foreach($silver as $si_sim)
								<label>
									<li>
										<span>
											<input type="checkbox" class="available_sim" id="available_sim_{{ $si_sim->id }}" name="available_sim[]" value="{{ $si_sim->id }}" data-phone="{{$si_sim->phone_number}}">
										</span>
										{{ $si_sim->phone_number }}
									</li>
								</label>
								@endforeach
							</ul>
							<p class="form-error">There is a one-off charge of {{ $currency }}{{ number_format($si_sim->price, 2, '.', '') }} Inc VAT</p>
							@endif
							<a class="popup_button reload_sim" href="#" data-type="silver">Reload</a>
							<a href="#" class="popup_button" data-dismiss="modal">OK</a>
						</div>
						<div class="noptions tab-pane" id="Gold">
							@if(!$gold->isEmpty())
							<ul id="gold_option">
								@foreach($gold as $go_sim)
								<label>
									<li>
										<span>
											<input type="checkbox" class="available_sim" id="available_sim_{{ $go_sim->id }}" name="available_sim[]" value="{{ $go_sim->id }}" data-phone="{{$go_sim->phone_number}}">
										</span>
										{{ $go_sim->phone_number }}
									</li>
								</label>
								@endforeach
							</ul>
							<p class="form-error">There is a one-off charge of {{ $currency }}{{ number_format($go_sim->price, 2, '.', '') }} Inc VAT</p>
							@endif
							<a class="popup_button reload_sim" href="#" data-type="gold">Reload</a>
							<a href="#" class="popup_button" data-dismiss="modal">OK</a>
						</div>
						<!-- if(allowed) -->
						<div class="noptions tab-pane" id="Custom">
							<div class="row">
								<div class="col-md-6">
									<label>Select Dealers</label>
									<select name="dealer" id="dealer">
										@foreach($dealers as $dealer)
											<option value="{{$dealer->id}}">{{$dealer->first_name.' '.$dealer->last_name}}</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-6">
									<label>Enter Number</label>
									<input type="text" id="custom-search" name="custom_select">
								</div>
							</div>

							<ul id="custom_option">

							</ul>
							<p class="form-error">Price may change.</p>
							<a href="#" class="popup_button" data-dismiss="modal">OK</a>
						</div>
						<!-- endif -->
					</div>
				</div>
			<!-- </div> -->
		</div>
	</div>
</div>

</div>
</div>


<script src="{{ asset('js/jquery.creditCardValidator.js') }}"></script>
<script src="{{ asset('js/jquery.mask.js') }}"></script>
<script src="{{ asset('js/cart.js') }}"></script>
@endsection
