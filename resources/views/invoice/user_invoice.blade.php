<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{isset($title)? $title:config('settings.app_name')}}</title>
<style>
@page {
    margin-bottom: 2cm;
    footer: page-footer;
}
body {
    font-family: 'roboto', sans-serif;
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
    border-bottom: 1px solid #F4F4F4;
    font-size:13px;
}

.summarytable th{
    padding: .75rem;
    vertical-align: top;
    border: 0px;
    color: #ffffff;
    background: #009ad7;
}
.summarytable th td {
    border-color:  #009ad7;
}
.summarytable tr {
    background: #FFFFFF;
    border-bottom: 1px solid #F4F4F4;
}
.table {
    margin: 0px;
}
.table thead th{
    margin: 0px;
    padding: .75rem;
    font-size:14px;
    font-weight: 600;
}
.table td{
    padding: .5rem;
    vertical-align: top;
    font-size:11px;
    line-height:3px;
}
.table tr {
    background: #FFFFFF;
}
.table th {
    border-bottom: 1px solid #F4F4F4;
}
.table tr:nth-child(even) {
    background-color: #F4F4F4;
}
.titlehead{
    background-color: #009ad7;
    color: #ffffff;
    padding: 6px;
    font-size: 12px;
    margin: 0px;
    font-weight: 600;
}
.item-title{
            float: left;
            font-weight: 600;
            width: 100%;
            font-size: 12px;
        }
        .items {
            display: block;
            float: left;
            width: 30%;
            font-size: 12px;
            font-weight: 200;
            padding: 1.5%;
        }
        .bg-blue {
            background-color: #009ad7;
        }
        .txt-white {
            color: #ffffff;
        }
        .txt-blue {
            color: #009ad7;
        }
        .txt-gray{
            color: #555555;
        }
        .bg-white {
            background-color: #ffffff;
        }
        .bb{
            border-bottom: 1px solid #E0E0E0;
        }
        .br {
            border-right: 1px solid #E0E0E0;
        }
        .bl {
            border-left: 1px solid #E0E0E0;
        }
        .mb-20{
            margin-bottom: 20px;
        }
        .customer-detail{
            float: left;
            width: 100%;
            font-size: 12px;
            margin-top: 20px;
        }
        .customer-name {
            float: left;
            width: 40%;
            font-size: 12px;
            margin-top: 20px;
        }
        .payment-status{
            float: right;
            font-size: 12px;
            font-weight: bold;
            text-align: right;
            width: 40%;
            margin-top: 20px;
        }
        .clr{
            clear: both;
        }
</style>
</head>
<body>
@php
$sym         = $user->country->currency_symbol;
@endphp
<htmlpagefooter name="page-footer">
    <hr>
    <div class="m_bottom" style="width:100%">
    @php $address = json_decode($invoiceData->details->office_address); @endphp
        <div style="width:90%;float:left;font-size:10px;">{{ ucwords($address->company_name).', '.$address->company_street.', '.$address->company_city.', '.$address->company_state.', '.$address->company_country.', '.$address->company_postcode }}</div>
        <div style="width:10%;float:left;font-size:10px;">Page {PAGENO} of {nb}</div>
    </div>
    &nbsp;
</htmlpagefooter>
<div>
    <div style="float: left; width:200px;text-align:center;">
        <img src="{{ asset('images/logo.png')}}" width="200px">
        <span style="font-size: 12px;">Keep you Connected</span>
    </div>
    <div style="float: right;width:400px;">
        <img src="{{ asset('images/geo-mobile2.png')}}" width="400px">
    </div>
</div>
<div class="item-title bg-blue">
    <div class="items txt-white">Invoice Period</div>
    <div class="items txt-white">Account Number</div>
    <div class="items txt-white" >Invoice Number</div>
</div>
<div class="item-title bg-white bb">
    <div class="items br">{{ Carbon::parse($invoiceData->invoice->date)->format('d M Y') }}</div>
    <div class="items br">{{ $invoiceData->details->account_no }}</div>
    <div class="items" > {{ $invoiceData->details->invoice_number }}</div>
</div>
<div class="customer-detail txt-gray">
    @php $billingadd = json_decode($user->userDetail->billing_address);
    @endphp
    {{ $user->first_name.' '.$user->last_name }}<br>
    {{ isset($billingadd->street) ? $billingadd->street: "" }}<br>
    {{ isset($billingadd->city) ? $billingadd->city: ""  }}<br>
    {{ isset($billingadd->postal_code) ? $billingadd->postal_code: ""  }}<br>
    {{ isset($billingadd->country) ? $billingadd->country: ""  }}<br>
</div>
<div class="clr"></div>
<div class="customer-name">
    Hello {{ $user->first_name.' '.$user->last_name }}, <br>
    Your bill total is <span class="txt-blue txt-b">{{ $sym.$invoiceData->invoice->total}}</span>
</div>
<div class="payment-status">Payment status - @php echo ($invoiceData->invoice->status == 1) ? 'Up to date' : 'Processing'; @endphp</div>
<div class="clr mb-20"></div>
<table class="summarytable" width="100%">
    <thead>
      <tr>
        <th align="left">Items<br><span style="font-size:9px;font-weight:normal;">(exc.vat/tax)</span></th>
        <th align="right">Price</th>
      </tr>
    </thead>
    <tbody>
        <tr>
            <td>Bundles & Extras ({{ $user->phone }})</td>
            <td align="right" >{{ $sym.$invoiceData->subscription->amount}}</td>
        </tr>
        <tr>
            <td>Additional Charges</td>
            <td align="right" >{{ $sym.$invoiceData->additional->amount}}</td>
        </tr>
        @if($invoiceData->invoice->credits_applied != 0)
        <tr>
            <td>Credit Applied</td>
            <td align="right" >-{{ $sym.$invoiceData->invoice->credits_applied }}</td>
        </tr>
        @endif
        <tr>
            <td align="right">Sub Total</td>
            <td align="right" >{{ $sym.$invoiceData->invoice->sub_total}}</td>
        </tr>
        <tr>
            <td align="right">VAT/Tax</td>
            <td align="right" >{{ $sym.$invoiceData->invoice->tax}}</td>
        </tr>
        <tr>
            <td align="right"><b>Total</b></td>
            <td align="right"  ><b>{{ $sym.$invoiceData->invoice->total}}</b></td>
        </tr>
    </tbody>
</table>
<div class="clr mb-20"></div>
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
@if($invoiceData->invoice->itemized)
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
        <td>{{Carbon::parse($uclist->connect_date)->format('d-M-Y')}}</td>
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
    <td>{{Carbon::parse($udlist->date)->format('d-M-Y')}}</td>
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
    <td>{{Carbon::parse($uslist->date)->format('d-M-Y')}}</td>
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
@endif
</body>
</html>