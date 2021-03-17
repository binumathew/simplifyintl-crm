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
									Dear {{ $user->name }},
									</p>
									<span style="font-size: 16px;">
										<p>
                                        @if($attempt == 1)
										Our attempt to process your subscription payment failed. We will attempt again within 24 Hrs. Should this attempt fail there will be an additional admin charge of £10 each for second and third attempt. To avoid service disruption, we request you to kindly contact our customer support ASAP.
										@elseif($attempt == 2)
										Our first and second attempt failed to successfully process your monthly subscription. As part of standard process the System terminate your account and be will with collection agency. Your current outstanding will include additional admin charges of £{{Config('general.settings.stripe_failed_fee')}} To avoid service disruption, we request you to kindly contact our customer support ASAP to remit the outstanding. We look forward to your continued association.
										@endif
										</p>															
									</span>
								</p>
								<br />								
								Best regards,<br /><br />
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

