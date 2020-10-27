<table id="breakdowntable" class="table table-condensed" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th>Payment Date</th>
			<th>Amount</th>											
			<th>Amount Paid</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i=1; ?>
		@foreach ($details as $plan)
		<tr id="{{$i}}">
			<td>{{ Carbon::parse($plan->payment_date)->format('d-m-Y') }}</td>
			<td>{{$plan->pay_amount}}</td>
			<td>{{$plan->amount_paid}}</td>													
		</tr>
		<?php 
		$i++; ?>
		@endforeach
	</tbody>
</table>