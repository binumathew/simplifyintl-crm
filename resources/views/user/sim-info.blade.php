<div class="row">
    @if($provider == "AT_T")
    <div class="col-md-7">
        <div class="card m-b-20">
            @php
                if($service_info->status == 'Active'){ 
                    $status = '<span class="badge badge-success">'.$service_info->status.'</span>'; 
                }elseif($service_info->status == 'Suspended'){
                    $status = '<span class="badge badge-warning">'.$service_info->status.'</span>'; 
                }elseif($service_info->status == 'Cancelled'){
                    $status = '<span class="badge badge-danger">'.$service_info->status.'</span>'; 
                }
            @endphp
            <div class="card-body sim-info">
            <h4 class="mt-0 header-title">Sim Info</h4>
            <table class="table table-striped table-bordered">
                <thead> </thead>
                <tbody>
                <tr>
                    <td width="70%">Sim</td>
                    <td width="30%">{!! $status !!}</td>
                </tr>
                <tr>
                    <td width="70%">Effective Date</td>
                    <td width="30%">{{ Carbon::parse($service_info->effectiveDate)->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td width="70%">Billing Account Number</td>
                    <td width="30%">{{ $service_info->billingAccountNumber }}</td>
                </tr>
                @foreach($service_info->characteristics as $key => $list)
                @php 
                if($list->name == 'activationDate' || $list->name == 'planEffectiveDate'){
                    $list->value = Carbon::parse($list->value)->format('d-m-Y');
                }
                @endphp
                <tr>
                    <td width="70%">{{ $list->name }}</td>
                    <td width="30%">{{ $list->value }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card m-b-20">
            <div class="card-body sim-info">
            <h4 class="mt-0 header-title">Sim Usage</h4>
            @foreach($service_info->usage as $key => $list)
            <h6><b>{{ $key }}</b></h6>
            <table class="table table-striped table-bordered">
                <thead> </thead>
                <tbody>
            @foreach($list as $lkey => $lst)
            @php 
                if($key == 'DATA'){
                    $lst = Helper::bytesToGB($lst);
                }

            @endphp
                <tr>
                    <td width="70%">{{ $lkey }}</td>
                    <td width="30%">{{ $lst }}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
            @endforeach
            </div>
        </div>
    </div>
    @endif
</div>