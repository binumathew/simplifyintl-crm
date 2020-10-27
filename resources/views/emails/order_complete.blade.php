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
									<p style="font-size: 18px;">
										Hello! {{ $data->user->name }},
									</p>
									<span style="font-size: 16px;">
										<p>
											We are pleased to confirm your Order ID: #{{ $data->order_id }} and the details are as follows along with tips of taking advantage of complimentary services you enjoy from {{ config('settings.app_name') }} App; 
										</p>																					
										<div align="center" class="button-container" style="padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
											<table style="border-collapse: collapse; border: 1px solid #a9a6a6; padding: 5px 10px; width: 100%;">
												<tr>
													<th style="min-width: 120px; border: 1px solid #a9a6a6; padding: 5px 10px;">Mobile No</th>
													<th style="min-width: 60px; border: 1px solid #a9a6a6; padding: 5px 10px;">Auto Recharge</th>
													<th style="min-width: 80px; border: 1px solid #a9a6a6; padding: 5px 10px;">Plan Activated</th>
													<th style="min-width: 45px; border: 1px solid #a9a6a6; padding: 5px 10px;">Next Renewal</th>
												</tr>
												@foreach($data->order as $sim) 
												<tr>
													<td style="border: 1px solid #a9a6a6; padding: 5px 10px;">{{ $sim['phone'] }}</td>
													<td style="border: 1px solid #a9a6a6; padding: 5px 10px;">
													@if($sim['auto_recharge'] > 0) 
														{{ $currency}}{{$sim['auto_recharge'] }}
													@else
														-
													@endif	
													</td>
													<td style="border: 1px solid #a9a6a6; padding: 5px 10px;">{{ $sim['plan'] }}</td>
													<td style="border: 1px solid #a9a6a6; padding: 5px 10px;">{{ $sim['next_renewal'] }}</td>
												</tr>	
												@endforeach											
											</table>
											<br><br>

										</div>

										<p style="text-align: justify;">
											The {{ config('settings.app_name') }} team are excited to welcome you to our new generation of {{ config('settings.app_name') }} and {{ config('settings.app_name') }} App services.  In order to make the best of your privileges we recommend you download {{ config('settings.app_name') }} App from Google Play and Apple  iTunes immediately after activating your SIM.  Apart from your Data Package and Unlimited local calls and SMS we suggest you enjoy our free bundle of 1000 Minutes of monthly international calls. 
										</p>
										
									</span>
								</p>
								<br />								
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
