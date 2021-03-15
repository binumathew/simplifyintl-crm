<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{isset($title)? $title:config('settings.app_name')}}</title>
<style>
@page {
    margin-top: 3cm;
    margin-bottom: 2cm;
	header: page-header;
	footer: page-footer;
}
body {
	font-family: 'Arial', sans-serif;
}
.cls_001{
    font-size:12px;
    font-weight:normal;
}
.c_align{
    text-align:center;
}
td img{
    display: block;
    margin-left: auto;
    margin-right: auto;

}
.f_right{
    float:right;
}
.f_left{
    float:left;
}
.summarytable td{
    padding: .75rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6;
    font-size:13px;
}
.table thead th{
    padding: .75rem;
    font-size:14px;
}
.table td{
    padding: .5rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6;
    font-size:11px;
    line-height:2px;
}
.titlehead{
    background: #f0f0f0;
    padding: 6px;
    border: 1px solid #d1d1d1;
}
</style>
</head>
<body>
@php
$sym         = $user->country->currency_symbol;
@endphp
<htmlpageheader name="page-header">
<table width="100%">
    <tr>
    <td align="left"><h5> Invoice Period <br><span class="cls_001"> {{ Carbon::parse($invoiceData->invoice->date)->format('d M Y') }} </span></br></h5> </td></td>
    <td><h5> Your account Number <br><span class="cls_001"> {{ $invoiceData->details->account_no }} </span></br></h5> </td></td>
    <td><h5> Your Invoice Number <br><span class="cls_001"> {{ $invoiceData->details->invoice_number }} </span></br></h5> </td></td>
    <td align="right">
        <div class="" style="width:100%">
            <img alt="" src="{{ asset('images/logo.png') }}" width="60" style="margin-right:2%;"/>
            <h5 class="" style="float:right">{{config('settings.app_name')}}</h5>
        </div>
    </td>
  </tr>
</table>
<hr>
</htmlpageheader>
<htmlpagefooter name="page-footer">
    <hr>
    <div class="m_bottom" style="width:100%">
    @php $address = json_decode($invoiceData->details->office_address); @endphp
        <div style="width:90%;float:left;font-size:10px;">{{ ucwords($address->company_name).', '.$address->company_street.', '.$address->company_city.', '.$address->company_state.', '.$address->company_country.', '.$address->company_postcode }}</div>
        <div style="width:10%;float:left;font-size:10px;">Page {PAGENO} of {nb}</div>
    </div>
    &nbsp;
</htmlpagefooter>
<br><br><br><br><br>
<table width="100%">
  <tr>
        <td align="left" width="40%">
            <p>
            @php $billingadd = json_decode($user->userDetail->billing_address);
            @endphp
            {{ $user->first_name.' '.$user->last_name }}<br>
            {{ isset($billingadd->street) ? $billingadd->street: "" }}<br>
            {{ isset($billingadd->city) ? $billingadd->city: ""  }}<br>
            {{ isset($billingadd->postal_code) ? $billingadd->postal_code: ""  }}<br>
            {{ isset($billingadd->country) ? $billingadd->country: ""  }}<br>
            </p>
        </td>
    </tr>
</table>
<br><br><br><br><br><br><br><br>
<div style="left:31.85px;font-size:18px;"><span >Hello {{ $user->first_name.' '.$user->last_name }},</span></div>
<div style="left:31.85px;"><span>Your bill total is </span><span> <b>{{ $sym.$invoiceData->invoice->total}}</b></span></div>
<div style="left:500.47px;position:absolute;margin-top:-40px;"><span><b>Payment status - @php echo ($invoiceData->invoice->status == 1) ? 'Uptodate' : 'Processing'; @endphp </b></span></div>
<br><br><br>
<table class="summarytable" width="100%">
    <thead>
      <tr>
        <th></th>
        <th align="left">Bundles & Extras <br><span style="font-size:9px;font-weight:normal;">(exc.vat/tax)</span></th>
        <th align="left">Additional Charges <br><span style="font-size:9px;font-weight:normal;">(exc.vat/tax)</span></th>
        <th align="left">Total</th>
      </tr>
    </thead>
    <tbody>
        <tr>
            <td>({{ $user->phone }})</td>
            <td>{{ $sym.$invoiceData->subscription->amount}}</td>
            <td>{{ $sym.$invoiceData->additional->amount}}</td>
            <td>{{ $sym.$invoiceData->amounttotal }}</td>
        </tr>
        @if($invoiceData->invoice->credits_applied != 0)
        <tr>
            <td colspan="3">Credit Applied</td>
            <td>-{{ $sym.$invoiceData->invoice->credits_applied }}</td>
        </tr>
        @endif
        <tr>
            <td colspan="2"></td>
            <td><b>Sub Total</b></td>
            <td><b>{{ $sym.$invoiceData->invoice->sub_total}}</b></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td><b>VAT/Tax</b></td>
            <td><b>{{ $sym.$invoiceData->invoice->tax}}</b></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td><b>Total</b></td>
            <td><b>{{ $sym.$invoiceData->invoice->total}}</b></td>
        </tr>
        @if($invoiceData->alreadytaken->isNotEmpty())
        @foreach($invoiceData->alreadytaken as $key => $altaken)
        <tr>
            <td colspan="3" style="font-size:16px;"><b>Payment received on joining - {{ Carbon::parse($altaken->created_at)->format('d-m-Y')}}</b></td>
            <td><b> -{{ $sym.Helper::number_format($altaken->total_amount)}}</b></td>
        </tr>
        @endforeach
        @endif
    </tbody>
</table>
@if($invoiceData->subscription->all->isNotEmpty())
<div style="page-break-after:always;"></div>
  <h4 class="titlehead">Bundles & Extras</h4>
  <table class="table" width="100%">
    <thead>
      <tr>
        <th align="left">CLI</th>
        <th align="left">Description</th>
        <th align="left">Period</th>
        <!-- <th align="left">Frequency</th> -->
        <th align="left">Unit Cost <br><span style="font-size:9px;font-weight:normal;">(exc.vat/tax)</span></th>
        <th align="left">Billing Cost <br><span style="font-size:9px;font-weight:normal;">(inc.vat/tax)</span></th>
      </tr>
    </thead>
    <tbody>
    @foreach($invoiceData->subscription->all as $skey => $slist)
        <tr>
            <td>{{ $user->phone }}</td>
            <td>{{ $slist->plan->plan_name }}</td>
            <td>{{ $slist->details->description }}</td>
            <td>{{ $sym.$slist->price->amount }}</td>
            <td>{{ $sym.$slist->total }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif
@if($invoiceData->additional->all->isNotEmpty())
<h4 class="titlehead">Additional Charges</h4>
  <table class="table" width="100%">
    <thead>
      <tr>
        <th align="left">CLI</th>
        <th align="left">Name</th>
        <th align="left">Cost <br><span style="font-size:9px;font-weight:normal;">(exc.vat/tax)</span></th>
      </tr>
    </thead>
    <tbody>
    @foreach($invoiceData->additional->all as $skey => $slist)
        <tr>
            <td>{{ $user->phone }}</td>
            <td>{{ $slist->details->description }}</td>
            <td>{{ $sym.$slist->amount }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif
@if($invoiceData->credit->isNotEmpty())
<h4 class="titlehead">Credit Details</h4>
  <table class="table" width="100%">
    <thead>
      <tr>
        <th align="left">CLI</th>
        <th align="left">Name</th>
        <th align="left">Amount</th>
      </tr>
    </thead>
    <tbody>
    @foreach($invoiceData->credit as $skey => $slist)
        <tr>
            <td>{{ $user->phone }}</td>
            <td>{{ $slist->comments }}</td>
            <td>{{ $sym.$slist->sub_total }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif
@if($invoiceData->calls->isNotEmpty())
    <h4 class="titlehead">Calls</h4>
    <table class="table" width="100%" border="0">
    <thead>
        <tr>
            <th align="left">Date</th>
            <th align="left">Time</th>
            <th align="left">From</th>
            <th align="left">Phone Number</th>
            <th align="left">Destination</th>
            <th align="left">Duration</th>
            <th align="left">Cost <br><span style="font-size:9px;font-weight:normal;">(exc.vat/tax)</span></th>
        </tr>
        </thead>
    <tbody>
        @php $totalcallcost = 0; $totalcall = 0; @endphp
        @foreach($invoiceData->calls as $uckey => $uclist)
        @php
        $callcost = ($uclist->cost != 0) ? $uclist->cost : 0;
        $totalcallcost = $totalcallcost + $callcost;
        $totalcall     = $totalcall + $uclist->duration;
        @endphp
        <tr>
        <td>{{Carbon::parse($uclist->connect_date)->format('d-M')}}</td>
        <td>{{Carbon::parse($uclist->connect_date)->format('H:i:s')}}</td>
        <td>{{ ($uclist->history_from == 1) ? 'Mobile app' : 'Sim' }}</td>
        <td>{{ '+'.str_replace("+","",$uclist->cli)}}</td>
        <td>{{ (strlen(trim($uclist->cld)) <= 6) ? trim($uclist->cld): '+'.str_replace("+","",$uclist->cld)}}</td>
        <td>{{ Helper::secondsToTime($uclist->duration) }}</td>
        <td>{{ $sym.Helper::number_format($callcost) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5"><b>Total</b></td>
            <td>{{ Helper::secondsToTime($totalcall) }}</td>
            <td>{{$sym.Helper::number_format($totalcallcost)}}</td>
        </tr>
    </tbody>
    </table>
@endif
@if($invoiceData->data->isNotEmpty())
<h4 class="titlehead">Data</h4>
<table class="table" width="100%" border="0">
<thead>
    <tr>
        <th align="left">Phone Number</th>
        <th align="left">Date</th>
        <th align="left">Volume(GB)</th>
        <th align="left">Cost <br><span style="font-size:9px;font-weight:normal;">(exc.vat/tax)</span></th>
    </tr>
    </thead>
<tbody>
    @php $totaldatacost = 0; $totaldata = 0; @endphp
    @foreach($invoiceData->data as $udkey => $udlist)
    @php
    $amount   = (float)$udlist->amount;
    $datacost = ($amount != 0) ? $amount : 0;
    $data     = ((float)$udlist->duration != 0) ? Helper::bytesToGB((float)$udlist->duration):0;
    $totaldatacost = $totaldatacost + $datacost;
    $totaldata      = $totaldata + $data;
    @endphp
    <tr>
    <td>{{ '+'.str_replace("+","",$udlist->from_number)}} </td>
    <td>{{Carbon::parse($udlist->date)->format('d-M')}}</td>
    <td>{{ $data }}</td>
    <td>{{ $sym.Helper::number_format($datacost) }}</td>
    </tr>
    @endforeach
    <tr>
        <td colspan="2"><b>Total</b></td>
        <td>{{ $totaldata }}</td>
        <td>{{$sym.Helper::number_format($totaldatacost)}}</td>
    </tr>
</tbody>
</table>
@endif
@if($invoiceData->sms->isNotEmpty())
<h4 class="titlehead">SMS</h4>
<table class="table" width="100%" border="0">
<thead>
    <tr>
        <th align="left">Date</th>
        <th align="left">Phone Number</th>
        <th align="left">Destination</th>
        <th align="left">SMS</th>
        <th align="left">Cost <br><span style="font-size:9px;font-weight:normal;">(exc.vat/tax)</span></th>
    </tr>
    </thead>
<tbody>
    @php $totalsmscost = 0; $smscount = 0;@endphp
    @foreach($invoiceData->sms as $uskey => $uslist)
    @php
    $amount   = (float)$uslist->amount;
    $smscost = ($amount != 0) ? $amount : 0;
    $totalsmscost = $totalsmscost + $smscost;
    $smscount = $smscount + $uslist->duration;
    @endphp
    <tr>
    <td>{{Carbon::parse($uslist->date)->format('d-M')}}</td>
    <td>{{ '+'.str_replace("+","",$uslist->from_number)}} </td>
    <td>{{ (strlen(trim($uslist->to_number)) <=6) ? $uslist->to_number : '0'.$uslist->to_number}} </td>
    <td>{{ $uslist->duration }} </td>
    <td>{{ $sym.Helper::number_format($smscost) }}</td>
    </tr>
    @endforeach
    <tr>
        <td colspan="3"><b>Total</b></td>
        <td>{{ $smscount}} </td>
        <td>{{$sym.Helper::number_format($totalsmscost)}}</td>
    </tr>
</tbody>
</table>
@endif
</body>
</html>