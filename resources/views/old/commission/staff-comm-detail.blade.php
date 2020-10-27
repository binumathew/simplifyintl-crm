@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Staff Commission</h1>
					</div>
					<div class="Unavbg">
						<div class="row">
							<div class="col-md-8 col-sm-7">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Staff Commission</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
								</ul>
							</div>
						</div>
					</div>   
					<div class="Paymentsbg ctablebg clearfix">
						<div class="panel panel-default">
    					    <div class="panel-heading">
    					        <h3 class="panel-title">Filter</h3>
    					    </div>
    					    <div class="panel-body">
    					        <form method="POST" action="{{ url('comm-staff-details') }}" id="comm-search-form" class="form-inline" role="form">
    					        @csrf
    					        <div class="form-group">
                                    <label for="pwd">Name</label>
                                    <select name="staff" class="form-control">
                                        <option value="-1">Pick option</option>
                                        @foreach ($staff as $staffs)
                                        <option value="{{ $staffs->id }}">{{ $staffs->first_name.' '.$staffs->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
    					           <div class="form-group">
                                            <label for="number">From</label>
                                            <input type="text" class="form-control customdate" name="from" id="from" placeholder="From">
                                    </div>
                                    <div class="form-group">
                                            <label for="number">TO</label>
                                            <input type="text" class="form-control customdate" name="to" id="to" placeholder="To">
                                    </div>

    					       	<div class="form-group">
                                    <button type="button" id="searchBtn" class="btn btn-primary">Search</button>
                                </div> 
                                <div class="form-group">
                                	<input type="hidden" name="userid" id="userid" value="{{ $userid }}">
                                    <button type="submit" class="btn btn-info pull-right" id="export" name="exportdata" value="1">Export</button>
                                </div>
    					        </form>
    					    </div>
    					</div>
						<div class="row">
							<div class="col-md-12">
								<table id="commissiontable" class="display responsive no-wrap" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Name</th>										
											<th>User Name</th>
											<th>Commission</th>
											<th>Commission Paid</th>
											<th>Commission to Pay</th>	
											<th>Commission Type</th>
											<th>Created at</th>						
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Modal -->
  <div class="modal fade" id="commDialog" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Commission</h4>
        </div>
        <div class="modal-body" id="comm-details">
        </div>
      </div>
      
    </div>
  </div>

	<script type="text/javascript">
	$(document).ready(function(){
		var commTable = $('#commissiontable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            searching: false,
            "bSort" : false,
            dom :'Blfrtip',
            ajax: {
                url: 'comm-staff-details',
                data: function (d) {
                	d.userid  	= $('input[name=userid]').val()
                    d.staff     = $('select[name=staff]').val();
                    d.from    	= $('input[name=from]').val();
                    d.to        = $('input[name=to]').val();
                }
            },
            columns: [
                //{data: 'id', name: 'id'},
                {data: 'staff_name', name: 'staff_name'},
                {data: 'phonenumber', name: 'phonenumber'},
                {data: 'total_amount', name: 'total_amount'},
                {data: 'paid_amount', name: 'paid_amount'},
                {data: 'pay_amount', name: 'pay_amount'},
                {data: 'comm_for', name: 'comm_for'},
                {data: 'date', name: 'date'},
            ],
            buttons: [
            	
    		],
        });
        $(document).on("click", ".comm-breakdown", function () {
			var datas = $(this).data('id');
			
			$.ajax({
				type:"POST",
				url:'comm-breakdown',
				headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
				data:{datas:datas},
				success:function(data){
					$('#comm-details').html(data);
					$("#commDialog").modal('show');
				}
			})
		});

		$('.customdate').datetimepicker({
        format: 'DD-MM-YYYY'
        });

		$('#searchBtn').on('click', function(e) {
        commTable.draw();
        e.preventDefault();
	    });

	    $('#comm-search-form').on('submit', function(e) {
	       $(this).submit();
	    });
		// $('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		// $('.dataTables_filter input').attr("placeholder", "Search");
		// setTimeout(function() {
		// 	$('#success').fadeOut('fast');
		// }, 5000);
	});
</script>
@endsection