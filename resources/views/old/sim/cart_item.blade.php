@foreach($cart as $item)
<div class="paymentwbg cart_item_{{ $item->id}}">
	<div class="buybox cart_detail_holder">
		<div class="row">
			<div class="col-md-8 col-xs-7">
				<b>{{ $item->product->plan_name }}</b>
				<i>{{ $item->product->period }} Days</i>
				<i>{{ $item->product->in_call_limit }}  International Mins</i>
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
				<div class="check-group">												
					<input type="checkbox" id="option-one{{$list->id}}" value="5"
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
		@endforeach
		<div class="cart_btn_holder">
			<!-- <button type="button" class="btn btn-sm pull-left change-number"  data-cart_id="{{$item->id}}">CHANGE</button> -->
			<button type="button" class="btn btn-sm pull-right remove_item" data-cart_id="{{$item->id}}">REMOVE</button>
		</div>
	</div>													
</div>
<script type="text/javascript">
	$('a[href="#"]').on('click', function(e) {
	    e.preventDefault();
	});
</script>
@endforeach