<style>
.td_pad{
    padding:2% !important;
}
</style>
<div class="row">
    <div class="col-md-8">
    <div class="card m-b-20">
        @if($provider == 'AT_T')
        <div class="card-body sim-services-details">
            <h4 class="mt-0 header-title">Sim/Services</h4>
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#settings" role="tab">
                        <span class="d-none d-md-block">Sim Settings</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                    </a>
                </li>
                @if($simstatus == 'Active')
                @if($bars->isNotEmpty())
                <li class="nav-item">
                    <a class="nav-link " data-toggle="tab" href="#bars" role="tab">
                        <span class="d-none d-md-block">Bars</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link " data-toggle="tab" href="#services" role="tab">
                        <span class="d-none d-md-block">Services</span>
                        <span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span>
                    </a>
                </li>
                @endif
            </ul>
            <div class="tab-content">
                <div class="tab-pane active p-3" id="settings" role="tabpanel">
                    <div class="row p-3">
                    @php
                    if($simstatus == 'Active'){ 
                        $status = '<span class="badge badge-success">'.$simstatus.'</span>'; 
                    }elseif($simstatus == 'Suspended'){
                        $status = '<span class="badge badge-warning">'.$simstatus.'</span>'; 
                    }elseif($simstatus == 'Cancelled'){
                        $status = '<span class="badge badge-danger">'.$simstatus.'</span>'; 
                    }
                    @endphp
                    <div class="col-md-12">
                        <h7>Current Status | {!! $status !!}</h7>
                    </div>
                        <hr><br>
                        <div class="col-md-12">
                        <form id="sim_status_form">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Change Status</label>
                                    <select name="change_sim_status" id="change_sim_status" class="form-control custom-select required">
                                        <option value="">Choose</option>
                                        <option value="1">Suspend</option>
                                        <option value="2">Resume</option>
                                        <option value="3">Cancel</option>  
                                    </select>
                                </div>
                            </div> 
                            <div class="col-md-6 sim_reason ">
                                <div class="form-group">
                                    <label>Reason</label>
                                    <select name="sim_status_reason" id="sim_status_reason" class="form-control custom-select required" >
                                        <option value="">Choose</option> 
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                            <button type="button" class="btn btn-success waves-effect waves-light sim_status_btn">Submit</button>
                            </div> 
                        </form>
                        </div>
                    </div>
                </div>
                @if($simstatus == 'Active')
                @if($bars->isNotEmpty())
                <div class="tab-pane p-3" id="bars" role="tabpanel">
                    <div class="row p-3">
                    <h7>Provider : {{ $bars[0]->provider }}</h7>
                    <hr><br>
                    
                        <table class="table table-striped table-bordered">
                        <thead> </thead>
                        <tbody>
                        @foreach($bars as $key => $bar)
                        @php
                            $servicevalue = json_decode($bar->service_value,TRUE);
                            $label = ($service_info->isNotEmpty() && isset($service_info[$bar->service_key])) ? $service_info[$bar->service_key]->value : '';
                            $checked   = ($label == 'Yes') ? 'checked' : '';
                            $status_to = ( array_search($servicevalue[$label],array_values($servicevalue)) == 0) ? array_values($servicevalue)[1] : array_values($servicevalue)[0];
                            $status = ( array_search($servicevalue[$label],array_values($servicevalue)) == 0) ? array_keys($servicevalue)[1] : array_keys($servicevalue)[0];
                        @endphp
                        <tr height="50px;">
                            <td width="70%" class="td_pad">{{ $bar->service_name }}</td>
                            <td width="30%" class="td_pad">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input manage_bars" id="bars_{{ $key }}" title="{{ $bar->service_name }}" data-bar_id="{{ Crypt::encrypt($bar->id) }}" data-status="{{ $status }}" {{ $checked }} data-status_to="{{ $status_to }}">
                                <label class="custom-control-label" for="bars_{{ $key }}"> {{ $label  }}</label>
                            </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        </table>
                    </div>
                </div>
                @endif
                @endif
                <div class="tab-pane p-3" id="services" role="tabpanel">
                    <div class="row p-3">
                        <div class="col-md-12">
                        <form id="sim_services_form">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Services</label>
                                    <select name="change_sim_services" id="change_sim_services" class="form-control custom-select required">
                                        <option value="">Choose</option>
                                        <option value="1">Change IMEI</option>
                                        <option value="2">Change SIM</option> 
                                    </select>
                                </div>
                            </div> 
                            <div class="col-md-6 imei_div" style="display:none;">
                                <div class="form-group">
                                    <label>IMEI</label>
                                    <input class="form-control required" type="text" name="imei" id="imei" maxlength="25" value=""/>
                                </div>
                            </div>
                            <div class="col-md-6 sim_div" style="display:none;">
                                <div class="form-group">
                                    <label>SIM NUMBER</label>
                                    <input class="form-control required" type="text" name="sim_number" id="sim_number" maxlength="25" value=""/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Zipcode</label>
                                    <input class="form-control required" type="text" name="postal_code" id="postal_code" maxlength="5" value=""/>
                                </div>
                            </div>
                            <div class="col-md-4">
                            <button type="button" class="btn btn-success waves-effect waves-light sim_services_btn">Submit</button>
                            </div> 
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    </div>
</div>