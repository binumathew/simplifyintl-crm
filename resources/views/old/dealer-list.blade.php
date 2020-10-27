@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Dealers</h1>
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
									<li class="active"><a href="">Dealers</a></li>
								</ul>
							</div>
							<div class="col-md-2 col-sm-5">
							<ul class="EAbtnbg">								
								<li><a href="dealer">Add Dealer</a></li>
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
											<th>Phone&nbsp;No</th>
											<th>Customer Id</th>				
											<th>Action</th>
										</tr>
									</thead>
									<tbody>						
										@foreach ($dealers as $dealer)
										<tr>
											<td>{{$dealer->first_name}} {{$dealer->last_name}}</td>
											<td>{{$dealer->email}}</td>
											<td>{{$dealer->telephone}}</td>
											<td>{{$dealer->customer_id}}</td>
											<td>										
												<a data-toggle="tooltip" title="Edit"  href="{{ url('dealer', [$dealer->id]) }}" class="fa fa-pencil-square-o editbtn"></a>
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

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);
	});
</script>
@endsection