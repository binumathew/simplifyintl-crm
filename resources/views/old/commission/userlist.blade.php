@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Dealer-Plan Commission</h1>
					</div>
					<div class="Unavbg">
						<div class="row">
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
							<div class="col-md-6 col-sm-5">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Plans</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
								</ul>
							</div>
							<div class="col-md-4 col-sm-5">
							<ul class="EAbtnbg">
								<!-- <li class="expo">
									<a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a>
								</li> -->
								<li><a href="{{ url('create-commplanuser') }}">Add&nbsp;UserPlan Commission</a></li>
							</ul>
							</div>
							<div class="col-md-2 col-sm-2">
							<ul class="EAbtnbg">
								<li><a data-toggle="modal" data-target="#addRevenue" id="add_revenue_btn">Add Revenue</a></li>
							</ul>
							</div>
						</div>
					</div>   
					@csrf
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-12">
								<table id="contacttable" class="display responsive no-wrap" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>User</th>
											<th>Plan Type</th>											
											<th>Plan Name</th>
											<th>Commission Rate</th>
											<th>Commission Type</th>
											<th>Duration</th>	
											<th>Status</th>
											<th>Created at</th>								
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i=1; ?>
										@foreach ($plandetails as $plan)
										<tr id="{{$i}}">
											<td>{{$plan->first_name.'-'.$plan->promocode }}</td>
											<td>{{($plan->plan_type == 1)? 'Plan':'Bundle'}}</td>
											<td>{{$plan->plan_name}}</td>							
											<td>{{$plan->comm_rate}}</td>
											<td>{{($plan->comm_type == 1)? 'Fixed':'Percentage'}}</td>
											<td>{{$plan->name}}</td>
											<td>{{($plan->status == 0)? 'InActive':'Active'}}</td>
											<td>{{date('d-m-Y',strtotime($plan->created_at))}}</td>									
											<td>
												<a data-toggle="tooltip" title="Edit"  href="{{url('/edit-commplanuser',Crypt::encrypt($plan->plan_type.'-'.$plan->plan_id.'-'.$plan->userid))}}" class="fa fa-pencil-square-o editbtn"></a>
												<a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_userplancomm" data-plan_id="{{$plan->id}}" ></a>										
											</td>
										</tr>
										<?php 
										$i++; ?>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="addRevenue" class="modal fade avoopopup" role="dialog">
        <div class="modal-dialog mt-7p">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h3 class="modal-title" id="addCreditModalTitle">Add Revenue</h3>
                </div>
                <div class="modal-body">
                    <form id="new-revenue-form" method="POST">
                        <div class="form-group">
                            <label>Choose Dealer</label>
                            <select id="addrev_dealer" class="form-control" name="dealer">
                            	<option value="-1" selected>Choose</option>
                                @foreach($dealers as $dealer)
                                    <option value="{{$dealer->id}}">{{$dealer->first_name}} {{$dealer->last_name}} ({{$dealer->promocode}})</option>
                                @endforeach
                            </select>
                        </div>  
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="text" id="revenue_amount" class="form-control" name="revenue_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="number">Expiry</label>
		                       <input type="text" class="form-control customdate" name="expiry_at" id="expiry_at" placeholder="Valid Till" required>
                        </div>
                        <div id="rev_msg" style="display:none;" class="alert"></div>                                   
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="payRevenue">Save</button>
                    </form>
                    
                </div>
            </div>

        </div>
    </div>

	<script type="text/javascript">
	$(document).ready(function(){
		$('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		$('.dataTables_filter input').attr("placeholder", "Search");

		// $(document).on("click", ".open-AddBookDialog", function () {
		// 	var myBookId = $(this).data('id');
		// 	$(".modal-body #bookId").val( myBookId );         
		// 	// $('#addBookDialog').modal('show');
		// });
		// if(confirm("Do you really want to delete this contact ?")){
		// 	$.ajax({
		// 		type:"POST",
		// 		url:'deletecontact',
		// 		headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
		// 		data:{id:id},
		// 		success:function(){
		// 			$('#'+list_id).fadeOut(1000);
		// 		}
		// 	})
		// }

		$(document).on("click", ".delete_userplancomm", function () {
			var planid = $(this).attr('data-plan_id');
			if(confirm("Do you really want to delete this user plan ?")){
				$.ajax({
				type:"POST",
				url:'delete-userplan-comm',
				headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
				data:{id:planid},
				success:function(data){
					alert(data['message']);
					window.location.reload();
				}
				})
			}
		});

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);

		$(document).on("click", '#payRevenue', function (){

            if($('#new-revenue-form').valid()) {
                $('#rev_msg').hide();
                //$('#loadingsign').show();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    data: $('#new-revenue-form').serialize(),
                    url: '<?php echo url('/'); ?>/add-revenue',
                    success: function(response){ 
                        //$('#loadingsign').hide();
                        if(response.status == "success"){
                        	$("#rev_msg").removeClass('alert-danger').addClass('alert-success');
                            $('#rev_msg').text(response.message);
                            $('#rev_msg').show();
                            $('#new-revenue-form').trigger("reset");
                        }
                        else{
                            $("#rev_msg").removeClass('alert-success').addClass('alert-danger');
                            $('#rev_msg').text(response.message);
                            $('#rev_msg').show();
                        }
                    }
                });
            }
            
        });
        $(document).on("change", '#addrev_dealer', function (){
        	var dealerid = $(this).val();
        	if(dealerid != -1)
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    data: {dealer:dealerid},
                    url: '<?php echo url('/'); ?>/get-revenue',
                    success: function(response){ 
                        if(response.status == "success"){
                            $('#revenue_amount').val(response.message['amount']);
                            $('#expiry_at').val(moment(response.message['expiry_at']).format('DD-MM-YYYY'));
                        }
                    }
                });
            
        });
        $('.customdate').datetimepicker({
        format: 'DD-MM-YYYY'
        });
	});
</script>
@endsection