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
											Hello! {{$data->name}},
										</p>
										<span style="font-size: 16px;">
											You have successfully registered account in {{ json_decode(config('settings.company_details'))->company_website }} 
											<p>username : 
												<strong>
													@if($data->email)
													{{$data->email}} / 
													@endif
													{{$data->username}} </strong>
											</p>											
											<p>Password : <strong>{{$data->password}}</strong></p>		
											@if(isset($data->token))									
											Please click the following link to verify your email address
											<div align="center" class="button-container" style="padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
												<a href="https://{{ json_decode(config('settings.company_details'))->company_website }}/user/verify/{{$data->token}}" style="-webkit-text-size-adjust: none; text-decoration: none; display: inline-block; color: #ffffff; background-color: #e4572e; border-radius: 15px; -webkit-border-radius: 15px; -moz-border-radius: 15px; width: auto; width: auto; border-top: 1px solid #fc7318; border-right: 1px solid #fc7318; border-bottom: 1px solid #fc7318; border-left: 1px solid #fc7318; padding-top: 4px; padding-bottom: 4px; font-family: 'Lato', Tahoma, Verdana, Segoe, sans-serif; text-align: center; mso-border-alt: none; word-break: keep-all;" target="_blank">
													<span style="padding-left:40px;padding-right:40px;font-size:16px;display:inline-block;">
														<span style="font-size: 16px; font-weight: 500; line-height: 32px; padding-top: 11px;    padding-bottom: 8px;">
															<strong>Verify Your Email</strong>
														</span>
													</span>
												</a>
											</div>
											Without timely verification,you cannot fully manage your {{ config('settings.app_name') }} accounts.<br />
											If you did not create an account, no further action is required.<br /><br />
											@endif			
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