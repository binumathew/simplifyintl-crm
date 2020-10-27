@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="Cleftpart">
					<h1>Orders</h1>
				</div>   
				@csrf
				<div class="Paymentsbg ctablebg clearfix">
					<div class="row">
						<div class="col-md-12">
							<select id="orders_filtr_type" class="datatableFilter">
								<option value="1">Ready To Activate</option>
								<option value="2">Ready for Welcome Call</option>
								<option value="3">Not Packed</option>
								<option value="4">All</option>
							</select>
							<table id="orderListTbl" class="display responsive no-wrap" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>Order Id</th>
										<th>Order Date</th>
										<th>Customer</th>
										<th>Contact Number</th>
										<th>Shipping Date</th>
										<th>SIM in Pack</th>
										<th>Agent</th>
										<th>Status</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="delvryProgressModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="delvryProgressModalTitle"></h3>
			</div>
			<div class="modal-body">
				<div id="delivery_status_hldr"></div>
				<ul id="dlvry_status_error" class="alert alert-danger displayNone"></ul>
			</div>
		</div>

	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){

		$('#orderListTbl').DataTable({
			responsive: true,
			"bSort" : true,
			language: { search: "" },
			processing: true,
			serverSide: true,
			"ajax": {
				"url": "orders-pagination",
				"data": function ( d ) {
					d.filter_type = $('#orders_filtr_type').val();
				}
			},
			
			"dataType": "jsonp",
			"columns": [
			{"data": "order_id", "name": "rq.order_id"},
			{"data" : function (data) {
				return moment(data.date).format('DD-MMM-YYYY');
			},"name" : "order_date","orderable": false, "searchable": false},
			{"data": "name", "name": "usr.name"},
			{"data" : function (data) {
				var phone = data.phone;
				return phone.replace("+44", "0");
			}, "name": "usr.phone","orderable": false, "searchable": false},
			{"data" : function (data) {
				if(data.ship_date)
					return moment(data.ship_date).format('DD-MMM-YYYY H:mm:ss');
				else
					return '-';
			},"name" : "ship_date","orderable": false, "searchable": false},
			{"data" : "sim_count","name":"sim_count","orderable": false, "searchable": false},
			{"data" : function (data) {
				return (data.promocode)?data.promocode:'SJ100';					
			},"name" : "rq.promocode"},
			{"data" : function (data) {
				if (data.delivery_status == 0) {
					return 'Order Received';
				} else if (data.delivery_status == 1) {
					return 'Ready to activate';
				} else if (data.delivery_status == 2) {
					return 'Active-CallBack pending';
				} else if (data.delivery_status == 3) {
					return 'Active';
				}
			}, "name": "rq.delivery_status","orderable": false, "searchable": false},
			{"data": function(data){
				var route = "{{URL::to('sim-activate')}}";
				var html = '<form method="post" action="'+route+'">@csrf<input type="hidden" name="order_id" value="'+data.order_id+'"><button type="submit" class="btn btn-success btn-xs" title="View Details"><i class="fa fa-eye"></i></button><a data-toggle="tooltip" title="Enquiry History" href="#" class="btn btn-info btn-xs fa fa-sticky-note call_history" data-id="'+data.id+'"></a></form>';
				if ({{ Auth::id() }} == 1) {
					html+= '<a data-toggle="tooltip" title="Manage Promocode" href="#" class="btn btn-info btn-xs fa fa-id-badge manage_promocode" data-id="'+data.id+'"></a>';
				}
				return  html;
			}, "name": "action","orderable": false, "searchable": false},
			],
			"columnDefs": [
			{ "targets": 1,"width": '80px'},
			{"defaultContent": "-","targets": "_all"}
			],
		});

		$('.dataTables_filter input').attr("placeholder", "Search");
	});

	$(document).on('change','#orders_filtr_type',function () {
		$('#orderListTbl').DataTable().draw();
	});

	$(document).on('click','.call_history',function (e) {
		var id = $(this).attr('data-id');
		$('#dlvry_status_error').html('');
		$('#dlvry_status_error').hide();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',                                                
			url: base_url+'/enquiry-history',
			data: {id:id},
			success:function(data){	
				if (data.error) {
					$('#delivery_status_hldr').html('<div class="alert alert-danger">'+data.message+'</div>');
				} else {
					$('#delivery_status_hldr').html(data.html);
				}
				$('#delvryProgressModalTitle').text('Enquiry History');
				$('#delvryProgressModal').modal('show');
			}
		});
	});

	$(document).on('click','.manage_promocode',function (e) {
		var id = $(this).attr('data-id');		
		var html = '<form method="post">@csrf<input type="hidden" id="order_id" name="order_id" value="'+id+'"><input type="text" name="promocode" placeholder="Promocode" class="form-control"><br/><a class="btn btn-success update_promo_code">UPDATE</a></form>';
		$('#delivery_status_hldr').html(html);
		$('#delvryProgressModalTitle').text('Manage Promocode');
		$('#delvryProgressModal').modal('show');
	});
	
	$(document).on('click','.update_promo_code',function (e) {	
		var order_id = $('#order_id').val();
		var promocode = $('input[name="promocode"]').val();	
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',                                                
			url: base_url+'/update-promocde',
			data: {order_id:order_id,promocode:promocode},
			success:function(data){					
				$('#delvryProgressModal').modal('hide');
			}
		});
	});

	$(document).on('click','#save_enq_history',function (e) {		
		var id = $('#enq_request_id').val();
		var note = $('#enquiry_note').val();
		$('#dlvry_status_error').html('');
		$('#dlvry_status_error').hide();
		if (note == "") {
			
			$('#dlvry_status_error').append('<li>Please add a note</li>');
			$('#dlvry_status_error').show();
		} else {
			$('#loadingsign').show();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'save-enquiry-detail',
				data: {id:id,note:note},
				success:function(data){	
					$('#loadingsign').hide();
					if (data.error) {
						$('#delivery_status_hldr').html('<div class="alert alert-danger">'+data.message+'</div>');
					} else {
						$('#delivery_status_hldr').html('<div class="alert alert-success">Updated successfully</div>');
						$('#dlvry_status_error').html('');
						$('#dlvry_status_error').hide();
					}

				}
			});
		}

	});
</script>
@endsection