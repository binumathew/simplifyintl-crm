<div class="bndl_table_hlde">
	<table class="display responsive no-wrap dataTable" cellspacing="0" width="100%">
		<thead>
			<tr>				
				<th>Phone</th>
				<th>Status</th>
				<th>Action</th>										
			</tr>
		</thead>
		<tbody>
			@foreach($simList as $sim)
			<tr>				
				<td>{{$sim->stock->phone_number .'/'}}<br/>{{$sim->stock->sim_number}}</td>
				<td>
					@php
					switch($sim->reg_status){
					case 0:
					echo 'Inactive';
					$active_flag = true;
					break;
					case '1':
					echo 'Active-Callback pending';
					break;
					case '2':
					echo 'Active';
					break;
				}
				@endphp
			</td>

			<td>
				@if($sim->reg_status == 1)
				<a title="Add Credit"  href="#" class="fa fa-money" data-toggle="modal" data-target="#addCreditModal"></a>
				<a title="Mark as Welcome Call Completed"  href="#" class="fa fa-check-square mark_wel_call" data-id="{{Crypt::encrypt($sim->id) }}" data-value="{{$sim->id}}" id="ad_crdt_{{$sim->id}}"></a>
				@elseif($sim->reg_status == 2)
				<a title="Add Credit"  href="#" class="fa fa-money" data-toggle="modal" data-target="#addCreditModal"></a>
				@endif
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
</div>
