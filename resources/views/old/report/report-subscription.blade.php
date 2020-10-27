@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Subscription</h1>
					</div>   
					<div class="Paymentsbg ctablebg clearfix">
                    <div class="panel panel-default">
					    <div class="panel-heading">
					        <h3 class="panel-title">Filter</h3>
					    </div>
					    <div class="panel-body">
					        <form method="POST" action="{{ url('list-report-subscription') }}" id="subscrip-search-form" class="form-inline" role="form">
                            @csrf
                            <div class="row">
                                <div class="form-group">
                                    <label for="number">Number</label>
                                    <input type="text" class="form-control" name="number" id="number" placeholder="search phonenumber">
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
                                    <label for="pwd">Plan</label>
                                    <select name="plan" class="form-control">
                                        <option value="-1">Pick option</option>
                                        @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->plan_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                  <div class="form-group">
                                    <label for="email">Package</label>
                                    <select name="package" class="form-control">
                                        <option value="-1">Pick option</option>  
                                        <option value="SIM New Activation Fee">SIM New Activation Fee</option>
                                        <option value="SIM Monthly Connection Fee">SIM Monthly Connection Fee</option> 
                                        <option value="Sim shipment and fulfillment - Unbranded">Sim shipment and fulfillment - Unbranded</option>        
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
					    </div>
					</div>
						<div class="row">
							<div class="col-md-12">
                            <table id="report-subscrip-table" class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>Usage Identifier</th>
                                        <th>Product</th>
                                        <th>Start Date</th>
                                        <th>Units</th>
                                        <th>Price</th>
                                        <th>Total Amount</th>
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
        var autoplanTable = $('#report-subscrip-table').DataTable({
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
            url: 'list-report-subscription',
            data: function (d) {
                d.number  = $('input[name=number]').val();
                d.from    = $('input[name=from]').val();
                d.to      = $('input[name=to]').val();
                d.plan     = $('select[name=plan]').val();
                d.package  = $('select[name=package]').val();

            }
        },
        columns: [
            {data: 'number', name: 'number'},
            {data: 'name', name: 'name'},
            {data: 'date', name: 'date'},
            {data: 'units', name: 'units'},
            {data: 'price', name: 'price'},
            {data: 'total', name: 'total'}
        ],
        columnDefs: [
           { width: '100px', targets: 2 } 
        ],
        buttons: [
		],
    });

    $('#searchBtn').on('click', function(e) {
        autoplanTable.draw();
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