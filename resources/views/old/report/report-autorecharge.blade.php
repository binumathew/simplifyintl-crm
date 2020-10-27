@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Auto Recharge</h1>
					</div>   
					@csrf
					<div class="Paymentsbg ctablebg clearfix">
                    <div class="panel panel-default">
					    <div class="panel-heading">
					        <h3 class="panel-title">Filter</h3>
					    </div>
					    <div class="panel-body">
                            <form method="POST" action="{{ url('list-report-autorecharge') }}" id="autorecharge-search-form" class="form-inline" role="form">
                                @csrf

					            <div class="form-group">
					                <label for="number">Number</label>
					                <input type="text" class="form-control" name="number" id="number" placeholder="search phonenumber">
					            </div>
                                <div class="form-group">
                                    <label for="number">From</label>
                                    <input type="text" class="form-control customdate" name="from" id="from" placeholder="Card Expiry">
                                </div>
                                <div class="form-group">
                                    <label for="number">TO</label>
                                    <input type="text" class="form-control customdate" name="to" id="to" placeholder="Card Expiry">
                                </div>
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
                            <table id="report-autorecharge-table" class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone Number</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Card Expiry</th>
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
        var autorechargeTable = $('#report-autorecharge-table').DataTable({
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
            url: 'list-report-autorecharge',
            data: function (d) {
                d.number  = $('input[name=number]').val();
                d.from    = $('input[name=from]').val();
                d.to      = $('input[name=to]').val();
                //d.email = $('input[name=email]').val();
            }
        },
        columns: [
            {data: 'name', name: 'name'},
            {data: 'phone_number', name: 'phone_number'},
            {data: 'status', name: 'status'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'card_expiry', name: 'card_expiry'},
            {data: 'created_at', name: 'created_at'}
        ],
        columnDefs: [
           { width: '100px', targets: 2 } 
        ],
        buttons: [
        	//{
		//	extend: 'excel',
		//	text: 'Export',
		//	className: 'btn-primary',
		//	exportOptions: {
		//		//orthogonal: null
		//	}
		//	},
		],
    });


    $('#autorecharge-search-form').on('submit', function(e) {
        $(this).submit();
    });
    $('#searchBtn').on('click', function(e) {
        autorechargeTable.draw();
        e.preventDefault();
    });
    $('.customdate').datetimepicker({
        format: 'DD-MM-YYYY'
    });

	});
</script>
@endsection