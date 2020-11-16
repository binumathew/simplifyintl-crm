<style type="text/css">
    .printableArea {
        font-family: "Verdana", Geneva, sans-serif;
        margin: 20px 20px 20px 30px;
    }
    .session-one{
        min-height: 275px; /*250px;*/
        width: 91%;
        margin: 10px 20px 0px 20px;
    }
    .session-two{
        clear: both;
        width: 91%;
        margin: 10px 20px 0px 20px;
    }
    .session-one img{
        position: relative;
        left: 45px;
    }
    .address{
        height: 105px;
        width: 245px;
        position: relative;
        left: 25px;  /*left: 10px;*/
        top: 250px;  /*top: 175px;*/
        line-height: 1.2;
        border: 1px solid #c7bdbd;
        padding: 5px;
    }
    .address ul{
        padding-inline-start: 10px;
    }
    .sim_detail_hldr{
        float: right;
        margin: 0px 0px 30px 0px;
    }
    .custom_row{
        margin-right: 0px;
        display: flex;
    }
    /*.sim_paste{
        width: 150%;
    }*/
    .sim_paste_hldr{
        /*display: flex;*/
        justify-content: center;
        margin-top: 100px;
        margin-left: 25px;
    }
    /*.sim_info{
        height: 200px;
        width: 53%;*/
        /*border: 1px solid black;*/
        /*padding: 8px 0px 0px 20px;*/

       /* margin-top: 30px;
        margin-left: 130px;
    }*/
    .content_holder{
        margin: 22px 10px 5px 25px;
    }
    .align_center{
        display: flex;
        justify-content: center;
    }
    .printableArea table {
        border-collapse: collapse;
    }

    .printableArea table, .printableArea table  td,.printableArea table  th {
        border: 1px solid #c7bdbd;
        padding: 5px 6px;
    }
    .fold_mark{
        position: relative;
        top: 307px;
        border: .5px dashed #989696;
        width: 100%;
    }
    .signature img{
        width: 200px !important;
        height: 50px !important;
        margin-left: -55px;
        margin-bottom: -15px;
        margin-top: -19px;
    }
    @page { margin: 0px 10px 0px 5px; }

</style>
<section class="printableArea">
    @foreach ($sim_request as $request)
    @php
    $address= json_decode($request->shipping_address);
    $simList = $request->list()->get();
    @endphp
    <div class="session-one">
        <!-- <img src="images/logo.svg" height="30px" /> -->
        <div class="sim_detail_hldr">
            <table>
                <tr>
                    <th>Mobile Number</th>
                    <th>Sim Number</th>
                </tr>
                @foreach ($simList as $list)
                <tr>
                    <td>{{ str_replace("44", "0", $list->stock->phone_number) }}</td>
                    <td>{{ $list->stock->box_no.'-'.$list->stock->sim_number }}</td>
                </tr>
                @endforeach
                <tr>
                    <th>Order id</th>
                    <td colspan="2">{{ $request->order_id}}</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td colspan="2">{{ date('Y-m-d') }}</td>
                </tr>
            </table>
        </div>
        <div class="address">
            <ul>To
                <li><b>{{ (isset($address->first_name))? ucfirst($address->first_name).' '.ucfirst($address->last_name): $request->user->name }}</b></li>
                <li>{{ $address->street }}</li>
                <li>{{ $address->city }}</li>
                <li>{{ $address->country.', '. $address->postal_code }}</li>
            </ul>
        </div>
    </div>
    <div class="fold_mark"></div>
    <div class="session-two">
        <div class="custom_row">
            <div class="sim_paste">
                <div class="sim_paste_hldr">
                    <p><b>Hello {{ (isset($address->first_name))? ucfirst($address->first_name).' '.ucfirst($address->last_name): $request->user->name }},</b><br/></p>
                    <h4>Welcome to AVOO!</h4>
                </div>
            </div>
        </div>
        <div class="content_holder">
            <p>Thank you for choosing <strong>AVOO</strong> as your service provider.</p>
            <!-- <p><strong>Plan Details : </strong> {{ '$simList[0]->auto_plan->plan->plan_name'}}<br><p> -->
            <!-- <p>
                Next Steps to activate and start enjoying both AVOO Mobile an AVOO Mobile App;
            </p> -->
            <label>To activate your SIM</label> <br/>
            <p>Insert SIM into your handset.<br/>Check if your phone is showing a valid network. It should show 3G or 4G based on your handset and the network name will be \'WELCOME\'<br/>Please make a call to 1244. Then will receive a Welcome SMS with your phone number<br/>After welcome message, you may also receive a network message asking to restart handset.<br/>Please restart your handset. Now the network name will be changed to <b>‘AVOO’</b><br/></p>
            <ol>
                <li><strong>Call your friendly AVOO Customer Service team on 0333 9989 900</strong></li>
            </ol>
            <p>Please note that it may take a while for a phone to attach to the network for the first time and that this process can only be done while in the UK (you cannot activate the SIM card abroad). Once activated, the SIM card can be used for roaming.</p><p>If you donot use the SIM card to make a call, send SMS or data within 90 days, the SIM card will be terminated.</p><br/>
            <p>
                <p class="signature">
                    <img src="{{ asset('/images/signature.png')}}">
                </p>
                <b>David Quirk</b><br/>
                Manager – Customer Services<br/>
                AVOO Mobile
            </p>

        </div>

    </div>
    <p style='page-break-after:always'></p>
    @endforeach
</section>
