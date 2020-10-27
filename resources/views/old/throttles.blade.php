@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="Cleftpart">
					<h1>Throttle List</h1>
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
							<input type="text" id="identifier_filter" placeholder="Email">
							<input type="text" id="ip_filter" placeholder="IP Address">
							<table id="throttleListTbl" class="display responsive no-wrap throttleListTbl" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th class="tbl_tick_bx">#</th>
										<th>Email</th>
										<th>IP Address</th>
										<th>Attempted On</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
							</table>
							<div class="pull-right">
								<button type="button" id="delete_throttle" class="btn btn-success">Delete</button>
							</div>
							
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="throttleDeleteModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close delvery_status_close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="delvryStatusModalTitle">Delete Throttle</h3>
			</div>
			<div class="modal-body">
				<p>Are you sure want to delete?</p>
				<input type="hidden" id="data_id" name="data_id">
				<input type="hidden" id="data_value" name="data_value">
				<input type="hidden" id="data_current" name="data_current">
				<div class="form-out-group displayNone" id="delivery_shiping_agent">
					<input type="text" name="shiping_agent" id="shiping_agent" class="form-input" placeholder="Shipping Agent Name">
				</div>

				<div class="popup_btn_holder">
					<button type="button" class="btn btn-default delvery_status_close" data-dismiss="modal">No</button>
					<button type="button" id="throttle_delete_btn" class="btn btn-success">Yes</button>
				</div>
			</div>
		</div>

	</div>
</div>

<div id="throttleMessageModal" class="modal fade" role="dialog">
	<div class="modal-dialog mt-7p modal-lg">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="delvryPrintModalTitle">Throttle</h3>
			</div>
			<div class="modal-body">
				<div id="throttlePrint_hldr"></div>
			</div>
		</div>

	</div>
</div>



<script type="text/javascript">
	$(document).ready(function(){

		$('#throttleListTbl').DataTable({
			responsive: true,
			"bSort" : true,
			language: { search: "" },
			processing: true,
			serverSide: true,
			"ajax": {
				"url": "throttle-list",
				
				"data": function ( d ) {
					d.email = $('#identifier_filter').val();
					d.ip = $('#ip_filter').val();
				}
			},
			
			"dataType": "jsonp",
			"columns": [
			{"data": function(data){
				return '<input type="checkbox" name="request_id[]" value="'+ data.id +'" class="request_list_chkbx">';
			}, "orderable": false, "searchable": false, "name":"id" },
			{"data": "identifier", "name": "identifier"},
			{"data": "ip_address", "name": "ip_address"},
			{"data": "attempted_at", "name": "attempted_at"},
			{"data": function(data){
				var html_data = '<a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_single_throttle" data-id="'+data.id+'" >';
				return html_data;

			}, "name": "action","orderable": false, "searchable": false},
			],
			/*"columnDefs": [
			{ "targets": 6,"width": '110px'},
			{"defaultContent": "-","targets": "_all"}
			],*/
		});

		$('.dataTables_filter input').attr("placeholder", "Search");
	});


	$(document).on('keyup','#identifier_filter',function () {
		$('#throttleListTbl').DataTable().draw();
	});

	$(document).on('keyup','#ip_filter',function () {
		$('#throttleListTbl').DataTable().draw();
	});

	var chkArray = [];

	$(document).on('click','#delete_throttle',function (e) {
		chkArray = [];
		$(".request_list_chkbx:checked").each(function() {
			chkArray.push($(this).val());
		});
		if (chkArray == '') {
			$('#throttlePrint_hldr').html('<div class="alert alert-danger">Please select atleast one entry to delete</div>');
			$('#throttleMessageModal').modal('show');
		} else {
			$('#throttleDeleteModal').modal('show');
		}
	});

	$(document).on('click','.delete_single_throttle',function (e) {
		chkArray = [];
		chkArray.push($(this).attr('data-id'));

		if (chkArray == '') {
			$('#throttlePrint_hldr').html('<div class="alert alert-danger">Please select atleast one entry to delete</div>');
			$('#throttleMessageModal').modal('show');
		} else {
			$('#throttleDeleteModal').modal('show');
		}
	})

	$(document).on('click','#throttle_delete_btn',function (e) {  
		if (chkArray == '') {
			$('#throttlePrint_hldr').html('<div class="alert alert-danger">Please select atleast one entry to delete</div>');
			$('#throttleMessageModal').modal('show');
		} else {
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: 'delete-throttle',
				data: {selected:chkArray},
				success:function(data){	
					if (data.error) {
						$('#throttlePrint_hldr').html('<div class="alert alert-danger">'+data.Message+'</div>');
						$('#throttleMessageModal').modal('show');
					} else {
						$('#throttleDeleteModal').modal('hide');
						$('#throttlePrint_hldr').html('<div class="alert alert-success">Deleted Successfully</div>');
						$('#throttleMessageModal').modal('show');
						$('#throttleListTbl').DataTable().ajax.reload();
					}
				}
			});
		}
		
	});

</script>
@endsection