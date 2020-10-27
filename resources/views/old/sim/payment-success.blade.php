@extends('layouts.home')
@section('content')
<div class="Cwrapper">
    <div class="container-fluid">
    <div class="paymentsec">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <form method="post" action="{{ url('sim-activate') }}">
                        @csrf
                        <input type="hidden" name="order_id" value="{{$order[0]->order_id}}">
                        <button type="submit" class="btn btn-success" title="Activate Sim">Activate</button>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="summarybg"> <b>Dear {{$user->first_name}} {{$user->last_name}},</b>
                        <p>Your order details are shown below for reference. </p>
                        <h1>Order ID: #{{$order[0]->order_id}}</h1>
                        <!-- <h4>Billed On: {{ date('F d, Y',strtotime($payment->created_at)) }}</h4> -->
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
                        <div id="sumtable">
                            <table width="640" border="0" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th scope="col">Item</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    <?php echo $product; ?>
                                    <?php echo $extra; ?>
                                    <?php echo $premium; ?>
                                    <tr>
                                        <td>Sub Total</td>
                                        <td></td>
                                        <td>{{$currency}}{{number_format($payment->amount, 2, '.', "")}}</td>
                                    </tr>
                                    <tr>
                                        <td>VAT</td>
                                        <td></td>
                                        <td>{{$currency}}{{number_format($payment->tax_amount, 2, '.', "")}}</td>
                                    </tr>
                                    @if($payment->discount_amount != 0)
                                    <tr>
                                        <td>Discount</td>
                                        <td></td>
                                        <td>- {{$currency}}{{number_format($payment->discount_amount, 2, '.', "")}}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td>Order Total</td>
                                        <td></td>
                                        <td><b>{{$currency}}{{number_format($payment->total_amount, 2, '.', "")}}</b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="baddress">
                                    @php $address = json_decode($order[0]->billing_address); @endphp
                                    <b>Billing Address</b>                                    
                                    <p>{{$user->first_name}} {{$user->last_name}}</p>
                                    <p>{{$address->street}}</p>
                                    <p>{{$address->city}}</p>
                                    <p>{{$address->country}}</p>
                                    <p>{{$address->postal_code}}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="baddress">
                                    @php $address = json_decode($order[0]->shipping_address); @endphp
                                    <b>Shipping Address</b>
                                    <p>{{$user->first_name}} {{$user->last_name}}</p>
                                    <p>{{$address->street}}</p>
                                    <p>{{$address->city}}</p>
                                    <p>{{$address->country}}</p>
                                    <p>{{$address->postal_code}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection