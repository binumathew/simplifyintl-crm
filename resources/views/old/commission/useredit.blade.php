@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-10">
				<div class="Cleftpart">
					<h1>Update User Plan Commission</h1>
				</div>
				<form action="{{ url('save-userplancommission') }}" method="post" id="commplan-form">					
					@csrf
					<input type="hidden" name="id" value="{{ $plandetails[0]->userid }}">
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-4 col-sm-4">
								<div class="popTbox">
									<i>User</i>
									<select class="form-control" name="user_id" id="userId" required>
										<option value="">Choose</option>
										@foreach ($user as $usr)
											<option {{ ($plandetails[0]->user_id ==$usr->id)?'selected':'' }} value="{{$usr->id}}">{{$usr->first_name.'-'.$usr->promocode}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-md-4 col-sm-4">
								<div class="popTbox">
									<i>Plan Type</i>
									<select class="form-control" name="plan_type" id="planType" required>
										<option value="">Choose</option>
										<option value="1"  {{ ($plandetails[0]->plan_type ==1)?'selected':'' }}>Plan</option>
										<option value="2" {{ ($plandetails[0]->plan_type ==2)?'selected':'' }}>Bundle</option>
									</select>
								</div>
							</div>
							<div class="col-md-4 col-sm-4">
								<div class="popTbox">
									<i>Plan Name</i>
									<select class="form-control" name="plan_id" id="planId" required>
										<option value="">Choose</option>
									</select>
								</div>
							</div>
							@php $i = 0; @endphp
							@foreach($plandetails as $plan)
							@if($i == 0)
							<div class="toClone">
							@else
							<div class="forCloned">
							@endif
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>Commission Rate</i>
									<input type="text" value="{{$plan->comm_rate}}" name="comm_rate[]" required autocomplete="off">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>Commission Type</i>
									<select class="form-control" name="comm_type[]" id="commType" required>
										<option value="">Choose</option>
										<option value="1" {{ ($plan->comm_type ==1)?'selected':'' }}>Fixed</option>
										<option value="2" {{ ($plan->comm_type ==2)?'selected':'' }}>Percentage</option>
									</select>
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>Duration</i>
									<select class="form-control" name="comm_duration[]" id="commDuration" required>
										<option value="">Choose</option>
										@foreach ($duration as $comm_dur)
											<option  {{ ($plan->comm_duration ==$comm_dur->id)?'selected':'' }} value="{{$comm_dur->id}}">{{$comm_dur->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-md-2 col-sm-2">
								<div class="popTbox">
									<i>Status</i>
									<select name="status[]" required>
										<option value="1" {{ ($plan->status ==1)?'selected':'' }} >Active</option>
										<option value="0" {{ ($plan->status ==0)?'selected':'' }} >InActive</option>
									</select>
								</div>
							</div>
							<div class="col-md-1 col-sm-1">
								<div class="popTbox">
									<i>&nbsp</i>
									@if($i == 0)
									<button type="button" class="btn btn-primary toadd" id="addButton">+</button>
									@endif
								</div>
							</div>
						</div>
						 @php $i++; @endphp
						 @endforeach
						</div>
						<div class="alert-status"> 
							@if(session()->has('message'))
							<div class="alert alert-success" id="success">
								{{ session()->get('message') }}
							</div>
							@endif
							@if(Session()->has('error'))
							<div class="alert alert-danger">
								{{ Session()->get('error') }}
							</div>
							@endif
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<a class="cancel_btn" href="{{ url('comm-userlist') }}">CANCEL</a>
									<input name="" type="submit" class="greenbtn" value="SUBMIT">
								</div>
							</div>
						</div>									
					</div>
				</form>
			</div>
			<div class="col-md-4">
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		var DurationCount   = $(".forCloned").children().length + 1;
		var duration 		= $('#commDuration option').length - 1;
		getplan(<?php echo $plandetails[0]->plan_id;?>);
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
					$('#loadingsign').hide();
				}
			});
			}	
		}
		$(document).on('click', '.toadd', function(){
			if( DurationCount < duration ){
				var clone = $(".toClone").clone();
				clone.find('button').removeClass('btn-primary toadd').addClass('toremove btn-warning').html('-');
				clone.find('input').val('');
				$(".forCloned").append(clone);
				DurationCount++;
			}
		});
		$(document).on('click', '.toremove', function(){
			$(this).parents().eq(2).remove();
			DurationCount--;
		});
		$(document).on('change', '#planType', function(){
			getplan();
		});
	});
</script>
@endsection