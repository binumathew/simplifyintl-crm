@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Commission</h1>
					</div>
					<div class="Unavbg">
						<div class="row">
							<div class="col-md-8 col-sm-7">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Commission</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
								</ul>
							</div>
						</div>
					</div>   
					@csrf
					<div class="Paymentsbg ctablebg clearfix">
						<div class="panel panel-default">
    					    <div class="panel-heading">
    					        <h3 class="panel-title">Filter</h3>
    					    </div>
    					    <div class="panel-body">
    					        <form method="POST" id="comm-search-form" class="form-inline" role="form">
    					           <div class="form-group">
                                            <label for="number">From</label>
                                            <input type="text" class="form-control customdate" name="from_date" id="from_date" placeholder="From">
                                    </div>
                                    <div class="form-group">
                                            <label for="number">TO</label>
                                            <input type="text" class="form-control customdate" name="to_date" id="to_date" placeholder="To">
                                    </div>

    					            <button type="submit" class="btn btn-primary">Search</button>
    					        </form>
    					    </div>
    					</div>
						<div class="row">
							<div class="col-md-12">
								<table id="commissiontable" class="display responsive no-wrap" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Name</th>										
											<th>Plan Name</th>
											<th>Total Commission</th>
											<th>Commission Paid</th>
											<th>Commission to Pay</th>	
											<th>Created at</th>								
											<th>Action</th>
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
                url: 'comm-user-details',
                data: function (d) {
                	d.userid  = <?php echo $userid;?>;
                    d.promocode  = $('input[name=promocode]').val();
                    d.from    	= $('input[name=from_date]').val();
                    d.to      = $('input[name=to_date]').val();
                }
            },
            columns: [
                //{data: 'id', name: 'id'},
                {data: 'dealer_name', name: 'dealer_name'},
                {data: 'planname', name: 'planname'},
                {data: 'total_amount', name: 'total_amount'},
                {data: 'paid_amount', name: 'paid_amount'},
                {data: 'pay_amount', name: 'pay_amount'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action'},
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
		$('#comm-search-form').on('submit', function(e) {
            commTable.draw();
            e.preventDefault();
        });
		$('.customdate').datetimepicker({
        format: 'DD-MM-YYYY'
        });

		// $('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		// $('.dataTables_filter input').attr("placeholder", "Search");
		// setTimeout(function() {
		// 	$('#success').fadeOut('fast');
		// }, 5000);
	});
</script>
@endsection