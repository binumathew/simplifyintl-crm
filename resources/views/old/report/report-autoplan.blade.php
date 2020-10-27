@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Auto Plan</h1>
					</div>   
					<div class="Paymentsbg ctablebg clearfix">
                    <div class="panel panel-default">
					    <div class="panel-heading">
					        <h3 class="panel-title">Filter</h3>
					    </div>
					    <div class="panel-body">
                            <form method="POST" action="{{ url('list-report-autoplan') }}" id="autoplan-search-form" class="form-inline" role="form">
                                @csrf
					            <div class="form-group">
					                <label for="number">Number</label>
					                <input type="text" class="form-control" name="number" id="number" placeholder="search phonenumber">
					            </div>
		                                <div class="form-group">
		                                    <label for="number">From</label>
		                                    <input type="text" class="form-control customdate" name="from" id="from" placeholder="Next Renewal">
		                                </div>
		                                <div class="form-group">
		                                    <label for="number">TO</label>
		                                    <input type="text" class="form-control customdate" name="to" id="to" placeholder="Next Renewal">
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

					            <div class="form-group">
                                    <button type="button" id="searchBtn" class="btn btn-primary">Search</button>
                                  </div> 
                                <div class="form-group">
                                    <button type="submit" class="btn btn-info pull-right" id="export" name="exportdata" value="1">Export</button>
                                  </div> 
					        </form>
					    </div>
					</div>
						<div class="row">
							<div class="col-md-12">
                            <table id="report-autoplan-table" class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone Number</th>
                                        <th>Child Number</th>
                                        <th>Plan</th>
                                        <th>Provider</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Next Renewal</th>
                                        <th>Created At</th>
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
        var autoplanTable = $('#report-autoplan-table').DataTable({
        // dom: "<'row'<'col-xs-12'<'col-xs-6'l><'col-xs-6'p>>r>"+
        //     "<'row'<'col-xs-12't>>"+
        //     "<'row'<'col-xs-12'<'col-xs-6'i><'col-xs-6'p>>>",
        processing: true,
        serverSide: true,
        // responsive: true,
        searching: false,
        "bSort" : false,
        autoWidth: false,
        dom :'Blfrtip',
        ajax: {
            url: 'list-report-autoplan',
            data: function (d) {
                d.number  = $('input[name=number]').val();
                d.from    = $('input[name=from]').val();
                d.to      = $('input[name=to]').val();
                // var nxtrenew    = $('select[name=next_renew]').val();
                // d.nextrenew     = (nxtrenew != -1 ) ? nxtrenew : '';
            }
        },
        columns: [
            {data: 'name', name: 'name'},
            {data: 'phone_number', name: 'phone_number'},
            {data: 'child', name: 'child'},
            {data: 'plan', name: 'plan'},
            {data: 'provider', name: 'provider'},
            {data: 'status', name: 'status'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'next_renewal', name: 'next_renewal'},
            {data: 'created_at', name: 'created_at'}
        ],
        columnDefs: [
           { width: '100px', targets: 2 } 
        ],
        // buttons: [
        //     {
        //         extend: 'collection',
        //         text: 'Export',
        //         buttons: [
        //             'copy',
        //             'excel',
        //             'csv',
        //             'pdf',
        //         ]
        //     }
        // ],
        buttons: [
   //      	{
			// extend: 'excel',
			// text: 'Export',
			// className: 'btn-primary',
			// exportOptions: {
			// 	orthogonal: null
			// }
			// },
		],
    });

    $('#autoplan-search-form').on('submit', function(e) {
        $(this).submit();
    });
    $('#searchBtn').on('click', function(e) {
        autoplanTable.draw();
        e.preventDefault();
    });
    $('.customdate').datetimepicker({
        format: 'DD-MM-YYYY'
    });

	});
</script>
@endsection