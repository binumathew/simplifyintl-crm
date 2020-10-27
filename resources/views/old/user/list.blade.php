@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
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
								<li class="active"><a href="">Users</a></li>
								<li><a href="{{ url('/temp-user') }}">Failed Login</a></li>
							</ul>
						</div>
						<div class="col-md-2 col-sm-5">
							<ul class="EAbtnbg">
								<li class="expo"></li>
								<li></li>
							</ul>
						</div>
					</div>
				</div>   
			
				<!-- <th>Switch</th> -->
				<!-- <th>Auth Name</th> -->
				<div class="Paymentsbg ctablebg clearfix">
					<div class="row">
						<div class="col-md-12">
							<table id="contacttable" class="display responsive no-wrap list-table" cellspacing="0" style="width:100%;">
								<thead>
									<tr>
										<th>Name</th>									
										<th>Email</th>										
										<th>Phone No</th>										
										<th>Platform</th>
										<th>Status</th>
										<th>Created On</th>										
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
<script type="text/javascript">
	$(document).ready(function(){

		$('#contacttable').DataTable({
			responsive: true,
			"bSort" : true,
			language: { search: "" },
			processing: true,
			serverSide: true,
			"ajax": {
				"url": "list-users",				
			},
			
			"dataType": "jsonp",
			"columns": [
			{"data": "name", "name": "name"},
			{"data": "email", "name": "email"},					
			{"data" : function (data) {
				var phone = data.phone;
				return phone.replace("+44", "0");
			}, "name": "phone"},			
			{"data" : "user_platform","name" : "ud.user_platform"},
			{"data" : function (data) {
				return (data.status)?'Active':'In-Active';			
			},"name" : "users.status","orderable": false, "searchable": false},
			{"data" : function (data) {
				return moment(data.created_at).format('DD-MMM-YYYY h:mm');
			},"name" : "created_at"},

			{"data": "action", "name": "action","orderable": false, "searchable": false},
			],
			"columnDefs": [
			{ "targets": 1,"width": '80px'},
			{"defaultContent": "-","targets": "_all"}
			],
		});

		$('.dataTables_filter input').attr("placeholder", "Search");

		$(document).on('click','.delete_user',function(){
          var id = $(this).attr('user-id'); 
          if(confirm("Do you really want to delete this contact ?")){
            $('#delete_user_'+id).submit();
          }
        });


		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);
	});
</script>

@endsection