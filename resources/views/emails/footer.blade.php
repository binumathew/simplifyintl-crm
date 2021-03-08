			<div style="background-color:transparent;">
				<div class="block-grid" style="Margin: 0 auto; min-width: 320px; max-width: 650px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
					<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
						<div class="col num12" style="min-width: 320px; max-width: 650px; display: table-cell; vertical-align: top; width: 650px;">
							<div style="width:100% !important;">
								<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:20px; padding-bottom:60px; padding-right: 0px; padding-left: 0px;">
									<!-- <table cellpadding="0" cellspacing="0" class="social_icons" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top" width="100%">
										<tbody>
											<tr style="vertical-align: top;" valign="top">
												<td style="word-break: break-word; vertical-align: top; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px;" valign="top">
													<table activate="activate" align="center" alignment="alignment" cellpadding="0" cellspacing="0" class="social_table" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: undefined; mso-table-tspace: 0; mso-table-rspace: 0; mso-table-bspace: 0; mso-table-lspace: 0;" to="to" valign="top">
														<tbody>
															<tr align="center" style="vertical-align: top; display: inline-block; text-align: center;" valign="top">
																<td style="word-break: break-word; vertical-align: top; padding-bottom: 5px; padding-right: 8px; padding-left: 8px;" valign="top"><a href="https://www.facebook.com/{{ config('settings.app_name') }}" target="_blank"><img alt="Facebook" height="32" src="{{url('/')}}/images/facebook.png" style="text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: none; display: block;" title="Facebook" width="32"/></a></td>
																<td style="word-break: break-word; vertical-align: top; padding-bottom: 5px; padding-right: 8px; padding-left: 8px;" valign="top"><a href="https://twitter.com/{{ config('settings.app_name') }}" target="_blank"><img alt="Twitter" height="32" src="{{url('/')}}/images/twitter.png" style="text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: none; display: block;" title="Twitter" width="32"/></a></td>
																<td style="word-break: break-word; vertical-align: top; padding-bottom: 5px; padding-right: 8px; padding-left: 8px;" valign="top"><a href="https://www.linkedin.com/company/{{ config('settings.app_name') }}" target="_blank"><img alt="LinkedIn" height="32" src="{{url('/')}}/images/linkedin.png" style="text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: none; display: block;" title="LinkedIn" width="32"/></a></td>
																<td style="word-break: break-word; vertical-align: top; padding-bottom: 5px; padding-right: 8px; padding-left: 8px;" valign="top"><a href="https://www.youtube.com/" target="_blank"><img alt="YouTube" height="32" src="{{url('/')}}/images/youtube.png" style="text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: none; display: block;" title="YouTube" width="32"/></a></td>
																<td style="word-break: break-word; vertical-align: top; padding-bottom: 5px; padding-right: 8px; padding-left: 8px;" valign="top"><a href="https://www.pinterest.com/{{ config('settings.app_name') }}" target="_blank"><img alt="Pinterest" height="32" src="{{url('/')}}/images/pinterest.png" style="text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: none; display: block;" title="Pinterest" width="32"/></a></td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table> -->
									<div style="color:#555555;font-family:'Lato', Tahoma, Verdana, Segoe, sans-serif;line-height:150%;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
										<div style="font-size: 12px; line-height: 18px; font-family: 'Lato', Tahoma, Verdana, Segoe, sans-serif; color: #555555;">
											<p style="font-size: 14px; line-height: 21px; text-align: center; margin: 0;">{{ config('settings.app_name') }}</p>
											<p style="font-size: 14px; line-height: 21px; text-align: center; margin: 0;">
												@php $comp = json_decode(config('settings.company_details')); @endphp
            									{{ ucwords($comp->company_name).','.$comp->company_street.','.$comp->company_city.','.$comp->company_country.','.$comp->company_postcode }}
											</p>
										</div>
									</div>
									<table border="0" cellpadding="0" cellspacing="0" class="divider" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" valign="top" width="100%">
										<tbody>
											<tr style="vertical-align: top;" valign="top">
												<td class="divider_inner" style="word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px;" valign="top">
													<table align="center" border="0" cellpadding="0" cellspacing="0" class="divider_content" height="0" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 60%; border-top: 1px dotted #C4C4C4; height: 0px;" valign="top" width="60%">
														<tbody>
															<tr style="vertical-align: top;" valign="top">
																<td height="0" style="word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" valign="top">
																	<span></span>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
									<div style="color:#4F4F4F;font-family:'Lato', Tahoma, Verdana, Segoe, sans-serif;line-height:120%;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
										<div style="font-size: 12px; line-height: 14px; font-family: 'Lato', Tahoma, Verdana, Segoe, sans-serif; color: #4F4F4F;">
											<p style="font-size: 12px; line-height: 16px; text-align: center; margin: 0;">
												<span style="font-size: 14px;">
													<span href="#" rel="noopener" style="text-decoration: none; color: #2190E3;" target="_blank">
														<strong>{{ config('settings.support_email') }}</strong>
													</span> | 
													<span href="#" rel="noopener" style="text-decoration: none; color: #2190E3;" target="_blank">
														<strong>{{ $comp->company_phone }}</strong>
													</span>  
													<!-- <span href="#" rel="noopener" style="text-decoration: none; color: #2190E3;" target="_blank">
														<strong>(718) 509-3006 (USA)</strong>
													</span> | 
													<span href="#" rel="noopener" style="text-decoration: none; color: #2190E3;" target="_blank">
														<strong>61-39-0345102 (Australia)</strong>
													</span> | 
													<span href="#" rel="noopener" style="text-decoration: none; color: #2190E3;" target="_blank">
														<strong>+447599706300 (WhatsApp)</strong>
													</span>   

													<span href="#" rel="noopener" style="text-decoration: none; color: #2190E3;" target="_blank">
														<strong>
															Skype: {{ config('settings.app_name') }}-techsupport
														</strong> </span> |
													<span href="#" rel="noopener" style="text-decoration: none; color: #2190E3;" target="_blank">
														<strong> 24/7 Chat support </strong>
													</span> -->
												</span>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</td>
	  </tr>
	</tbody>
  </table>
</body>
</html>
