@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Order</h1>
					</div>   
					<div class="Paymentsbg ctablebg clearfix">
                    <div class="panel panel-default">
					    <div class="panel-heading">
					        <h3 class="panel-title">Filter</h3>
					    </div>
					    <div class="panel-body">
					        <form method="POST" action="{{ url('list-report-order') }}" id="order-search-form" class="form-inline" role="form">
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
                                    <label for="pwd">Plan</label>
                                    <select name="plan" class="form-control">
                                        <option value="-1">Pick option</option>
                                        @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->plan_name }}</option>
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
					            <!-- <div class="form-group">
					                <label for="email">Email</label>
					                <input type="text" class="form-control" name="email" id="email" placeholder="search email">
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
                            <table id="report-order-table" class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Phone Number</th>
                                        <th>Name</th>
                                        <th>Plan</th>
                                        <!-- <th>Billing Address</th> -->
                                        <!-- <th>Shipping Address</th> -->
                                        <th>Delivery Status</th>
                                        <!-- <th>Payment Status</th> -->
                                        <th>Amount</th>
                                        <th>Next Renewal</th>
                                        <th>Promocode</th>
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
        var orderTable = $('#report-order-table').DataTable({
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
            url: 'list-report-order',
            data: function (d) {
                d.promocode  = $('select[name=promocode]').val();
                d.plan       = $('select[name=plan]').val();
                d.from    = $('input[name=from]').val();
                d.to      = $('input[name=to]').val();
            }
        },
        columns: [
            {data: 'order_id', name: 'order_id'},
            {data: 'phone_number', name: 'phone_number'},
            {data: 'username', name: 'username'},
            {data: 'plan', name: 'plan'},
            // {data: 'billing_address', name: 'billing_address'},
            // {data: 'shipping_address', name: 'shipping_address'},
            {data: 'delivery_status', name: 'delivery_status'},
            // {data: 'status', name: 'status'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'next_renewal', name: 'next_renewal'},
            {data: 'promocode', name: 'promocode'},
            {data: 'created_at', name: 'created_at'}
        ],
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

    $('#searchBtn').on('click', function(e) {
        orderTable.draw();
        e.preventDefault();
    });
    $('.customdate').datetimepicker({
        format: 'DD-MM-YYYY'
    });
    $('#order-search-form').on('submit', function(e) {
       $(this).submit();
    });
	});
</script>
@endsection