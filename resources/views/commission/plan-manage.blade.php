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
                                    <li class="breadcrumb-item active">Define Plan Commission</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Define Plan Commission</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">

                        <div class="card m-b-20">

                            <div class="card-body right-nav">

                                <ul>
                                    <li><a href="{{ url('/comm-plan') }}" class="selected">Plan Commissions List</a></li>                                    

                                </ul>                            

                            </div>

                        </div>

                    </div>
                    <div class="col-10">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('save-plancommission') }}" id="save-plancommission-form" method="POST">
                                        @csrf
                                    <h5>{{ (!empty($plan)) ? 'Edit' : 'Define' }} Plan Commission</h5>
                                    <input type="hidden" name="edit_id" value="{{ (!empty($plan)) ? Crypt::encrypt($plan[0]->id) : ''}}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Plan Type</label>
                                                <div class="input-group">
                                                    <select class="form-control" name="plan_type" id="planType" required>
                                                        <option value="">Choose</option>
                                                        <option value="1" {{ (!empty($plan) && $plan[0]->plan_type ==1)?'selected':'' }}>Plan</option>
                                                        <option value="2" {{ (!empty($plan) && $plan[0]->plan_type ==2)?'selected':'' }}>Bundle</option>
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
                                        <div class="col-md-4"></div>
                                    </div>
                                        @php  
                                        $count = (!empty($plan)) ? count($plan) : 1;
                                        @endphp
                                        @for($i = 0 ; $i < $count ; $i++)
                                        @if($i == 0)
                                        <div class="toClone">
                                        @else
                                        <div class="forCloned">
                                        @endif
                                        <div class="row child">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Commission Rate</label>
                                                <div class="input-group">
                                                    <input type="text" name="comm_rate[]" required autocomplete="off" class="form-control dyninp" value="{{ (!empty($plan))?$plan[$i]->comm_rate :'' }}">
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Commission Type</label>
                                                <div class="input-group">
                                                    <select class="form-control dyninp" name="comm_type[]"  required>
                                                    <option value="">Choose</option>
                                                    <option value="1" {{ (!empty($plan) && $plan[$i]->comm_type ==1)?'selected':'' }}>Fixed</option>
                                                    <option value="2" {{ (!empty($plan) && $plan[$i]->comm_type ==2)?'selected':'' }}>Percentage</option>
                                                    </select>
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Duration</label>
                                                <div class="input-group">
                                                    <select class="form-control dyninp commDuration" name="comm_duration[]"  required>
                                                    <option value="">Choose</option>
                                                    @foreach ($duration as $comm_dur)
                                                        <option {{ (!empty($plan) && $plan[$i]->comm_duration ==$comm_dur->id)?'selected':'' }}  value="{{$comm_dur->id}}">{{$comm_dur->name}}</option>
                                                    @endforeach
                                                    </select>
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <div class="input-group">
                                                   <select name="status[]" required class="form-control dyninp">
                                                    <option value="1" {{ (!empty($plan) && $plan[$i]->status ==1)?'selected':'' }} >Active</option>
                                                    <option value="0" {{ (!empty($plan) && $plan[$i]->status ==0)?'selected':'' }}>InActive</option>
                                                    </select>
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>&nbsp</label>
                                                <div class="input-group">
                                                @if($i == 0)
                                                  <button type="button" class="btn btn-primary toadd">+</button>
                                                @else
                                                <button type="button" class="btn btn-warning toremove ">-</button>
                                                @endif
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                    @if($count == 1)
                                    <div class="forCloned"></div>
                                    @endif
                                    @endfor
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" id="plancommBtn" class="btn btn-primary ">{{ (!empty($plan)) ? 'Update' : 'Define' }} Commission</button>
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
       $('#save-plancommission-form')[0].reset();    
    });
    getplan(<?php echo (!empty($plan)) ? $plan[0]->plan_id : "";?>);
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