@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>White Listed IP</h1>
						<!--<span>Home  |  My Avoo  | Contacts </span> -->
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
							<div class="col-md-10 col-sm-7">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="#">Firewall</a></li>
									
								</ul>
							</div>
							<div class="col-md-2 col-sm-5">
							<ul class="EAbtnbg">								
								<li><a href="#" id="add_firewall">ADD&nbsp;IP</a></li>
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
											<th>#</th>
											<th>IP Address</th>										
											<th>Description</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>		
										@php $i = 0; @endphp				
										@foreach ($firewall as $ip)
										<tr id="ip-row{{$ip->id}}">
											<td>{{ ++$i }}</td>
											<td id="address_{{$ip->id}}">{{$ip->ip_address}}</td>	
											<td id="desc_{{$ip->id}}">{{$ip->description}}</td>		
											<td>												
												<a data-toggle="tooltip" title="Edit"  href="#" class="fa fa-pencil-square-o editbtn edit_firewall" data-list="{{$ip->id}}"></a>
												<a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_firewall" data-list="{{$ip->id}}"></a>
											</td>
										</tr>										
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>		
	</div>

	<div id="firewallpopup" class="modal fade avoopopup" role="dialog">
		<div class="modal-dialog mt-7p">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title" id="firewalltitle">White Listed IP</h3>
				</div>
				<div class="modal-body">
				<form action="{{ url('/save-firewall') }}" method="post">
					@csrf
					<div class="form-group">
						<label>IP Address</label>
						<input type="hidden" id="whitelist_id" name="id">
						<input type="text" id="ip_address" class="form-control" name="ip_address">
					</div>
					<div class="form-group">
						<label>Descrtiption</label>									
						<input type="text" id="description" class="form-control" name="description">
					</div>		
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-success" id="save_whitelist">Submit</button>
				</form>
				</div>
			</div>
		</div>
	</div>

	<div id="firewallConfirmPopup" class="modal fade avoopopup" role="dialog">
		<div class="modal-dialog mt-7p">

			<!-- Modal content-->
			<div class="modal-content">
			<form action="{{ url('/delete-firewall') }}" method="post">
				@csrf
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title" >Confirm Delete!</h3>
				</div>
				<div class="modal-body">
					<p>Are you sure you want to delete this item?</p>
					<input type="hidden" id="firewall_id" name="id">
					<div class="popup_btn_holder">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="submit"  class="btn btn-danger">Delete</button>
					</div>
				</div>
			</form>
			</div>

		</div>
	</div>

	<script type="text/javascript">
	$(document).ready(function(){
		$('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		$('.dataTables_filter input').attr("placeholder", "Search");
		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);

		$(document).on('click','.edit_firewall',function () {
			var id = $(this).data('list');
			$('#whitelist_id').val(id);
			$('#firewalltitle').html('Edit White Listed IP');
			$('#ip_address').val($('#address_'+id).html());
			$('#description').val($('#desc_'+id).html());
			$('#firewallpopup').modal('show');
		});

		$(document).on('click','#add_firewall',function () {
			$('#whitelist_id').val('');
			$('#firewalltitle').html('Add White Listed IP');
			$('#ip_address').val('');
			$('#description').val('');
			$('#firewallpopup').modal('show');
		});

		$(document).on('click','.delete_firewall',function () {
			var id = $(this).data('list');
			$('#firewall_id').val(id);
			$('#firewallConfirmPopup').modal('show');
		});
	});
</script>
@endsection