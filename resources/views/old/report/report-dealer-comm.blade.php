@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Dealer Commission Report</h1>
					</div>   
					<div class="Paymentsbg ctablebg clearfix">
                    <div class="panel panel-default">
					    <div class="panel-heading">
					        <h3 class="panel-title">Filter</h3>
					    </div>
					    <div class="panel-body">
					        <form method="POST" action="{{ url('list-report-dealer-comm') }}" id="subscrip-search-form" class="form-inline" role="form">
                            @csrf
                            <div class="row">
                                <div class="form-group">
                                    <label for="pwd">Promocode</label>
                                    <select name="promocode" class="form-control">
                                        <option value="-1">Pick option</option>
                                        @foreach ($promocode as $promo)
                                        <option value="{{ $promo->promocode }}">{{ $promo->promocode }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                 <div class="form-group">
                                    <button type="button" id="searchBtn" class="btn btn-primary">Search</button>
                                  </div> 
                                <div class="form-group">
                                    <button type="submit" class="btn btn-info pull-right" id="export" name="exportdata" value="1">Export</button>
                                  </div>
                            </div>
                            </form>
                            <form method="POST" action="{{ url('report-dealer-details') }}" id="subscrip-search-form" class="form-inline" role="form">
                                @csrf
                                <div class="form-group pull-right">
                                    <button type="submit" class="btn btn-info" id="full_details">Full Details</button>
                                </div> 
                            </form>
					    </div>
					</div>
						<div class="row">
							<div class="col-md-12">
                            <table id="report-subscrip-table" class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Promocode</th>
                                        <th>Total Commission</th>
                                        <th>Total commission Paid</th>
                                        <th>Advance Balance</th>
                                        <th>This month Commission</th>
                                        <th>This month Not Paid</th>
                                        <th>Amount to Pay</th>
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
        var oTable = $('#report-subscrip-table').DataTable({
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
            url: 'list-report-dealer-comm',
            data: function (d) {
                d.promocode  =  $('select[name=promocode]').val();
            }
        },
        columns: [
            {data: 'name', name: 'name'},
            {data: 'promocode', name: 'promocode'},
            {data: 'total', name: 'total'},
            {data: 'totalpaid', name: 'totalpaid'},
            {data: 'advancebal', name: 'advancebal'},
            {data: 'monthcomm', name: 'monthcomm'},
            {data: 'monthnotpaid', name: 'monthnotpaid'},
            {data: 'topay', name: 'topay'}   
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
        	//{
		//	extend: 'excel',
		//	text: 'Export',
		//	className: 'btn-primary',
		//	exportOptions: {
		//		orthogonal: null
		//	}
		//	},
		],
    });

    $('#searchBtn').on('click', function(e) {
        oTable.draw();
        e.preventDefault();
    });
    $('.customdate').datetimepicker({
        format: 'DD-MM-YYYY'
    });
    $('#subscrip-export-form').on('submit', function(e) {
       $(this).submit();
    });

	});
</script>
@endsection