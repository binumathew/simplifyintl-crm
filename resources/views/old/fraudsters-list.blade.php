@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<!-- <div class="Cleftpart">
						<h1>Fraudsters</h1>
						<span>Home  |  My Avoo  | Contacts </span> 
					</div> -->
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
							<div class="col-md-12 col-sm-7">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Fraudsters Users</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
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
											<th>Name</th>											
											<th>Email</th>
											<th>Created Date</th>
											<th>Phone&nbsp;No</th>
											<!-- <th>Status</th> -->
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($fraudsters as $user)
										<tr id="user_{{$user->id}}">
										<td>{{$user->name}}</td>							
										<td>{{$user->email}}</td>
										<td>{{$user->created_at}}</td>
										<td>{{$user->phone}}</td>
										<td>
										@php
										$parameter= Crypt::encrypt($user->id);
										@endphp
										<!-- <a data-toggle="tooltip" title="Edit"  href="#" class="fa fa-pencil-square-o editbtn"></a> -->
										<a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_fraudster" data-list_id={{$user->id}}></a>
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
		<div class="modal hide" id="addBookDialog">
			<div class="modal-header">
				<button class="close" data-dismiss="modal">×</button>
				<h3>Modal header</h3>
			</div>
			<div class="modal-body">
				<p>some content</p>
				<input type="text" name="bookId" id="bookId" value=""/>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	$(document).ready(function(){
		$('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		$('.dataTables_filter input').attr("placeholder", "Search");

		$(document).on("click", ".delete_fraudster", function () {
			var $this = $(this);
			if(confirm("Do you really want to delete this contact ?")){
				var id = $this.data('list_id');
				$.ajax({
					type:"POST",
					url:base_url+'/delete-fraudster',
					headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
					data:{id:id},
					success:function(){
						$('#user_'+id).fadeOut(1000);
					}
				})
			}
		});

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);
	});
</script>
@endsection