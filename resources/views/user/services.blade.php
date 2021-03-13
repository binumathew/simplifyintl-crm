<style>
.td_pad{
    padding:2% !important;
}
</style>
<div class="row">
    <div class="col-md-8">
    <div class="card m-b-20">
        @if(in_array($provider,['O2','EE_O2','VUK']))
        <div class="card-body sim-services-details">
            <h4 class="mt-0 header-title">Sim/Services</h4>
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#settings" role="tab">
                        <span class="d-none d-md-block">Sim Settings</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                    </a>
                </li>


                <li class="nav-item">
                    <a class="nav-link " data-toggle="tab" href="#bars" role="tab">
                        <span class="d-none d-md-block">Bars</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link " data-toggle="tab" href="#services" role="tab">
                        <span class="d-none d-md-block">Services</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                    </a>
                </li>

            </ul>
            <div class="tab-content">
                <div class="tab-pane active p-3" id="settings" role="tabpanel">
                <div class="row p-3">
                    @php
                    if($simstatus == 0){ 
                        $status = '<span class="badge badge-danger">Not Active</span>'; 
                    }elseif($simstatus == 1){
                        $status = '<span class="badge badge-success">Active</span>'; 
                    }
                    @endphp
                    <div class="col-md-12">
                        <h7>Current Status | {!! $status !!}</h7>
                    </div>
                    </div>
                </div>
                <div class="tab-pane p-3" id="bars" role="tabpanel">
                    <div class="row p-3">
                    
                    <hr><br>

                        <table class="table table-striped table-bordered">
                        <thead> </thead>
                        <tbody>
                        @foreach($bars as $key => $bar)
                        @php 
                        $servicevalue = json_decode($bar->service_value,TRUE);
                        if(empty($service_info) || !isset($service_info[$bar->service_key])){
                            continue;
                        }else{
                            $dwpkey    = $service_info[$bar->service_key];
                            $label     = $servicevalue[$dwpkey];
                            $labelcont = ($dwpkey == 1) ? 'Bared' : 'Not Bared';
                            $disable   = '';
                            if(isset($recentlyopted[$bar->id])){
                                $disable = 'disabled';
                                if(isset($user_services[$bar->id])){
                                    $checked   = ($user_services[$bar->id]->service_status == 1) ? 'checked' : '';
                                    $status = $user_services[$bar->id]->service_status;
                                }
                            }else{
                                $status    = $dwpkey;
                                $checked   = ($dwpkey == 1) ? 'checked' : '';
                            }
                        } 
                        @endphp
                        <tr height="50px;">
                            <td width="70%" class="td_pad">{{ $bar->service_name }}</td>
                            <td width="30%" class="td_pad">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input custom_manage_bars" id="bars_{{ $key }}" title="{{ $bar->service_name }}" data-bar_id="{{ $bar->id }}" data-status="{{ $status }}" {{ $checked }} {{ $disable }}>
                                <label class="custom-control-label" for="bars_{{ $key }}"> <b> {{$labelcont}}</b></label>
                            </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        </table>
                        <div class="col-md-6">
                        <!-- <button type="button" class="btn btn-primary btn-sm waves-effect waves-light" >Discard changes</button> -->
                        </div>
                        <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm waves-effect waves-light pull-right custom_bar_apply">Apply changes</button>
                        </div>
                    </div>
                </div>
                <div class="tab-pane p-3" id="services" role="tabpanel">
                    <div class="row p-3">
                    
                    <hr><br>

                        <table class="table table-striped table-bordered">
                        <thead> </thead>
                        <tbody>
                        @foreach($bars as $key => $bar)
                        @php 
                        $servicevalue = json_decode($bar->service_value,TRUE);
                        if(empty($network_info) || !isset($network_info[$bar->service_key])){
                            continue;
                        }else{
                            
                            $dwpkey    = $network_info[$bar->service_key];
                            $label     = $servicevalue[$dwpkey];
                            $labelcont = ($dwpkey == 1) ? 'Active' : 'Not Active';
                            $disable   = '';
                            if(isset($recentlyopted[$bar->id])){
                                $disable = 'disabled';
                                if(isset($user_services[$bar->id])){
                                    $checked   = ($user_services[$bar->id]->service_status == 1) ? 'checked' : '';
                                    $status = $user_services[$bar->id]->service_status;
                                }
                            }else{
                                $status    = $dwpkey;
                                $checked   = ($dwpkey == 1) ? 'checked' : '';
                            }
                        } 
                        @endphp
                        <tr height="50px;">
                            <td width="70%" class="td_pad">{{ $bar->service_name }}</td>
                            <td width="30%" class="td_pad">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input custom_manage_network {{ $disable }}" id="bars_{{ $key }}" title="{{ $bar->service_name }}" data-bar_id="{{ $bar->id }}" data-status="{{ $status }}" {{ $checked }} {{ $disable }}>
                                <label class="custom-control-label" for="bars_{{ $key }}"><b> {{$labelcont}}</b> </label>
                            </div>
                            </td>
                        </tr>
                        @if($bar->service_short_code == 'SER_GPRS')
                        <tr height="50px;">
                            <td width="70%" class="td_pad">4G Service (Auto added via GPRS Tariff)</td>
                            <td width="30%" class="td_pad">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input custom_manage_network disabled" {{ $checked }}>
                                <label class="custom-control-label" for=""><b> {{$labelcont}}</b>(Depend on GPRS)</label>
                            </div>
                            </td>
                        </tr>
                        <tr height="50px;">
                            <td width="70%" class="td_pad">3G Enabled (O2 only can change)</td>
                            <td width="30%" class="td_pad">
                            <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input custom_manage_network disabled" {{ $checked }}>
                                <label class="custom-control-label" for=""><b> {{$labelcont}}</b>(Depend on GPRS)</label>
                            </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                        </tbody>
                        </table>
                        <div class="col-md-6">
                        <!-- <button type="button" class="btn btn-primary btn-sm waves-effect waves-light" >Discard changes</button> -->
                        </div>
                        <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm waves-effect waves-light pull-right custom_network_apply">Apply changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @elseif(in_array($provider,['E_SIM']))
        <div class="card-body sim-services-details">
            <h4 class="mt-0 header-title">Sim/Services</h4>
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#settings" role="tab">
                        <span class="d-none d-md-block">Sim Settings</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                    </a>
                </li>


                <!-- <li class="nav-item">
                    <a class="nav-link " data-toggle="tab" href="#bars" role="tab">
                        <span class="d-none d-md-block">Bars</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                    </a>
                </li> -->

                <li class="nav-item">
                    <a class="nav-link " data-toggle="tab" href="#services" role="tab">
                        <span class="d-none d-md-block">Services</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                    </a>
                </li>

            </ul>
            <div class="tab-content">
                <div class="tab-pane active p-3" id="settings" role="tabpanel">
                <div class="row p-3">
                    @php
                    if($simstatus == 0){ 
                        $status = '<span class="badge badge-danger">Not Active</span>'; 
                    }elseif($simstatus == 1){
                        $status = '<span class="badge badge-success">Active</span>'; 
                    }
                    @endphp
                    <div class="col-md-12">
                        <h7>Current Status | {!! $status !!}</h7>
                    </div>
                    </div>
                </div>
                <!-- <div class="tab-pane p-3" id="bars" role="tabpanel">
                    <div class="row p-3">
                    
                    <hr><br>

                        <table class="table table-striped table-bordered">
                        <thead> </thead>
                        <tbody>
                        @foreach($bars as $key => $bar)
                        @php 
                        $servicevalue = json_decode($bar->service_value,TRUE);
                        if(empty($service_info) || !isset($service_info[$bar->service_key])){
                            continue;
                        }else{
                            $dwpkey    = $service_info[$bar->service_key];
                            $label     = $servicevalue[$dwpkey];
                            $labelcont = ($dwpkey == 1) ? 'Bared' : 'Not Bared';
                            $disable   = '';
                            if(isset($recentlyopted[$bar->id])){
                                $disable = 'disabled';
                                if(isset($user_services[$bar->id])){
                                    $checked   = ($user_services[$bar->id]->service_status == 1) ? 'checked' : '';
                                    $status = $user_services[$bar->id]->service_status;
                                }
                            }else{
                                $status    = $dwpkey;
                                $checked   = ($dwpkey == 1) ? 'checked' : '';
                            }
                        } 
                        @endphp
                        <tr height="50px;">
                            <td width="70%" class="td_pad">{{ $bar->service_name }}</td>
                            <td width="30%" class="td_pad">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input custom_manage_bars" id="bars_{{ $key }}" title="{{ $bar->service_name }}" data-bar_id="{{ $bar->id }}" data-status="{{ $status }}" {{ $checked }} {{ $disable }}>
                                <label class="custom-control-label" for="bars_{{ $key }}"> <b> {{$labelcont}}</b></label>
                            </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        </table>
                        <div class="col-md-6">
                        </div>
                        <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm waves-effect waves-light pull-right custom_bar_apply">Apply changes</button>
                        </div>
                    </div>
                </div> -->
                <div class="tab-pane p-3" id="services" role="tabpanel">
                    <div class="row p-3">
                    
                    <hr><br>

                        <table class="table table-striped table-bordered">
                        <thead> </thead>
                        <tbody>
                        @foreach($bars as $key => $bar)
                        @php 
                        $servicevalue = json_decode($bar->service_value,TRUE);
                        if(empty($network_info) || !isset($network_info[$bar->service_key])){
                            continue;
                        }else{
                            
                            $dwpkey    = $network_info[$bar->service_key];
                            $dwpkey    = ($dwpkey == "Enabled") ? 1 : 0;
                            $label     = $servicevalue[$dwpkey];
                            $labelcont = ($dwpkey == 1) ? 'Active' : 'Not Active';
                            $disable   = '';
                            if(isset($recentlyopted[$bar->id])){
                                $disable = 'disabled';
                                if(isset($user_services[$bar->id])){
                                    $checked   = ($user_services[$bar->id]->service_status == 1) ? 'checked' : '';
                                    $status = $user_services[$bar->id]->service_status;
                                }
                            }else{
                                $status    = $dwpkey;
                                $checked   = ($dwpkey == 1) ? 'checked' : '';
                            }
                        } 
                        @endphp
                        <tr height="50px;">
                            <td width="70%" class="td_pad">{{ $bar->service_name }}</td>
                            <td width="30%" class="td_pad">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input custom_manage_network {{ $disable }}" id="bars_{{ $key }}" title="{{ $bar->service_name }}" data-bar_id="{{ $bar->id }}" data-status="{{ $status }}" {{ $checked }} {{ $disable }}>
                                <label class="custom-control-label" for="bars_{{ $key }}"><b> {{$labelcont}}</b> </label>
                            </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        </table>
                        <div class="col-md-6">
                        </div>
                        <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm waves-effect waves-light pull-right custom_network_apply">Apply changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    </div>
</div>