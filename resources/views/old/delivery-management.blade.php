@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="Cleftpart">
					<h1>Delivery List</h1>
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

					</div>
				</div>   
				@csrf
				<div class="Paymentsbg ctablebg clearfix">
					<div class="row">
						<div class="col-md-12">
							<select id="delvry_filtr_type" class="datatableFilter">
								<option value="1">Requested</option>
								<option value="2">Packed/Shipped</option>
								<option value="3">All</option>
							</select>
							<table id="deliveryListTbl" class="display responsive no-wrap deliveryListTbl" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th class="tbl_tick_bx">#</th>
										<th>Order Id</th>
										<th>Customer</th>
										<th>Shipping Address</th>
										<th>SIM in Pack</th>
										<th>Agent</th>
										<th>Status</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
							</table>
							<div class="pull-right">
								<button type="button" id="bulk_pack" class="btn btn-success">Mark As Packed</button>
								<button type="button" id="print_slip" class="btn btn-warning">Print</button>
							</div>
							
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="delvryStatusModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close delvery_status_close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="delvryStatusModalTitle">Update Status</h3>
			</div>
			<div class="modal-body">
				<p>Are you sure want to update?</p>
				<input type="hidden" id="data_id" name="data_id">
				<input type="hidden" id="data_value" name="data_value">
				<input type="hidden" id="data_current" name="data_current">
				<div class="form-out-group displayNone" id="delivery_shiping_agent">
					<input type="text" name="shiping_agent" id="shiping_agent" class="form-input" placeholder="Shipping Agent Name">
				</div>

				<div class="popup_btn_holder">
					<button type="button" class="btn btn-default delvery_status_close" data-dismiss="modal">No</button>
					<button type="button" id="delvery_status_btn" class="btn btn-success">Yes</button>
				</div>
				<div id="delvery_popError" class="alert alert-danger displayNone">Please enter shipping agent name</div>
			</div>
		</div>

	</div>
</div>

<div id="bulkShipModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="bulkShipModalTitle">Packed / Ship</h3>
			</div>
			<div class="modal-body">
				<p>Please enter shipping agent</p>
				<div class="form-out-group">
					<input type="text" name="bulk_shiping_agent" id="bulk_shiping_agent" class="form-input" placeholder="Shipping Agent Name">
				</div>

				<div class="popup_btn_holder">
					<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					<button type="button" id="bulk_ship_btn" class="btn btn-success">Yes</button>
				</div>
				<div id="bulk_ship_Error" class="alert alert-danger displayNone">Please enter shipping agent name</div>
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

<div id="delvryPrintModal" class="modal fade" role="dialog">
	<div class="modal-dialog mt-7p modal-lg">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="delvryPrintModalTitle"></h3>
			</div>
			<div class="modal-body">
				<div id="delvryPrint_hldr"></div>
			</div>
		</div>

	</div>
</div>

<div id="adrsChangeModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="adrsChangeModalTitle">Update Shipping Address</h3>
			</div>
			<div class="modal-body form-container">
				<input type="hidden" name="adrs_simlist_id" id="adrs_simlist_id">
				<div class="pcode clearfix">
					<span>
						<input type="text" id="delivery_c_postalcode" name="postal_code" placeholder="Postal Code" class="valid">
						<div id="delivery_c_postalcode-error" class="invalid form-error" style="display: none;"></div>
					</span>
					<span>
						<input type="text" id="delivery_c_house_no" name="house_no" placeholder="House No">
					</span>
					<a class="faddress delivery_find_address">Find Address</a>
				</div>
				<div class="displayNone" id="adrs_find_error">
					<div class="invalid form-error" id="adrs_find_error-message"></div>
				</div>
				<p class="displayNone" id="adrs_select_holder">
					<select id="delvry_selt_box"></select>
				</p>
				<p>
					<input type="text" id="delivery_c_address" name="delivery_c_address" placeholder="Billing Address">
				</p>

				<button type="button" id="delvry_adrs_updt_btn" class="btn btn-success">Update</button>

			</div>
		</div>

	</div>
</div>

<div id="SimDuplicateModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title">Order Duplicate</h3>
			</div>
			<div class="modal-body form-container">
				<input type="hidden" name="simlist_id" id="simlist_id">
				<p>
					<input type="text" id="new_phone_number" name="new_phone_number" placeholder="Phone Number / Sim Number">
				</p><br/>
				<div id="order_duplicate_hldr"></div>
				<span>	
				   <button type="button" id="update_order" class="btn btn-primary">Update</button>
				</span>	
			</div>
		</div>

	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){

		$('#deliveryListTbl').DataTable({
			responsive: true,
			"bSort" : true,
			language: { search: "" },
			processing: true,
			serverSide: true,
			"ajax": {
				"url": "pagination",
				
				"data": function ( d ) {
					d.filter_type = $('#delvry_filtr_type').val();
				}
			},
			
			"dataType": "jsonp",
			"columns": [
			{"data": function(data){
				return '<input type="checkbox" name="request_id[]" value="'+ data.id +'" class="request_list_chkbx">';
			}, "orderable": false, "searchable": false, "name":"_id" },
			{"data": "order_id", "name": "rq.order_id"},
			{"data": "name", "name": "usr.name"},
			{"data": function(data){
				var htmlDecode =$.parseHTML(data.shipping_address)[0]['wholeText'];
				var address = JSON.parse(htmlDecode);
				return address.street+', '+address.city+', '+address.country+', '+address.postal_code.toUpperCase();
			}, "name": "rq.shipping_address"},
			{"data" : "sim_count","name":"sim_count","orderable": false, "searchable": false},
			{"data" : "role","name":"role","orderable": false, "searchable": false},
			{"data": function ( data ) {
				switch(data.delivery_status){
					case 0:
					return '<select id="delivery_itm_status_'+data.id+'" class="delivery_itm_status" data-id="'+ data.id +'" data-value="'+ data.delivery_status +'"><option value="0" selected="selected">Request Received</option><option value="1">Packed / Shipped</option></select>';
					break;
					case '1':
					return '<select id="delivery_itm_status_'+data.id+'" class="delivery_itm_status" data-id="'+ data.id +'" data-value="'+ data.delivery_status +'"><option value="0">Request Received</option><option value="1" selected="selected">Packed / Shipped</option></select>';
					break;
					case '2':
					return 'Callback';
					break;
					case '3':
					return 'Activated';
					break;
				}

			}, "name": "rq.delivery_status", "searchable": false},
			{"data": function(data){
				var html_data = '<a data-toggle="tooltip" title="View Status" href="#" class="fa fa-eye view_history" data-id="'+data.id+'"></a><a data-toggle="tooltip" title="Enquiry History" href="#" class="fa fa-history call_history" data-id="'+data.id+'"></a>';
				//if (data.delivery_status == 0) {
					html_data = html_data+'<a data-toggle="tooltip" title="Sim Details" href="#" class="fa fa-info-circle sim_details sim_detail_btn_'+data.id+'" data-id="'+data.id+'"></a>';
				//}
				if (data.print_status == 0) {
					html_data = html_data+'<a data-toggle="tooltip" title="Print" href="#" class="fa fa-print print_sim_note" data-id="'+data.id+'"></a>';
				}
				return html_data;

			}, "name": "action","orderable": false, "searchable": false},
			],
			"columnDefs": [
			{ "targets": 6,"width": '110px'},
			{"defaultContent": "-","targets": "_all"}
			],
		});

		$('.dataTables_filter input').attr("placeholder", "Search");
	});

	$(document).on('change','.delivery_itm_status',function () {
		var that = $(this);
		var id = $(this).attr('data-id');
		var value = $(this).val();
		var current_val = $(this).attr('data-value'); 
		if (value != parseInt(current_val)+1) {
			that.val(current_val);
			swal("Error", 'You are not allowed to do this action', "error");
		} else { 
			$('#data_id').val(id);
			$('#data_value').val(value);
			$('#data_current').val(current_val);
			$("#delvery_popError").hide(); 
			$('#delvryStatusModal').modal('show');
			if (value == 1) {
				$('#delivery_shiping_agent').show();
			}
		}
	});

	$(document).on('click','#delvery_status_btn',function (e) { 
		var id = $('#data_id').val();
		var value = $('#data_value').val();
		var current = $('#data_current').val();
		var agent = $('#shiping_agent').val();
		if ((value == 1) && (agent == "")) {
			$('#delvery_popError').show();
		} else {
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'update-delivery-status',
				data: {id:id,value:value,agent:agent},
				success:function(data){	
					$('.sim_detail_btn_'+id).hide();							
					$('#delvryStatusModal').modal('hide');
					$('#deliveryListTbl').DataTable().ajax.reload();
				}
			});
		}
	});

	$(document).on('change','#delvry_filtr_type',function () {
		$('#deliveryListTbl').DataTable().draw();
	});

	$(document).on('click','.delvery_status_close',function (e) {
		var id = $('#data_id').val();
		var current = $('#data_current').val(); 
		$('#delivery_itm_status_'+id).val(current);
		$('#data_id').val('');
		$('#data_value').val('');
		$('#data_current').val('');
	});

	$(document).on('click','.view_history',function (e) {
		var id = $(this).attr('data-id');
		$('#dlvry_status_error').html('');
		$('#dlvry_status_error').hide();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',                                                
			url: 'show-delivery-status',
			data: {id:id},
			success:function(data){	
				if (data.error) {
					$('#delivery_status_hldr').html('<div class="alert alert-danger">'+data.message+'</div>');
				} else {
					$('#delivery_status_hldr').html(data.html);
				}
				$('#delvryProgressModalTitle').text('Status')
				$('#delvryProgressModal').modal('show');
			}
		});
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
			url: 'enquiry-history',
			data: {id:id},
			success:function(data){	
				if (data.error) {
					$('#delivery_status_hldr').html('<div class="alert alert-danger">'+data.message+'</div>');
				} else {
					$('#delivery_status_hldr').html(data.html);
				}
				$('#delvryProgressModalTitle').text('Enquiry History')
				$('#delvryProgressModal').modal('show');
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

	$(document).on('click','#print_slip',function () { 
		var chkArray = [];
		$(".request_list_chkbx:checked").each(function() {
			chkArray.push($(this).val());
		});

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',                                                
			url: 'print-address',
			data: {selected:chkArray},
			success:function(data){	
				if (data.error) {
					$('#delvryPrint_hldr').html('<div class="alert alert-danger">'+data.message+'</div>');
					$('#delvryPrintModalTitle').text('Print')
					$('#delvryPrintModal').modal('show');
				} else {
					$('#delvryPrint_hldr').html(data.html);
					$('#delvryPrintModalTitle').text('Print')
					$('#delvryPrintModal').modal('show');
					//document.title = 'default_filename';
					$.print(".printableArea");
					// $('#delvryPrintModal').modal('hide');
				}
			}
		});
	});

	$(document).on('click','.print_sim_note',function (e) {
		var chkArray = [];
		chkArray.push($(this).attr('data-id'));
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',                                                
			url: 'print-address',
			data: {selected:chkArray},
			success:function(data){	
				if (data.error) {
					$('#delvryPrint_hldr').html('<div class="alert alert-danger">'+data.message+'</div>');
					$('#delvryPrintModalTitle').text('Print')
					$('#delvryPrintModal').modal('show');
				} else {
					$('#delvryPrint_hldr').html(data.html);
					$('#delvryPrintModalTitle').text('Print')
					$('#delvryPrintModal').modal('show');

					$.print(".printableArea");
					$('#delvryPrintModal').modal('hide');
				}
			}
		});
	})

	$(document).on('click','.sim_details',function (e) {
		var id = $(this).attr('data-id');
		$('#dlvry_status_error').html('');
		$('#dlvry_status_error').hide();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',                                                
			url: 'sim-details',
			data: {id:id},
			success:function(data){	
				if (data.error) {
					$('#delivery_status_hldr').html('<div class="alert alert-danger">'+data.message+'</div>');
				} else {
					$('#delivery_status_hldr').html(data.html);
				}
				$('#delvryProgressModalTitle').text('SIM Details')
				$('#delvryProgressModal').modal('show');
			}
		});

	});

	$(document).on('click','.delivery_find_address',function (e) {
		var postal_code = $('#delivery_c_postalcode').val();
		var house_no = $('#delivery_c_house_no').val();
		if (postal_code == '') {
			$('#delivery_c_postalcode-error').text('Please enter postal code');
			$('#delivery_c_postalcode-error').show();
			setTimeout(function(){
				$('#delivery_c_postalcode-error').hide();
			}, 3000);
		} else {
			$('#loadingsign').show();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'get-address',
				data: {postal_code:postal_code,house_no:house_no},
				success:function(data){	
					$('#loadingsign').hide();
					if (data.error) {
						if (data.type == 1) {
							$('#adrs_select_holder').show();
							$('#delvry_selt_box').html(data.list);
							$('#adrs_find_error').show();
							$('#adrs_find_error-message').text(data.message);
						} else if (data.type == 1) {
							$('#adrs_select_holder').hide();
							$('#adrs_find_error').show();
							$('#adrs_find_error-message').text(data.message);
						}
					} else {
						$('#adrs_select_holder').hide();
						$('#adrs_find_error').hide();
						$('#delivery_c_address').val(data.address);
					}
				}
			});
		}
	});

	$(document).on('change','#delvry_selt_box',function () {
		$('#delivery_c_address').val($(this).val());
	});
	$(document).on('click','#delvry_adrs_updt_btn',function () {
		var id = $('#adrs_simlist_id').val();
		var address = $('#delivery_c_address').val();
		if (address == '') {
			$('#adrs_find_error').show();
			$('#adrs_find_error-message').text('Billing address cannot be blank');
			setTimeout(function(){
				$('#adrs_find_error').hide();
			}, 5000);
		}

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',                                                
			url: 'update-shipping-address',
			data: {id:id,address:address},
			success:function(data){	
				if (data.error) {
					$('#adrs_find_error').show();
					$('#adrs_find_error-message').text(data.message);
					setTimeout(function(){
						$('#adrs_find_error').hide();
					}, 5000);
				} else {
					location.reload();
				}
			}
		});
	});

	$(document).on('click','.updt_adrs',function (e) {
		var id = $(this).attr('data-id');
		$('#adrs_simlist_id').val(id);
		$('#adrsChangeModal').modal('show');
	});

	$(document).on('click','.order_duplicate',function (e) {
		var id = $(this).attr('data-id');
		$('#simlist_id').val(id);
		$('#SimDuplicateModal').modal('show');
	});

	$(document).on('click','#update_order',function (e) {
		var simId = $('#simlist_id').val();
		var phone = $('#new_phone_number').val();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',                                                
			url: 'order-update', 
			data: {sim_id:simId,phone:phone},
			success:function(data){	
				if (data.error) {
					$('#order_duplicate_hldr').html('<div class="alert alert-danger">'+data.message+'</div>');
				} else {
					$('#SimDuplicateModal').modal('hide');
					$('#delvryProgressModal').modal('hide');
					$('#deliveryListTbl').DataTable().ajax.reload();
				}
			}
		});

	});	

	$(document).on('click','#bulk_pack',function (e) {
		var chkArray = [];
		$(".request_list_chkbx:checked").each(function() {
			chkArray.push($(this).val());
		});
		if (chkArray == '') {
			$('#delvryPrint_hldr').html('<div class="alert alert-danger">Please select atleast one address to ship</div>');
			$('#delvryPrintModalTitle').text('Print')
			$('#delvryPrintModal').modal('show');
		} else {
			$('#bulk_ship_Error').hide();
			$('#bulkShipModalTitle').text('Bulk Ship')
			$('#bulkShipModal').modal('show');
		}
	})

	$(document).on('click','#bulk_ship_btn',function (e) { 
		var chkArray1 = [];
		$(".request_list_chkbx:checked").each(function() {
			chkArray1.push($(this).val());
		}); 
		if (chkArray1 == '') {
			$('#delvryPrint_hldr').html('<div class="alert alert-danger">Please select atleast one address to ship</div>');
			$('#delvryPrintModalTitle').text('Print')
			$('#delvryPrintModal').modal('show');
		} else {
			var agent_name = $('#bulk_shiping_agent').val(); 
			if (agent_name == "") {
				$('#bulk_ship_Error').show();
			} else {
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',                                                
					url: 'bulk-ship',
					data: {selected:chkArray1,agent:agent_name},
					success:function(data){	
						if (data.error) {
							$('#delvryPrint_hldr').html('<div class="alert alert-danger">'+data.message+'</div>');
							$('#delvryPrintModalTitle').text('Shipping')
							$('#delvryPrintModal').modal('show');
						} else {
							$('#bulkShipModal').modal('hide');
							$('#bulk_shiping_agent').val(''); 
							$('#delvryPrint_hldr').html('<div class="alert alert-success">Updated Success fully</div>');
							$('#delvryPrintModalTitle').text('Shipping')
							$('#delvryPrintModal').modal('show');
							$('#deliveryListTbl').DataTable().ajax.reload();
						}
					}
				});
			}
		}
		
	});

	$(document).on('click','#sim_detail_print',function () {
		$.print(".simDetail_content");
	})
</script>
@endsection