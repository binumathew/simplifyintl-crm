@extends('layouts.home')
@section('content')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<!-- <div class="Cleftpart">
					<h1>Call History</h1>
					<span>Home  |  My Avoo  | Contacts </span> 
				</div> -->
				<div class="Unavbg">
					<div class="row">
						<div class="col-md-12 col-sm-6">
							<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Call History</a></li>
								</ul>
						</div>
						<div class="col-md-0 col-sm-6">
							<ul class="EAbtnbg">
								<!-- <li class="expo">
									<a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a>
								</li> -->
								<!-- <li><a href="addcontact">UPDATE</a></li> -->
							</ul>
						</div>
					</div>
				</div>   
				<div class="Paymentsbg ctablebg clearfix">
				<div class="panel panel-default">
					    <div class="panel-heading">
					        <h3 class="panel-title">Filter</h3>
					    </div>
					    <div class="panel-body">
					        <form method="POST" action="{{ url('call-pagination') }}" id="callhistory-search-form" class="form-inline" role="form">
					        	@csrf
					        	
					        	<div class="form-group">
					            <label for="users">Users</label>
					                <select name="users" class="form-control">
					                	<option value="-1">Please select</option>
					                	@foreach ($users as $user)
					                	<option value="{{ $user->id }}">{{ $user->name.' ('.$user->phone.')' }}</option>
					                	@endforeach
					                </select>
					            </div>
					        
                                <div class="form-group">
                                    <label for="number">From</label>
                                    <input type="text" class="form-control customdate" name="from" id="from" placeholder="From">
                                </div>
                                <div class="form-group">
                                    <label for="number">TO</label>
                                    <input type="text" class="form-control customdate" name="to" id="to" placeholder="To">
                                </div>
                                <div class="form-group">
                                    <label for="platform">Platform</label>
                                    <select id="platform" name="platform" class="form-control">
                                    	<option value="0">Please select</option>
                                    	<option value="1">App</option>
                                    	<option value="2">Sim</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="call_type">Call Type</label>                                    
                                    <select id="call_type" name="call_type" class="form-control">
                                    	<option value="0">Please select</option>
                                    	<option value="1">Free Calls</option>
                                    	<option value="2">Paid Calls</option>
                                    </select>                               
                                </div>

					            <!-- <div class="form-group">
					                <label for="email">Next Renewal</label>
					                <select name="next_renew" class="form-control">
			                                        <option value="-1">Pick option</option>  
			                                        <option value="1">Today</option>
			                                        <option value="2">This Week</option> 
			                                        <option value="3">This Month</option>        
			                                </select>
					            </div> -->

					            <button type="button" id="searchBtn" class="btn btn-primary">Search</button>
                            @csrf
                            <button type="submit" class="btn btn-info pull-right" id="export" name="exportdata" value="1">Export</button>
                            </form>
					    </div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<!-- <input type="text" name="connect_dat" class="datepicker pull-right"> -->
							<table id="contacttable" class="display responsive no-wrap" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>Name</th>											
										<th>CLI</th>
										<th>CLD</th>	
										<th>Duration</th>
										<th>Cost</th>	
										<th>Connect Time</th>
										<th>Platform</th>
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
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script type="text/javascript">
	$(document).ready(function(){

		function secondsTimeSpanToHMS(s) {
			var h = Math.floor(s/3600); 
			s -= h*3600;
			var m = Math.floor(s/60); 
			s -= m*60;
			return h+":"+(m < 10 ? '0'+m : m)+":"+(s < 10 ? '0'+s : s); 
		}

		$.fn.dataTable.ext.search.push(
			function (settings, data, dataIndex) { alert(data[4]);
				var sel_date = $('.datepicker').datepicker("getDate");

				var connect_date = new Date(data[4]);
            // if (sel_date == null && max == null) { return true; }
            // if (min == null && startDate <= max) { return true;}
            // if(max == null && startDate >= min) {return true;}
            // if (startDate <= max && startDate >= min) { return true; }
            return true;
        }
        );

		var table = $('#contacttable').DataTable({
			dom: 'Bfrtip',

			responsive: true,
			"bSort" : false,
			language: { search: "" },
			processing: true,
			serverSide: true,
			ajax: {
	            url: 'call-pagination',
	            data: function (d) {
	                d.users   = $('select[name=users]').val();
	               	d.from    = $('input[name=from]').val();
                	d.to      = $('input[name=to]').val();
                	d.platform= $('select[name=platform]').val();
                	d.call_type= $('select[name=call_type]').val();
	            }
        	},
			"dataType": "jsonp",
			"columns": [
			{"data": "username", "name": "username"},
			{"data" : "cli","name":"cli"},
			{"data" : "cld","name":"cld"},
			{"data": function(data){

				return secondsTimeSpanToHMS(data.duration);
			},"name":"duration","orderable": false, "searchable": false},
			{"data" : "cost","name":"cost","orderable": false, "searchable": false},
			//{"data" : "connect_date","name":"connect_date"},
			{"data" : function (data) {
				return moment(data.connect_date).format('DD-MM-YYYY HH:mm:s');
			},"name":"connect_date"},
			{"data" : function (data) {
				return (data.history_from == 1)?'App':'Sim';
			},"name":"history_from"},
			],
			"columnDefs": [
			{"defaultContent": "-","targets": "_all"}
			],
			buttons: [
			],

		});

		$('.dataTables_filter input').attr("placeholder", "Search");

		$('#searchBtn').on('click', function(e) {
	        table.draw();
	        e.preventDefault();
	    });

	    $('.customdate').datetimepicker({
	        format: 'DD-MM-YYYY'
	    });

	    $('#callhistory-search-form').on('submit', function(e) {
	       $(this).submit();
	    });

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);


		$(".datepicker").datepicker({ onSelect: function () { table.draw(); }, changeMonth: true, changeYear: true });

		$('.datepicker').change(function () {
			table.draw();
		});
	});
</script>
@endsection