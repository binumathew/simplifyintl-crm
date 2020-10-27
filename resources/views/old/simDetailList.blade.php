<div class="simDetail_content">
    @php
    $shippingAddress=json_decode($address);
    $adrs = $shippingAddress->street.', '.$shippingAddress->city.', '.$shippingAddress->country.', '.$shippingAddress->postal_code;
    @endphp
    <table id="deliverySimListTbl" class="popup_table" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Phone Number / Sim Number</th>
                <th>Shipping Address</th>
                <th>Box Number</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($sim_list as $Bundle)
            @php
            $simDetail = $Bundle->getSim();
            $i =1;
            @endphp
            @foreach ($simDetail['simList'] as $sim)
            <tr>
                @if($i==1)
                <td rowspan="{{ $simDetail['count'] }}">{{ $sim->auto_plan->plan->plan_name }}
                    ({{$sim->auto_plan->plan->provider}})</td>
                @php $i++ @endphp
                @endif
                <td>{{ $sim->stock->phone_number}}/<br>{{$sim->stock->sim_number }}</td>
                <td>{{ $adrs }}</td>
                <td>{{ $sim->stock->box_no }}</td>
                <td>
                    <a href="#" class="btn btn-info btn-xs updt_adrs fa fa-pencil-square-o editbtn" data-id="{{ $sim->id}}" title="Update Address"></a>
                    <a data-toggle="tooltip" title="Order Duplicate" href="#" class="btn btn-info btn-xs fa fa-retweet order_duplicate" data-id="{{ $sim->id}}"></a>
                </td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</div>
<br>
<button type="button" id="sim_detail_print" class="btn btn-primary"><i class="fa fa-print"></i> Print</button>
