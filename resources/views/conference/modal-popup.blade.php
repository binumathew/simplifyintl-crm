@if(isset($conference))
 @php 
    $msisdnlist = json_decode($conference->msisdnlist);
    $msisdn_data = json_decode($conference->msisdn_data);
    $con_setting = json_decode($conference->conf_settings);
    $settings = json_decode(Helper::get_option('conference_settings_name'));
@endphp
<div class="card m-b-20">
    <div class="card-body">        
        <div class="form-group col-md-12">
            <label>Description</label>
            <textarea class="form-control" disabled>{{ $conference->description }}</textarea>
        </div>
        <span class="p-2">Conference Settings</span>
        <div class="row form-group p-2">            
            @foreach($settings as $key => $setting)   
            <div class="col-md-3">
                <div class="form-group">                                                                                 
                    <label class="col-form-label">{{ $setting->name }}</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="{{$key}}" {{($con_setting->{$key}) ?'checked':''}}  disabled>
                        <label class="custom-control-label" for="{{$key}}"></label>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <span>Participants</span>
        <ol class="activity-feed mb-0">           
            @if($msisdnlist)
                @foreach ($msisdnlist as $msisdn)
                <li class="feed-item">                                            
                    <span class="activity-text ">{{ $msisdn->contact_person_name }}</span>
                    <span class="date text-muted">{{ $msisdn->phoneno .' '. $msisdn_data->{$msisdn->phoneno}->tariff }}</span>
                    <span class="date text-muted">{{ $msisdn->emailid }}</span>
                    <span class="date text-muted">{{ $msisdn_data->{$msisdn->phoneno}->time .' ( '.$msisdn_data->{$msisdn->phoneno}->time_zone.' )' }}</span>

                </li>
                @endforeach
            @endif
        </ol>                  
    </div>
</div>  

<button type="button" class="btn btn-secondary pull-right" data-dismiss="modal">Close</button>       
@endif
