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
											Dear {{$data->name}},
										</p>
										<span style="font-size: 16px;">
											We thank you for your payment.
											<p>
												@if(isset($data->error))
													We hereby confirm that we have received {{ $data->amount }} and failed in update the amount to your {{ config('settings.app_name') }} account. Please contact our customer support.
												@else
													We hereby confirm that we have received {{ $data->amount }} and updated the amount to your {{ config('settings.app_name') }} account. Now your account is ready to make calls.
												@endif
												<br/><br/>
												@if(isset($data->note))
													{{$data->note}}
												@endif
											</p>											
											<p><strong>Your payment details</strong></p>												
											Paid Amount: {{ $data->amount }}<br/>
											Transaction ID: {{ $data->transaction_id }}
											<div align="center" class="button-container" style="padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
												<a href="{{url('/login')}}" style="-webkit-text-size-adjust: none; text-decoration: none; display: inline-block; color: #ffffff; background-color: #e4572e; border-radius: 15px; -webkit-border-radius: 15px; -moz-border-radius: 15px; width: auto; width: auto; border-top: 1px solid #fc7318; border-right: 1px solid #fc7318; border-bottom: 1px solid #fc7318; border-left: 1px solid #fc7318; padding-top: 4px; padding-bottom: 4px; font-family: 'Lato', Tahoma, Verdana, Segoe, sans-serif; text-align: center; mso-border-alt: none; word-break: keep-all;" target="_blank">
													<span style="padding-left:40px;padding-right:40px;font-size:16px;display:inline-block;">
														<span style="font-size: 16px; font-weight: 500; line-height: 32px; padding-top: 11px;    padding-bottom: 8px;">
															<strong>Login</strong>
														</span>
													</span>
												</a>
											</div>
											*(<span style="font-weight:bold;">In your bank statement, it will be mentioned as your payment has been received by</span> "Gencom").<br/>
											<p>If you need any further information regarding the above payment, please feel free to contact us.</p>					
										</span>
									</p>
									Best Regards,<br />
									{{ json_decode(config('settings.company_details'))->company_website }}
								</div>
							</div>							
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	@endsection					
