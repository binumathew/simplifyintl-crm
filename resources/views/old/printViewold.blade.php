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
    .sim_paste{ 
        width: 47%;
    }
    .sim_paste_hldr{
        /*display: flex;*/
        justify-content: center;
        margin-top: 100px;
        margin-left: 25px;
    }
    .sim_info{
        height: 200px;
        width: 53%;
        /*border: 1px solid black;*/
        /*padding: 8px 0px 0px 20px;*/
        
        margin-top: 30px;
        margin-left: 130px;
    }
    .content_holder{
        margin: 40px 10px 5px 25px;
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
        <!-- <img src="public/images/logo.svg" height="30px" /> -->
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
                    <p><b>Hello!</b><br/>
                    Your AVOO SIM is here. </p>
                </div>
            </div>
            <div class="sim_info"></div>
            <!-- 
                
                <p>About AVOO SIM enclosed here; We have enclosed herein your new AVOO SIM in THREE sizes to match your mobile fit. Including Standard, Micro and Nano as you wish.</p><br/><br/> -->
                <!-- <h4 class="align_center">Pop out the SIM you require</h4> -->
                <!-- <div class="align_center">
                    <img src="public/images/sim_img.png" height="50px" />
                </div><br/>
                <p>Please make sure that you always turn your phone OFF when removing or inserting s SIM Card.</p>
               
            </div> -->
        </div>
        <div class="content_holder">
            <p>
                Next Steps to activate and start enjoying both AVOO Mobile an AVOO Mobile App;
            </p>
            <label>To activate your SIM</label>
            <!-- <ol>
                <li>
                    1.  Visit www.avoomobile.com and LOG IN using your New Mobile No. Once you are Logged In for the steps for Activating your New SIM.                                   
                </li>
            </ol> -->
            <!-- <p>Alternatively</p> -->
            <ol>
                <li><strong>Call your friendly AVOO Customer Service team on 0333 9989 900</strong></li>
            </ol>
            <p>
                Now you are good to go with your AVOO Mobile services. We welcome you to the AVOO Mobile family.
            </p><br/>
            <p>
                <p class="signature">
                    <img src="{{ asset('/public/images/signature.png')}}">
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