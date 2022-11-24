<div class="row">
    <div class="col-12">
        <div class="card m-b-20">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#active_subscriptions" role="tab">
                            <span class="d-none d-md-block">Active Subscriptions</span>
                            <span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                        </a>
                    </li> 
                    @if($disabled_plans->isNotEmpty())                   
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#disabled_subscriptions" role="tab">
                            <span class="d-none d-md-block">In-active Subscriptions</span>
                            <span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                        </a>
                    </li>
                    @endif
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active p-3" id="active_subscriptions" role="tabpanel">
                        <table id="dataTable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Plan</th>
                                    <th>Int Min</th>
                                    <th>Provider</th>
                                    <th>Sim Number</th>
                                    <th>Amount</th>
                                    <th>Next Renewal</th>   
                                    <th>Card Detail</th>
                                    <th>Card Expire On</th>
                                    <!-- <th>Status</th>
                                    <th>Action</th> -->
                                </tr>
                            </thead>
                            <tbody> 
                                @php $i = 1; $checked = ''; @endphp
                                @if($plans)
                                @foreach ($plans as $plan)
                                @php $checked = ($plan->status)? 'checked':'' @endphp
                                <tr class="odd">
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $plan->plan->plan_name }}</td>
                                    <td>
                                        @if($plan->plan_type == 'sim')
                                            {{ isset($plan->switch)? $plan->switch->minutes : $plan->plan->in_call_limit }}
                                        @else
                                            {{ $plan->plan->minutes }}
                                        @endif
                                    </td>
                                    <td>{{ isset($plan->plan->provider) ? $plan->plan->provider : 'App' }}</td>
                                    <td>
                                        {{ $plan->sim[0]->stock->sim_number }}
                                    </td>
                                    <td>{{ $currency.$plan->total_amount }}</td>
                                    <td>{{ ($plan->next_renewal)? Helper::date_format($plan->next_renewal,'M d, Y'):'' }}</td>
                                    <td id="plan_card_type_{{$plan->id}}">{{ $plan->card_type }}</td>
                                    <td>{{ 
                                        ($plan->card_expiry != '0000-00-00' ) ? Helper::date_format($plan->card_expiry,'M d, Y') : '' }}</td>
                                    <!-- <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input change_subsciption" id="renew_{{$plan->id}}" {{$checked}} title="Disable Subscription" data-renew_id="{{$plan->id}}" data-status="{{ $plan->status}}">
                                            <label class="custom-control-label" for="renew_{{$plan->id}}"></label>
                                        </div>
                                    </td> -->
                                    <!-- <td>                                        
                                        <a class="change_card m-r-10" id="change_card_{{$plan->id}}" data-original-title="Change Card" data-id="{{Crypt::encrypt($plan->id)}}"><i class="mdi mdi-credit-card mdi-24px"></i></a>
                                        <a class="renew_subscription m-r-10" data-original-title="Renew Plan" data-plan_id="{{$plan->id}}"><i class="mdi mdi-rotate-3d mdi-24px"></i></a>
                                        @if($plan->status_changeon != null)
                                            <span class="m-r-10" title="Disable on {{ Helper::date_format($plan->status_changeon, 'd M, Y') }}"><i class="mdi mdi-information-outline mdi-24px"></i></span>
                                        @endif
                                    </td> -->
                                </tr>                                       
                                @endforeach
                                @endif 
                                                               
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane p-3" id="disabled_subscriptions" role="tabpanel">
                        <table id="dataTable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Plan</th>
                                    <th>Int Min</th>
                                    <th>Provider</th>
                                    <th>Sim Number</th>
                                    <th>Amount</th>
                                    <th>Next Renewal</th>   
                                    <th>Card Detail</th>
                                    <th>Card Expire On</th>
                                   <!--  <th>Status</th>
                                    <th>Action</th> -->
                                </tr>
                            </thead>
                            <tbody> 
                                @php $i = 1; $checked = ''; @endphp
                                @if($plans)
                                @foreach ($disabled_plans as $plan)
                                @php $checked = ($plan->status)? 'checked':'' @endphp
                                <tr class="odd">
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $plan->plan->plan_name }}</td>
                                    <td>
                                        @if($plan->plan_type == 'sim')
                                            {{ isset($plan->switch)? $plan->switch->minutes : $plan->plan->in_call_limit }}
                                        @else
                                            {{ $plan->plan->minutes }}
                                        @endif
                                    </td>
                                    <td>{{ isset($plan->plan->provider) ? $plan->plan->provider : 'App' }}</td>
                                    <td>
                                        {{ $plan->sim[0]->stock->sim_number }}
                                    </td>
                                    <td>{{ $currency.$plan->total_amount }}</td>
                                    <td>{{ ($plan->next_renewal)? Helper::date_format($plan->next_renewal,'M d, Y'):'' }}</td>
                                    <td id="plan_card_type_{{$plan->id}}">{{ $plan->card_type }}</td>
                                    <td>{{ ($plan->card_expiry != '0000-00-00' ) ? Helper::date_format($plan->card_expiry,'M d, Y') : '' }}</td>
                                    <!-- <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input change_subsciption" id="renew_{{$plan->id}}" {{$checked}} title="Enable Subscription" data-renew_id="{{$plan->id}}" data-status="{{ $plan->status}}">
                                            <label class="custom-control-label" for="renew_{{$plan->id}}"></label>
                                        </div>
                                    </td> -->
                                    <!-- <td>
                                        <a class="change_card m-r-10" id="change_card_{{$plan->id}}" data-original-title="Change Card" data-id="{{Crypt::encrypt($plan->id)}}"><i class="mdi mdi-credit-card mdi-24px"></i></a>
                                        <a class="renew_subscription m-r-10" data-original-title="Renew Plan" data-plan_id="{{$plan->id}}"><i class="mdi mdi-rotate-3d mdi-24px"></i></a>
                                        @if($plan->status_changeon != null)
                                            <span class="m-r-10" title="Disable on {{ Helper::date_format($plan->status_changeon, 'd M, Y') }}"><i class="mdi mdi-information-outline mdi-24px"></i></span>
                                        @endif
                                    </td> -->
                                </tr>                                       
                                @endforeach
                                @endif 
                                                               
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <script type="text/javascript">
        $('#dataTable').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search:''}});

        $('.change_card').on('click', function() {
            var $this = $(this);
            var renew_id = $this.data('id');
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: base_url+'/card-list',
                data: {renew_id:renew_id},            
                success: function(response){                
                    if(response.success){
                        $('#orderCustomLabel').text('Existing Card Details');
                        $('#orderCustombody').html(response.html); 
                        $('#orderCustomModal').modal('show');    
                    }else{                                     
                        alert(response.message);
                    }
                }
            });
        });

        $('.renew_subscription').on('click', function() {
            var renew_id = $(this).data('plan_id');
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: base_url+'/renewal-list',
                data: {renew_id:renew_id},
                success: function(response){                
                    if(response.success){
                        $('#orderCustomLabel').text('Subscription Renewal');
                        $('#orderCustombody').html(response.html); 
                        $('#orderCustomModal').modal('show');    
                    }else{                                     
                        alert(response.message);
                    }
                }
            });
        });

        
        
    // function updateAutoPlanStatus(autoplan_id,userid,amount,elem,admin) {
    //     var result;
    //     var thiselem = $(elem);
    //     var status   = thiselem.attr('data-status');
    //     if(status == 1){
    //         var dataamount = thiselem.attr('data-amount');
    //         var dedamount  = parseFloat((parseFloat(dataamount)*parseFloat(80))/100).toFixed(2);

    //         var options = [{ text: 'Disable Now ( '+dedamount+' will be deducted )', value: '1' },
    //             { text: 'After Contract Period (30 Days)', value: '2' }];

    //         if(admin == 1){
    //             options.push({ text: 'Disable sim card without any payment', value: '0' });             
    //         }

    //         bootbox.prompt({
    //         title: "Plan Subscription",
    //         message: '<p>Please select an option below:</p>',
    //         inputType: 'radio',
    //         inputOptions: options,

    //         callback: function (result) {
    //             if(result != null){ 
    //                 $('#loadingsign').show(); 
    //                 $.ajax({
    //                     headers: {
    //                         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //                     },
    //                     type: 'POST',
    //                     data: {'autoplan_id' :autoplan_id,'status_type':result,'user_id':userid,'amount':amount},
    //                     url: '<?php echo url('/'); ?>/update-autoplan-status',
    //                     success: function(response){ 
    //                         $('#loadingsign').hide();
    //                         if(response.status == 'success'){
    //                             thiselem.attr('data-status',0);
    //                         }
    //                         else if(response.status == 'failure'){
    //                             thiselem.prop('checked',true);
    //                         }
    //                         alert(response.message);

    //                     }
    //                 });   
    //             }else{
    //                 thiselem.prop('checked',true);
    //             }
    //         }
    //         });
    //     }else{
    //         $('#loadingsign').show();
    //         $.ajax({
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //             },
    //             type: 'POST',
    //             data: {'autoplan_id' :autoplan_id,'status_type':0,'user_id':userid,'amount':amount},
    //             url: '<?php echo url('/'); ?>/update-autoplan-status',
    //             success: function(response){ 
    //                 $('#loadingsign').hide();
    //                 alert(response.message);
    //                 if(response.status == 'success'){
    //                     thiselem.attr('data-status',1);
    //                 }
    //                 else if(response.status == 'failure'){
    //                   thiselem.prop('checked',false);
    //                 }
    //             }
    //         });   
    //     }
    // }

</script>