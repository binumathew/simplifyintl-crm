@extends('layouts.home')
@section('content')
<!-- page wrapper start -->
        <div class="wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="btn-group pull-right">
                                <ol class="breadcrumb hide-phone p-0 m-0">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">Define {{ $type }} Clawback</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Define {{ $type }} Clawback</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">

                        <div class="card m-b-20">

                            <div class="card-body right-nav">

                                <ul>

                                    <li><a href="{{ url('/clawback-plan') }}" class="{{ ($type == 'Plan') ? 'selected' : ''}}">Clawback Plan</a></li>                                    

                                    <li><a href="{{ url('/clawback-dealer') }}" class="{{ ($type == 'Dealer') ? 'selected' : ''}}">Clawback Dealer</a></li>

                                </ul>                            

                            </div>

                        </div>

                    </div>
                    <div class="col-10">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('save-clawback') }}" id="save-clawback-form" method="POST">
                                        @csrf
                                    <h5>{{ (!empty($plan)) ? 'Edit' : 'Define' }} {{ $type }}  Clawback</h5>
                                    <input type="hidden" name="edit_id" value="{{ (!empty($plan)) ? Crypt::encrypt($plan->id) : ''}}">
                                    <input type="hidden" name="type" value="{{ $type }}" id="type">
                                    <div class="row">
                                        @if($type == 'Dealer')
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Dealer</label>
                                                <div class="input-group">
                                                   <select class="form-control" name="dealer_id" id="dealerId" required>
                                                    <option value="">Choose</option>
                                                    @foreach ($dealer as $usr)
                                                        <option {{ (!empty($plan) && $plan->admin_id == $usr->id)?'selected':'' }} value="{{Crypt::encrypt($usr->id)}}">{{$usr->first_name.'-'.$usr->promocode}}</option>
                                                    @endforeach
                                                </select>
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Plan Type</label>
                                                <div class="input-group">
                                                    <select class="form-control" name="plan_type" id="planType" required >
                                                        <option value="">Choose</option>
                                                        <option value="1" {{ (!empty($plan) && $plan->plan_type ==1)?'selected':'' }}>Plan</option>
                                                        <option value="2" {{ (!empty($plan) && $plan->plan_type ==2)?'selected':'' }}>Bundle</option>
                                                    </select>
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Plan Name</label>
                                                <div class="input-group">
                                                    <select class="form-control" name="plan_id" id="planId" required>
                                                    <option value="">Choose</option>
                                                    </select>
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Period (Months)</label>
                                                <div class="input-group">
                                                    <input type="number" name="period" class="form-control" maxlength="4" autocomplete="off" value="{{ (!empty($plan)) ? $plan->period : "" }}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <div class="input-group">
                                                   <select name="status" required class="form-control dyninp">
                                                    <option value="1" {{ (!empty($plan) && $plan->status ==1)?'selected':'' }} >Active</option>
                                                    <option value="0" {{ (!empty($plan) && $plan->status ==0)?'selected':'' }}>InActive</option>
                                                    </select>
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-primary ">{{ (!empty($plan)) ? 'Update' : 'Define' }} Clawback</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                            
                                        </div>
                                    </div>
                                    </div>
                                    </form>
                                </div>                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- page wrapper end -->
<script>
    var DurationCount = $(".forCloned").children().length + 1;
    var duration = $('.commDuration:first option').length - 1;

    $('#resetBtn').on('click', function(e) {
       $('#save-clawback-form')[0].reset();    
    });
    getplan(<?php echo (!empty($plan)) ? $plan->plan_id : "";?>);
    function getplan($planid = ""){
        var type = $('select[name=plan_type]').val();
        $('#loadingsign').show();
        if(type != ""){
        $('#planId').find('option').not(':first').remove();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            data:{type:type},
            url: base_url+'/comm-getplan',
            success: function(response){ 
                var data = JSON.parse(response);
                $.each(data,function(k,val){
                    var sel = simbundle = "";
                    if( $planid == val.id){
                        sel = "selected";
                    }
                    if(val.sim_count != undefined)
                        { simbundle = " Bundle -"+val.sim_count;}
                    var option = "<option "+sel+" value='"+val.id+"'>"+val.plan_name+simbundle+"</option>";
                    $("#planId").append(option); 
                });
            }
        });
        }   
    }
</script>
@endsection