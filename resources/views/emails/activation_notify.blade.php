@extends('layouts.email')
@section('content')
<style>
.custom_table {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

.custom_table td, .custom_table th {
  border: 1px solid #ddd;
  padding: 8px;
}

.custom_table tr:nth-child(even){background-color: #f2f2f2;}

.custom_table tr:hover {background-color: #ddd;}

.custom_table th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #b0a9a9;
  color: white;
}
</style>
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
										<p style="font-size: 16px; text-align: justify;">
											Please find the activation completed user details on {{ $data->date }}<br/> <br/>
														
										</p>
                                        <table class="custom_table">
                                            <thead>
                                                <tr>
                                                <th scope="col">Sl.No</th>
                                                <th scope="col">Order</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Number</th>
                                                </tr>
                                            </thead>
                                        <tbody>
                                        @foreach($data->userlist as $key => $list)
                                            <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $list->order_id }}</td>
                                            <td>{{ $list->name }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        </table>
									</p>
                                    
									Best Regards,<br />
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