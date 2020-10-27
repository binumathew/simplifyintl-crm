@extends('layouts.home')
@section('content')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="Cleftpart">
					<h1>Payment History</h1>
					<!--<span>Home  |  My Avoo  | Contacts </span> -->
				</div>
				<div class="Unavbg">
					<div class="row">
						<div class="col-md-8 col-sm-6">
						</div>
						<div class="col-md-4 col-sm-6">
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
					        <form method="POST" action="{{ url('payment-pagination') }}" id="payment-search-form" class="form-inline" role="form">
					        	@csrf
					        	<div class="form-group">
					            <label for="users">Users</label>
					                <select name="users" class="form-control">
					                	<option value="-1">Please select</option>
					                	@foreach ($users as $user)
					                	<option value="{{ $user->id }}">{{ $user->name }}</option>
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
					                <label for="payment_method">Payment Method</label>
					                <select name="payment_method" class="form-control" id="payment_method">
                                        <option value="0">All</option>  
                                        <option value="1">Paypal</option>
                                        <option value="2">Braintree</option> 
                                        <option value="3">Bank Transfer</option> 
                                        <option value="4">Direct Cash</option>
                                        <option value="5">App Payment</option> 
	                                </select>
					            </div>
					            <div class="form-group">
					                <label for="currency_symbol">Currency</label>
					                <select name="currency" class="form-control" id="currency_symbol">
					                	<option value="£" selected>GBP</option>
                                        <option value="$">USD</option>  
                                        <option value="€">EUR</option>                                        
	                                </select>
					            </div>

					            <button type="button" id="searchBtn" class="btn btn-primary">Search</button>
                            @csrf
                            <button type="submit" class="btn btn-info pull-right" id="export" name="exportdata" value="1">Export</button>
                            </form>
					    </div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<!-- <input type="text" name="connect_dat" class="datepicker pull-right"> -->
							<table id="contacttable" class="display responsive no-wrap enddatetbl" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>Name</th>											
										<th>Transaction ID</th>	
										<th>Buy Price</th>	
										<th>Amount</th>	
										<th>Description</th>
										<th>Payment Method</th>								
										<th>Payment Date</th>										
										<th>Status</th>					
									</tr>
								</thead>
								<tbody></tbody>
								<tfoot>
									<tr>
										<th colspan="2"></th>
										<th>Buy Price</th>
										<th>Sell Price</th>
										<th>Profit</th>
										<th colspan="3"></th>
									</tr>
									<tr>
										<td colspan="2">Total</td>
										<td id="total_buy_amount"></td>
										<td id="total_amount"></td>
										<td id="total_profit"></td>
										<td colspan="3"></td>
									</tr>
							    </tfoot>
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

		var table = $('#contacttable').DataTable({
			dom: 'Bfrtip',

			responsive: true,
			"bSort" : false,
			language: { search: "" },
			processing: true,
			serverSide: true,
			ajax: {
	            url: 'payment-pagination',
	            data: function (d) {
	                d.users   = $('select[name=users]').val();
	               	d.from    = $('input[name=from]').val();
                	d.to      = $('input[name=to]').val();
                	d.method  = $('select[name=payment_method]').val();
                	d.currency =  $('select[name=currency]').val();
	            }
        	},
        	drawCallback:function(settings)
		    {
		     	$('#total_amount').html(settings.json.currency + settings.json.total_sum);
		     	$('#total_buy_amount').html(settings.json.currency + settings.json.total_buy);
		     	$('#total_profit').html(settings.json.currency + (settings.json.total_sum - settings.json.total_buy).toFixed(2));
		    },
			"dataType": "jsonp",
			"columns": [
			{"data": "name", "name": "usr.name"},
			{"data" : "transaction_id","name":"transaction_id"},
			{"data" : function(data){
				return data.currency_symbol+data.buy_price;	
			},"name":"buy_price"},
			{"data" : function(data){
				return data.currency_symbol+data.total_amount;
			},"name":"total_amount"},						
			{"data" : "description","name":"description","orderable": false, "searchable": false},
			{"data" : "payment_method","name":"payment_method"},
			{"data" : function (data) {
				return moment(data.created_at).format('DD-MM-YYYY HH:mm:s');
			},"name":"user_payments.created_at"},
			{"data": function(data){
				switch(parseInt(data.status)){
					case 0:
					return 'Failed';
					break;
					case 1:
					return 'Success';
					break;
					case 2:
					return 'Success but failed';
					break;
				}
			},"name":"status"},
			],
			"columnDefs": [
			{"defaultContent": "-","targets": "_all"}
			],
			buttons: [
			// {
			// 	extend: 'excel',
			// 	text: 'Export',
			// 	className: 'btn-primary',
			// 	exportOptions: {
			// 		orthogonal: null
			// 	}
			// },
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

	    $('#payment-search-form').on('submit', function(e) {
	       $(this).submit();
	    });
		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);
	});
</script>
@endsection