@extends('layouts.email')
@section('content')

<div style="background-color:transparent;">
	<div class="block-grid" style="Margin: 0 auto; min-width: 320px; max-width: 650px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #D6E7F0;">
		<div style="border-collapse: collapse;display: table;width: 100%;background-color:#D6E7F0;">
			<div class="col num12" style="min-width: 320px; max-width: 650px; display: table-cell; vertical-align: top; width: 650px;">
				<div style="width:100% !important;">
					<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:60px; padding-right: 25px; padding-left: 25px;">											
						<div style="color:#555555;font-family:'Lato', Tahoma, Verdana, Segoe, sans-serif;line-height:120%;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
							<div style="font-size: 14px; line-height: 21px; font-family: 'Lato', Tahoma, Verdana, Segoe, sans-serif; color: #000000;">
								<p style="text-align: center; margin: 0;">
									<p style="font-size: 18px;">										Hello!  {{ $data->order[0]->user->name }},
									</p>
									<span style="font-size: 16px;">
										<p>
											We thankyou for your order and pleased to confirm that the same is being processed. Please find here with the Order Details for your reference;
										</p>											
										<h2>Order ID : #{{ $data->order[0]->order_id}}</h2>
										@php  $product = $extra = $premium = ''; $premium_cost = 0;
										$premium_no = 0; 
				                            foreach($data->order as $item) {   
				                                foreach($item->sim_data as $sim) {        
				                                    $plan_name = $sim[0]['plan_name'];
				                                    $plan_price = $sim[0]['plan_price'];
				                                    
				                                    $product .='<tr><td style="border: 1px solid #a9a6a6; padding: 5px 10px;">Sim Purchased - Mob No:'; 
				                                    foreach($sim as $list) {
				                                        $product .= '<br><i>0'.ltrim($list['phone_number'],'44').'</i>';
				                                    }
				                                    $product .= '<br>with '. $plan_name;                                             
				                                    $product .= '</td><td style="text-align: center; border: 1px solid #a9a6a6; padding: 5px 10px;">'.count($sim).'</td><td style="text-align: center; border: 1px solid #a9a6a6; padding: 5px 10px;">'.$currency.''.number_format($plan_price, 2, '.', "").'</td></tr>'; 
				                                
				                                    foreach($sim as $list) {                                               
				                                        if($list['extra_credit'] > 0 ) {
				                                            $extra .='<tr><td style="border: 1px solid #a9a6a6; padding: 5px 10px;">Credit on Mob no  <br/><i> 0'. ltrim($list['phone_number'],'44') .'</i></td><td style="text-align: center; border: 1px solid #a9a6a6; padding: 5px 10px;"></td><td style="text-align: center; border: 1px solid #a9a6a6; padding: 5px 10px;">' .$currency.''.number_format($list['extra_credit'], 2, '.', "").'</td></tr>';
				                                        }
				                                        if($list['sim_cost'] > 0 ) {
				                                        	$premium_cost += $list['sim_cost'];
				                                        	$premium_no++;
				                                        }
				                                    }
				                                }
				                            }
				                            if($premium_cost > 0){
				                                $premium = '<tr><td style="border: 1px solid #a9a6a6; padding: 5px 10px;">Premium Numbers Charge</td><td style="text-align: center; border: 1px solid #a9a6a6; padding: 5px 10px;">'. $premium_no .'</td><td style="text-align: center; border: 1px solid #a9a6a6; padding: 5px 10px;">'.$currency .''. number_format($premium_cost, 2, '.', "") .'</td></tr>';
				                            }
				                        @endphp		
				                       																	
										<div align="center" class="button-container" style="padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
											<table style="width: 100%; border: 1px solid #a9a6a6; padding: 5px 10px; border-collapse: collapse;">
												<tr>
													<th style="height: 50px; border: 1px solid #a9a6a6; padding: 5px 10px;">Description/Item</th>
													<th style="height: 50px; width: 85px; border: 1px solid #a9a6a6; padding: 5px 10px;">Quantity</th>
													<th style="height: 50px; width: 65px; border: 1px solid #a9a6a6; padding: 5px 10px;">Price</th>
												</tr>
												<?php echo $product; ?>
			                                    <?php echo $extra; ?>
			                                    <?php echo $premium; ?>
			                                    <tr>
													<td style="border: 1px solid #a9a6a6; padding: 5px 10px;" colspan="2">Sub Total</td>
													<td style="text-align: center; border: 1px solid #a9a6a6; padding: 5px 10px;">{{$currency}}{{ number_format($data->order[0]->payment->amount, 2, '.', "") }}</td>
												</tr>
												<tr>
													<td style="border: 1px solid #a9a6a6; padding: 5px 10px;" colspan="2">VAT</td>
													<td style="text-align: center; border: 1px solid #a9a6a6; padding: 5px 10px;">{{$currency}}{{ number_format($data->order[0]->payment->tax_amount, 2, '.', "") }}</td>
												</tr>
												@if($data->order[0]->payment->discount_amount != 0)
												<tr>
													<td style="border: 1px solid #a9a6a6; padding: 5px 10px;" colspan="2">Discount</td>
													<td style="text-align: center; border: 1px solid #a9a6a6; padding: 5px 10px;">- {{$currency}}{{ number_format($data->order[0]->payment->discount_amount, 2, '.', "") }}</td>
												</tr>
												@endif
												<tr>
													<td style="border: 1px solid #a9a6a6; padding: 5px 10px;" colspan="2">Order Total</td>
													<td style="text-align: center; border: 1px solid #a9a6a6; padding: 5px 10px;"> {{$currency}}{{ number_format($data->order[0]->payment->total_amount, 2, '.', "") }}</td>
												</tr>
											</table>
											<br><br>
											@php 
												$billing = json_decode($data->order[0]->billing_address);
												$shipping = json_decode($data->order[0]->shipping_address);
											@endphp
											<table style="width: 100%; padding: 5px 10px; border-collapse: collapse;">
												<tr>
													<td style="font-size: 25px; font-weight: 900;  padding: 5px 10px;">Billing Address</td>
													<td style="font-size: 25px; font-weight: 900; padding: 5px 10px;">Shipping Address</td>
												</tr>
												<tr>
													<td style="padding: 5px 10px;">
														{{ $billing->street }}<br/>
														{{ $billing->city }}<br/>
														{{ $billing->country }}<br/>
													</td>
													<td style="padding: 5px 10px;">
														{{ $shipping->street }}<br/>
														{{ $shipping->city }}<br/>
														{{ $shipping->country }}<br/>
													</td>
												</tr>												
												<tr>
													<td style="padding: 5px 10px;">{{ $billing->postal_code }}</td>
													<td style="padding: 5px 10px;">{{ $shipping->postal_code }}</td>
												</tr>
											</table>

										</div>
										
									</span>
								</p>
								<br />
								<p>We shall confirm shortly the status of your order shortly. Look forward to serving you at the earliest.</p>
								Best regards,<br /><br />
								<!-- <b>David K</b><br/>
								Manager – Customer Services<br/> -->
								{{ config('settings.app_name') }}
							</div>
						</div>							
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection					
