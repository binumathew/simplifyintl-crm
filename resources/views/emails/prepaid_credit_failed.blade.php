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
									Hello,
									</p>
									<span style="font-size: 16px;">
										<p>
                                        Esim Prepaid Credit failed for the customer.
										</p>	
                                        <p>Name : <b>{{$data->name}}</b></p>
                                        <p>Phone : <b>{{$data->phone}}</b></p>
                                        <p>Email : <b>{{$data->email}}</b></p>														
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

