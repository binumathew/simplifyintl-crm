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
									<li class="active"><a href="">Staff Commission</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
								</ul>
							</div>
							<div class="col-md-4 col-sm-5">
								<ul class="EAbtnbg">
									<!-- <li class="expo">
										<a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a>
									</li> -->
									<li><a href="add-staff-commission">ADD&nbsp;STAFF COMMISSION</a></li>
								</ul>
							</div>					
						</div>
					</div>   
					@csrf
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-12">
								<select id="role_filtr_type" class="datatableFilter">
									<option value="0">All</option>
									@foreach ($roles as $role)
										<option value="{{$role->id}}">{{$role->name}}</option>
									@endforeach
								</select>
								<table id="contacttable" class="display responsive no-wrap" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Role</th>											
											<th>Target From</th>	
											<th>Target To</th>
											<th>Commission Rate</th>														
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

	<script type="text/javascript">
	$(document).ready(function(){
		//$('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		//$('.dataTables_filter input').attr("placeholder", "Search");

		var orderTable = $('#contacttable').DataTable({
            // dom: "<'row'<'col-xs-12'<'col-xs-6'l><'col-xs-6'p>>r>"+
            //     "<'row'<'col-xs-12't>>"+
            //     "<'row'<'col-xs-12'<'col-xs-6'i><'col-xs-6'p>>>",
            processing: true,
            serverSide: true,
            // responsive: true,
            searching: false,
            "bSort" : false,
            dom :'Blfrtip',
            ajax: {
                url: 'staff-commission-list',
                data: function (d) {
                    d.role  = $('#role_filtr_type').val();
                }
            },
            columns: [
                //{data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'target_from', name: 'target_from'},
                {data: 'target_to', name: 'target_to'},
                {data: 'commission', name: 'commission'},
                {"data": function(data){
                var route = "{{URL::to('comm-user')}}";
                var html = '<a data-toggle="tooltip" title="Edit"  href="edit-staff-commission/'+data.id+'" class="fa fa-pencil-square-o editbtn"></a><a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_contact" user-id="" ></a>';
                return  html;
                }, "name": "action"},
            ],
            buttons: [
            	
    		],
        });

        $(document).on('change','#role_filtr_type',function () {
			$('#contacttable').DataTable().draw();
		});

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