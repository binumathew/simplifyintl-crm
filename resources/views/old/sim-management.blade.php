@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<!-- <div class="Cleftpart">
						<h1>Sim Stock</h1>
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
							<div class="col-md-8 col-sm-7">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Sim Stock</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
								</ul>
							</div>
							<div class="col-md-4 col-sm-5">
							<ul class="EAbtnbg">
								<li class="expo">
									<a href="#">IMPORT<i class=" fa fa-file-excel-o"></i></a>
								</li>
							<li><a href="{{url('/dealer-assign')}}">ASSIGN</a></li>
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
											<th>Phone Number</th>
											<th>Category</th>
											<th>Dealer</th>
											<th>Price</th>
											<th>Box Number</th>
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
		//$('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		$('.dataTables_filter input').attr("placeholder", "Search");

		$('#contacttable').DataTable({
			responsive: true,
			"bSort" : false,
			language: { search: "" },
			processing: true,
			serverSide: true,
			"ajax": 'sim-pagination',
			"dataType": "jsonp",
			"columns": [
			{"data": "phone_number", "name": "ts.phone_number"},
			{"data": "category", "name": "ts.category"},
			{"data": "fullname", "name": "a.first_name"},
			{"data": "price", "name": "ts.price"},
			{"data": "box_no", "name": "ts.box_no"},			
			{"data": function ( data ) {				
				return (data.status == '1')?'Active':'Sold';
			}, "name": "ts.status"},
			{"data": function ( data ) {

				var parameter = "<?= Crypt::encrypt("+data.id+"); ?>";

				return '<a data-toggle="tooltip" title="Edit"  href="editcontact/'+parameter+'" class="fa fa-pencil-square-o editbtn"></a><a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_contact"  user-id="'+parameter+'" ></a>';
				
			}, "name": "action","orderable": false, "searchable": false},
			],
			"columnDefs": [{
				"defaultContent": "-",
				"targets": "_all"
			}],
		});

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);
	});
</script>
@endsection