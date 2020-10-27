@php $currsymbol = Helper::get_option('currency_symbol'); @endphp
@if(isset($dealer) && !empty($dealer))
<table class="table table-hover table-bordered">
    <tbody>
      <tr><td>Name</td><td>{{ $dealer[0]->first_name }}</td></tr>
      <tr><td>Plan Type</td><td>{{ ($dealer[0]->plan_type == 1) ? "Plan" : "Bundle"  }}</td></tr>
      <tr><td>Plan Name</td><td>{{ $dealer[0]->plan_name }}</td></tr>
      <tr><td>Plan Price</td><td>{{ $currsymbol.Helper::number_format($dealer[0]->plan_price) }}</td></tr>
      <tr><td>Commission</td><td>{{ $currsymbol.Helper::number_format($dealer[0]->totalcomm) }}</td></tr>
    </tbody>
</table>
<table class="table table-hover table-bordered">
  <thead>
    <tr>
      <th scope="col">Duration</th>
      <th scope="col">Commission Type</th>
      <th scope="col">Commission Rate</th>
      <th scope="col">Status</th>
    </tr>
  </thead>
  <tbody>
  	@foreach($dealer as $dkey => $dlist)
    <tr>
      <td>{{ $dlist->name }}</td>
      <td>{{ ($dlist->comm_type == 1) ? "Fixed" : "Percentage" }}</td>
      <td>{{ ($dlist->comm_type == 1) ? $currsymbol.Helper::number_format($dlist->comm_rate) : $dlist->comm_rate.'%'  }}</td>
      <td>{{ ($dlist->status == 1) ? "Active" : "InActive" }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif
@if(isset($plan) && !empty($plan))
<table class="table table-hover table-bordered">
    <tbody>
      <tr><td>Plan Type</td><td>{{ ($plan[0]->plan_type == 1) ? "Plan" : "Bundle"  }}</td></tr>
      <tr><td>Plan Name</td><td>{{ $plan[0]->plan_name }}</td></tr>
      <tr><td>Plan Price</td><td>{{ $currsymbol.Helper::number_format($plan[0]->plan_price) }}</td></tr>
      <tr><td>Commission</td><td>{{ $currsymbol.Helper::number_format($plan[0]->totalcomm) }}</td></tr>
    </tbody>
</table>
<table class="table table-hover table-bordered">
  <thead>
    <tr>
      <th scope="col">Duration</th>
      <th scope="col">Commission Type</th>
      <th scope="col">Commission Rate</th>
      <th scope="col">Status</th>
    </tr>
  </thead>
  <tbody>
  	@foreach($plan as $dkey => $dlist)
    <tr>
      <td>{{ $dlist->name }}</td>
      <td>{{ ($dlist->comm_type == 1) ? "Fixed" : "Percentage" }}</td>
       <td>{{ ($dlist->comm_type == 1) ? $currsymbol.Helper::number_format($dlist->comm_rate) : $dlist->comm_rate.'%'  }}</td>
      <td>{{ ($dlist->status == 1) ? "Active" : "InActive" }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif
@if(isset($breakdown) && !empty($breakdown))
<table id="breakdowntable" class="table table-hover table-bordered">
  <thead>
    <tr>
      <th>Payment Date</th>
      <th>Amount</th>                     
      <th>Amount Paid</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $i=1; ?>
    @foreach ($breakdown as $plan)
    <tr id="{{$i}}">
      <td>{{ Carbon::parse($plan->payment_date)->format('d-m-Y') }}</td>
      <td>{{$currsymbol.Helper::number_format($plan->pay_amount)}}</td>
      <td>{{$currsymbol.Helper::number_format($plan->amount_paid)}}</td> 
      <td>{{($plan->user_active == 1) ? "Active" : "InActive" }}</td>                         
    </tr>
    <?php 
    $i++; ?>
    @endforeach
  </tbody>
</table>
@endif