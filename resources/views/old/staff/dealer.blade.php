@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
<!-- 				<div class="Cleftpart">
					<h1>{{ ($staff_type==1)?'Staff':'Dealer' }}</h1>
					<span>Home  |  My Avoo  | Contacts </span> 
				</div> -->
				<div class="Unavbg">
					<div class="row">
						<div class="alert-status"> 
							@if(session()->has('success'))
							<div class="alert alert-success" id="success">
								{{ session()->get('success') }}
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
								<li class="active"><a href="">{{ ($staff_type==1)?'Staff':'Dealer' }} List</a></li>
								<!--<li><a href="#">Contact Groups</a></li>-->
							</ul>
						</div>
						<div class="col-md-2 col-sm-5">
							<ul class="EAbtnbg">
								<li class="expo">
									<!-- <a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a> -->
								</li>
								@if($staff_type==1)
								<li><a href="{{ url('staff/create') }}">ADD&nbsp;STAFF</a></li>
								@else
								<li><a href="{{ url('/create-dealer') }}">ADD&nbsp;DEALER</a></li>
								@endif
							</ul>
						</div>
					</div>
				</div>   
				@csrf
				<div class="Paymentsbg ctablebg clearfix">
					<div class="row">
						<div class="col-md-12">
							<table id="contacttable" class="display responsive no-wrap list-table" cellspacing="0" style="width:100%;">
								<thead>
									<tr>
										<th>Name</th>											
										<th>Email</th>	
										<th>Promo Code</th>										
										<th>Role</th>
										<th>Created Date</th>							
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i=1; ?>
									@foreach ($dealer as $user)
									<tr id="{{$i}}">
										<td>{{$user->first_name}} {{$user->last_name}}</td>						
										<td>{{$user->email}}</td>
										<td>{{$user->promocode}}</td>
										<td>
											{{ $user->name }}
										</td>
										<td>{{$user->created_at}}</td>										
										<td>
											@if($staff_type == 2 && $user->id != Auth::id())
											<a data-toggle="tooltip" title="Edit"  href="{{ route('staff.edit',$user->id)}}" class="fa fa-pencil-square-o editbtn"></a>
											<!-- <a data-toggle="tooltip" title="Delete" href="{{route('staff.destroy',$user->id)}}" class="fa fa-trash-o"></a>											 -->
											@endif
										</td>
									</tr>
									<?php 
									$i++; ?>
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

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);
	});
</script>
@endsection