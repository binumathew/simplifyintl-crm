@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Porting Report</h1>
					</div> 
					<div class="Paymentsbg ctablebg clearfix">
                    <div class="panel panel-default">
					    <div class="panel-heading">
					        <h3 class="panel-title">Filter</h3>
					    </div>
					    <div class="panel-body">
					        <form method="POST" action="{{ url('list-port') }}" id="port-search-form" class="form-inline" role="form">
			                               @csrf
			                                <div class="form-group">
			                                    <label for="Promocode">Promocode</label>
			                                    <select name="promocode" class="form-control">
			                                        <option value="-1">Pick option</option>
			                                        @foreach ($promocode as $promo)
			                                        <option value="{{ $promo->promocode }}">{{ $promo->promocode }}</option>
			                                        @endforeach
			                                    </select>
			                                </div>
					            <div class="form-group">
					                <label for="category">Status</label>
					                <select name="status" class="form-control">
					                	<option value="-1">Please select</option>
					                	<option value="5">Request Cancelled</option>
					                	<option value="0">Request Received</option>
					                	<option value="1">Request Initiated</option>
					                	<option value="2">Request Confirmed</option>
					                	<option value="3">Processed</option>
					                	<option value="4">Process Completed</option>
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
					            <!-- <div class="form-group">
					                <label for="name">Name</label>
					                <input type="text" class="form-control" name="name" id="name" placeholder="search name" autocomplete="off">
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
                            <table id="port-table" class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>AVOO Number</th>                                        
                                        <th>Pac Number</th>
                                        <th>Porting To</th>
                                        <th>Provider</th>
                                        <th>Reference</th>
                                        <th>Expected Date</th>
                                        <th>PromoCode</th>
                                        <th>Status</th>
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
        var portTable = $('#port-table').DataTable({
        // dom: "<'row'<'col-xs-12'<'col-xs-6'l><'col-xs-6'p>>r>"+
        //     "<'row'<'col-xs-12't>>"+
        //     "<'row'<'col-xs-12'<'col-xs-6'i><'col-xs-6'p>>>",
        processing: true,
        serverSide: true,
        responsive: true,
        searching: false,
        "bSort" : false,
        dom :'Blfrtip',
        ajax: {
            url: 'list-port',
            data: function (d) {
                d.status = $('select[name=status]').val();
                d.promocode = $('select[name=promocode]').val();
                d.from    = $('input[name=from]').val();
                d.to      = $('input[name=to]').val();
            }
        },
        columns: [
            {data: 'phone_number', name: 'phone_number'},
            // {data: 'temp_number', name: 'temp_number'},
            {data: 'pac_number', name: 'pac_number'},
            {data: 'porting_to', name: 'porting_to'},
            {data: 'provider', name: 'provider'},
            {data: 'reference_id', name: 'reference_id'},
            {data: 'expected_date', name: 'expected_date'},
            {data: 'promocode', name: 'promocode'},
            {data: 'status', name: 'status'},
            {data: 'created_at', name: 'created_at'}
        ],
        buttons: [
		],

    });
    $('#searchBtn').on('click', function(e) {
        portTable.draw();
        e.preventDefault();
    });
    $('.customdate').datetimepicker({
        format: 'DD-MM-YYYY'
    });    
    $('#port-search-form').on('submit', function(e) {
        $(this).submit();
    });

	});
</script>
@endsection