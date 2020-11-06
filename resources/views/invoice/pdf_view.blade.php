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
$sym         = $arraydata['currency_symbol']; 
$accountno   = $arraydata['account_no'];
@endphp
<htmlpageheader name="page-header">
<table width="100%">
    <tr>
    <td align="left"> {{ Carbon::now()->format('d M Y') }}</td>
    <td><h5> Your account Number <br><span class="cls_001"> {{ $accountno }} </span></br></h5> </td></td>
    <td><h5> Your Invoice Number <br><span class="cls_001"> {{ $arraydata['invoicedata']['invoice_number'] }} </span></br></h5> </td></td>
    <td align="right">
        <div class="" style="width:100%"> 
            <img alt="" src="{{ asset('public/images/logo.png') }}" width="60" style="margin-right:2%;"/>
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
    @php $address = json_decode($arraydata['office_address']); @endphp
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
            @php $billingadd = json_decode($arraydata['billing_address']);
            @endphp
            {{$arraydata['name']}}<br>
            {{ isset($billingadd->street) ? $billingadd->street: "" }}<br>
            {{ isset($billingadd->city) ? $billingadd->city: ""  }}<br>
            {{ isset($billingadd->postal_code) ? $billingadd->postal_code: ""  }}<br>
            {{ isset($billingadd->country) ? $billingadd->country: ""  }}<br>
            </p>
        </td>
    </tr>
</table>
<br><br><br><br><br><br><br><br>
<div style="left:31.85px;font-size:18px;"><span >Hello {{ $arraydata['name'] }},</span></div>
<div style="left:31.85px;"><span>Your bill total is </span><span> <b>{{ $sym.$arraydata['invoicedata']['total_amount']}}</b></span></div>
<br><br><br>

<table class="summarytable" width="100%">
    <thead>
      <tr>
        <th></th>
        <th align="left">Bundles & Extras <br><span style="font-size:9px;font-weight:normal;">(inc.vat/tax)</span></th>
        <th align="left">Additional Charges <br><span style="font-size:9px;font-weight:normal;">(inc.vat/tax)</span></th>
        <th align="left">Total</th>
      </tr>
    </thead>
    <tbody>
    @if($arraydata['parent'])
    @foreach($arraydata['parent'] as $pkey => $plist)
    <tr>
        <td>({{ $plist->msisdn }})</td>
        <td>{{ $sym }}{{ isset($plist->plan) ? $plist->plantotal : (float)0.00 }}</td>
        <td>{{ $sym }}{{ isset($plist->plan) ? $plist->additionaltotal : (float)0.00 }}
        @if(isset($plist->call_cost) &&  $plist->call_cost != 0)
        <br><span>Calls {{ $sym.$plist->call_cost }}</span>
        @endif
        @if(isset($plist->data_cost) &&  $plist->data_cost != 0)
        <br><span>Data {{ $sym.$plist->data_cost }}</span>
        @endif
        @if(isset($plist->sms_cost) &&  $plist->sms_cost != 0)
        <br><span>SMS {{ $sym.$plist->sms_cost }}</span>
        @endif
        </td>
        <td>{{$sym}}{{ isset($plist->plan) ? $plist->planplusaddtotalamount : (float)0.00}}<br><span>{{$sym}}{{ isset($plist->plan) ? $plist->planplusaddamount : (float)0.00 }} (exc.vat/tax)</span></td>
    </tr>
    @endforeach
    @endif
    @if(isset($arraydata['child']))
    @foreach($arraydata['child'] as $ckey => $clist)
    <tr>
        <td>({{ $clist->msisdn }})</td>
        <td>{{ $sym }}{{ isset($clist->plan) ? $clist->plantotal : (float)0.00 }}</td>
        <td>{{ $sym }}{{ isset($clist->plan) ? $clist->additionaltotal : (float)0.00 }}
        @if(isset($clist->call_cost) &&  $clist->call_cost != 0)
        <br><span>Calls {{ $sym.$clist->call_cost }}</span>
        @endif
        @if(isset($clist->data_cost) &&  $clist->data_cost != 0)
        <br><span>Data {{ $sym.$clist->data_cost }}</span>
        @endif
        @if(isset($clist->sms_cost) &&  $clist->sms_cost != 0)
        <br><span>SMS {{ $sym.$clist->sms_cost }}</span>
        @endif
        </td>
        <td>{{$sym}}{{ isset($clist->plan) ? $clist->planplusaddtotalamount: (float)0.00 }}<br><span>{{$sym}}{{ isset($clist->plan) ? $clist->planplusaddamount : (float)0.00 }} (exc.vat/tax)</span></td>
    </tr>
    @endforeach
    @endif
    <tr>
        <td><b>Your bill total</b></td>
        <td><b>{{ $sym.$arraydata['bundletotal']}}</b></td>
        <td><b>{{ $sym.$arraydata['addchargetotal']}}</b></td>
        <td><b>{{ $sym.$arraydata['invoicedata']['total_amount']}}</b><br><span>{{ $sym.$arraydata['invoicedata']['amount']}} (exc.vat/tax)</span><br><span>{{ $sym.$arraydata['invoicedata']['tax']}} (vat/tax total)</span></td>
    </tr> 
    </tbody>
  </table>
@php $breakdowns = (isset($arraydata['child'])) ? array_merge($arraydata['parent'],$arraydata['child']) : $arraydata['parent']; @endphp
@if(!empty($breakdowns))
@if(isset($breakdowns[0]->plan))
<div style="page-break-after:always;"></div>
@endif
@foreach($breakdowns as $bkey => $blist)
@if(!empty($blist->plan) || !empty($blist->buycredit))
    <p class="titlehead"> Bill breakdown for <b>({{ $blist->msisdn }})</b></p>
@endif
    @if(!empty($blist->plan))
    <h4 class="titlehead">Bundles & Extras</h4>
        <table class="table" width="100%" border="0">
        <thead>
            <tr>
                <th align="left">Plan Name</th>
                <th align="left">Qantity</th>
                <th align="left">Amount</th>
                <th align="left">Vat/Tax</th>
                <th align="left">Total Amount</th>
            </tr>
            </thead>
        <tbody>
            @foreach($blist->plan as $pkey => $plist)
            <tr>
                <td colspan="1">&nbsp;</td>
                <td colspan="5">From {{ Carbon::parse($plist->created_at)->format('d M') }} to  {{ Carbon::parse($plist->created_at)->endofMonth()->format('d M Y') }}</td>
            </tr>
            <tr>
            <td>{{ $plist->plan}}</td>
            <td>1</td>
            <td>{{$sym.$plist->amount}}</td>
            <td>{{$sym.$plist->tax_amount}}</td>
            <td>{{$sym.$plist->total_amount}}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="4"><b>Total</b></td>
                <td>{{$sym.$blist->plantotal}}</td>
            </tr>
        </tbody>
        </table>
    @endif
    @if(!empty($blist->buycredit))
        <h4 class="titlehead">Credit Details</h4>
        <table class="table" width="100%" border="0">
        <thead>
            <tr>
                <th align="left">Qantity</th>
                <th align="left">Amount</th>
                <th align="left">Vat/Tax</th>
                <th align="left">Total Amount</th>
            </tr>
            </thead>
        <tbody>
            @foreach($blist->buycredit as $bckey => $bclist)
            <tr>
                <td>1</td>
                <td>{{$sym.$bclist->amount}}</td>
                <td>{{$sym.$bclist->tax_amount}}</td>
                <td>{{$sym.$bclist->total_amount}}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3"><b>Total</b></td>
                <td>{{$sym.$blist->credittotal}}</td>
            </tr>
        </tbody>
        </table>
    @endif
    @if(isset($blist->plan))
    <h4 class="titlehead">Additional Charges</h4>
    <table class="table" width="100%" border="0">
    <thead>
        <tr>
            <th align="left">Name</th>
            <th align="left">Cost</th>
        </tr>
        </thead>
    <tbody>
        @if(isset($blist->call_cost) && $blist->call_cost != 0)
        <tr>
            <td>Calls </td>
            <td>{{$sym.$blist->call_cost}}</td>
        </tr>
        @endif
        @if(isset($blist->data_cost) && $blist->data_cost != 0)
        <tr>
            <td>Data </td>
            <td>{{$sym.$blist->data_cost}}</td>
        </tr>
        @endif
        @if(isset($blist->sms_cost) && $blist->sms_cost != 0)
        <tr>
            <td>SMS </td>
            <td>{{$sym.$blist->sms_cost}}</td>
        </tr>
        @endif
        <tr>
            <td><b>Total</b></td>
            <td>{{$sym}}{{ isset($blist->plan) ? $blist->additionaltotal : (float)0.00 }}</td>
        </tr>
    </tbody>
    </table>
    @endif
@endforeach
@if(isset($breakdowns[0]->usercalls) || isset($breakdowns[0]->userdata) || isset($breakdowns[0]->usersms))
<div style="page-break-after:always;"></div>
@endif
@foreach($breakdowns as $bkey => $blist)
@if(!empty($blist->usercalls) || !empty($blist->userdata) || !empty($blist->usersms))
    <p class="titlehead"> Itemised usage <b>({{ $blist->msisdn }})</b></p>
@endif
    @if(!empty($blist->usercalls))
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
                <th align="left">Cost</th>
            </tr>
            </thead>
        <tbody>
            @php $totalcallcost = 0; $totalcall = 0; @endphp
            @foreach($blist->usercalls as $uckey => $uclist)
            @php 
            $callcost = ($uclist->cost != 0) ? (Helper::number_format($uclist->cost + ($uclist->cost*.25))) : 0;
            $totalcallcost = $totalcallcost + $callcost;
            $totalcall     = $totalcall + $uclist->duration;
            @endphp
            <tr>
            <td>{{Carbon::parse($uclist->connect_date)->format('d-M')}}</td>
            <td>{{Carbon::parse($uclist->connect_date)->format('H:i:s')}}</td>
            <td>{{ ($uclist->history_from == 1) ? 'Mobile app' : 'Sim' }}</td>
            <td>{{ '+'.str_replace("+","",$uclist->cli)}}</td>
            <td>{{ '+'.str_replace("+","",$uclist->cld)}}</td>
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
    @if(!empty($blist->userdata))
        <h4 class="titlehead">Data</h4>
        <table class="table" width="100%" border="0">
        <thead>
            <tr>
                <th align="left">Date</th>
                <th align="left">Volume(GB)</th>
                <th align="left">Cost</th>
            </tr>
            </thead>
        <tbody>
            @php $totaldatacost = 0; $totaldata = 0; @endphp
            @foreach($blist->userdata as $udkey => $udlist)
            @php 
            $amount   = (float)$udlist->amount;
            $datacost = ($amount != 0) ? (Helper::number_format($amount + ($amount*.25))) : 0;
            $data     = ((float)$udlist->duration != 0) ? Helper::bytesToGB((float)$udlist->duration):0;
            $totaldatacost = $totaldatacost + $datacost;
            $totaldata      = $totaldata + $data;
            @endphp
            <tr>
            <td>{{Carbon::parse($udlist->date)->format('d-M')}}</td>
            <td>{{ $data }}</td>
            <td>{{ $sym.Helper::number_format($datacost) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="1"><b>Total</b></td>
                <td>{{ $totaldata }}</td>
                <td>{{$sym.Helper::number_format($totaldatacost)}}</td>
            </tr>
        </tbody>
        </table>
    @endif
    @if(!empty($blist->usersms))
        <h4 class="titlehead">SMS</h4>
        <table class="table" width="100%" border="0">
        <thead>
            <tr>
                <th align="left">Date</th>
                <th align="left">Phone Number</th>
                <th align="left">Destination</th>
                <th align="left">SMS</th>
                <th align="left">Cost</th>
            </tr>
            </thead>
        <tbody>
            @php $totalsmscost = 0; $smscount = 0;@endphp
            @foreach($blist->usersms as $uskey => $uslist)
            @php 
            $amount   = (float)$uslist->amount;
            $smscost = ($amount != 0) ? (Helper::number_format($amount + ($amount*.10))) : 0;
            $totalsmscost = $totalsmscost + $smscost;
            $smscount = $smscount + $uslist->duration;
            @endphp
            <tr>
            <td>{{Carbon::parse($uslist->date)->format('d-M')}}</td>
            <td>{{ '+'.str_replace("+","",$uslist->from_number)}} </td>
            <td>{{ (strlen(trim($uslist->to_number)) <= 4) ? trim($uslist->to_number): '+'.str_replace("+","",$uslist->to_number)}} </td>
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
@endforeach

@endif
</body>
</html>