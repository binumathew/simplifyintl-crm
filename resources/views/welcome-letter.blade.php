<style type="text/css">
    .name-table {
        margin: 60px 30px 10px 50px;
        width: 440px !important;
    }

    .top-right-table {
        margin: 100px 0px 0px 0px;
        /*min-height: 275px;*/
        width: 95%;
    }

    .active_step {
        font-size: 7px !important;
        /* font-style: italic; */
        /* font-weight: bold;*/
    }

    .modal-content {
        font-size: 15px;
    }

    .main-content {
        padding-left: 50px;
    }
    .signature img {
        width: 200px !important;
        height: 50px !important;
        margin-left: -55px;
        margin-bottom: -15px;
        margin-top: -19px;
    }
</style>
@foreach ($sim_request as $request)
@php
$address= json_decode($request->shipping_address);
$simList = $request->list()->get();
@endphp

<!-- <div id="welcome-letter" class=" premium-number"> -->
    <div class="modal-dialog welcome-letter">
        <div class="modal-content">
            <div class="top-right-table">
                <table align="right" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <th nowrap="nowrap" class="right-border">Mobile Number</th>
                        <th nowrap="nowrap">Sim Number</th>
                    </tr>
                    @foreach ($simList as $list)
                    <tr>
                        <td nowrap="nowrap" class="right-border">
                            @if($list->stock->verified)
                                {{ '0'.ltrim($list->stock->phone_number, '44') }}
                            @else
                                0759xxxxxxx
                            @endif
                        </td>
                        <td>{{ $list->stock->box_no.'-'.$list->stock->sim_number }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td nowrap="nowrap" class="right-border">Order id</td>
                        <td nowrap="nowrap">{{ $request->order_id}}</td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" class="right-border">Date</td>
                        <td nowrap="nowrap">{{ date('Y-m-d', strtotime($request->created_at)) }}</td>
                    </tr>
                </table>
            </div>

            <div class="name-table">
                To<br />
                <b>{{ (isset($address->first_name))? ucfirst($address->first_name).' '.ucfirst($address->last_name): $request->user->name }}</b><br />
                {{ $address->street }}<br />
                {{ $address->city }}<br />
                {{ $address->country.', '. strtoupper($address->postal_code) }}<br />
            </div>

            <div class="main-content">
                <p><b>Hello {{ (isset($address->first_name))? ucfirst($address->first_name).' '.ucfirst($address->last_name): $request->user->name }},</b></p>
                <p>Welcome to {{ config('settings.app_name') }}!</p>
                <p>Thank you for choosing <b>{{ config('settings.app_name') }}</b> as your service provider.</p>
                <p><b>To activate your SIM</b></p>
                @if($list->stock->provider =='EE')
                <p ><ul class="active_step"> <li>Insert SIM into your handset.</li><li>Check if your phone is showing a valid network. It should show 3G or 4G based on your handset and the network name will be \'WELCOME\'</li><li>Please make a call to 1244. Then will receive a Welcome SMS with your phone number</li><li>After welcome message, you may also receive a network message asking to restart handset.</li><li>Please restart your handset. Now the network name will be changed to <b>‘{{ config('settings.app_name') }}’</b></li>
                </ul>
                </p>
                @endif
                <ol>
                    <li><strong>Call your friendly {{ config('settings.app_name') }} Customer Service team on 0333 9989 900</strong></li>
                    @if($list->stock->provider =='E_SIM' && $list->stock->is_esim)
                    <li><strong>Scan the Qrcode</strong></li>
                    @endif
                    
                </ol>
                @if($list->stock->provider =='E_SIM' && $list->stock->is_esim)
                <img src="{{$qrcode}}" alt="QR Code" width="200" height="200" style="display: block;margin-left: auto;margin-right: auto;"/>
                @endif
                <div class="line">&nbsp;</div>
                <div class="main-content-1">

                    <p>Please note that it may take a while for a phone to attach to the network for the first time and that this process can only be done while in the UK (you cannot activate the SIM card abroad). Once activated, the SIM card can be used for roaming. Once your {{ config('settings.app_name') }} SIM is active and fully functional, then we recommend you to visit Google Play or App Store and download {{ config('settings.app_name') }} App for using the FREE International minutes that is part of your package. Should you need to make calls outside the Free Countries, you can do so by topping up online at {{ json_decode(config('settings.company_details'))->company_website }} Once again welcome to {{ config('settings.app_name') }} community.</p>
                    <p class="signature"><img src="{{ asset('/images/signature.png')}}" /><br />
                    <b>David Quirk</b><br />
                    Manager – Customer Services<br />
                    {{ config('settings.app_name') }}</p>
                </div>
            </div>
        </div>
    </div>
<!-- </div> -->
<p style='page-break-after:always'></p>
@endforeach
