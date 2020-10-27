@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Dealer Commission</h1>
					</div>
					<div class="Unavbg">
						<div class="row">
							<div class="col-md-8 col-sm-7">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Dealer Commission</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
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
    					        <form method="POST" action="{{ url('report-dealer-full') }}" id="dealer-search-form" class="form-inline" role="form">
    					        @csrf
    					        <div class="form-group">
                                    <label for="pwd">Name</label>
                                    <select name="dealer" class="form-control">
                                        <option value="-1">Pick option</option>
                                        @foreach ($dealer as $del)
                                        <option value="{{ $del->id }}">{{ $del->first_name.' '.$del->last_name }}</option>
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
								<table id="commissiontable" class="display responsive no-wrap" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Name</th>										
											<th>User Name</th>
											<th>Commission</th>
											<th>Commission Paid</th>
											<th>Commission to Pay</th>	
                                            <th>Payment Date</th>
											<th>Created at</th>						
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
		var commTable = $('#commissiontable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            searching: false,
            "bSort" : false,
            dom :'Blfrtip',
            ajax: {
                url: 'report-dealer-full',
                data: function (d) {
                    d.dealer     = $('select[name=dealer]').val();
                    d.from    	= $('input[name=from]').val();
                    d.to        = $('input[name=to]').val();
                }
            },
            columns: [
                //{data: 'id', name: 'id'},
                {data: 'dealer_name', name: 'dealer_name'},
                {data: 'planname', name: 'planname'},
                {data: 'total_amount', name: 'total_amount'},
                {data: 'paid_amount', name: 'paid_amount'},
                {data: 'pay_amount', name: 'pay_amount'},
                {data: 'paydate', name: 'paydate'},
                {data: 'date', name: 'date'},
            ],
            buttons: [
            	
    		],
        });

		$('.customdate').datetimepicker({
        format: 'DD-MM-YYYY'
        });

		$('#searchBtn').on('click', function(e) {
            commTable.draw();
            e.preventDefault();
	    });

	    //$('#dealer-search-form').on('submit', function(e) {
	       // $(this).submit();
        //    e.preventDefault();
	    //});
		// $('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		// $('.dataTables_filter input').attr("placeholder", "Search");
		// setTimeout(function() {
		// 	$('#success').fadeOut('fast');
		// }, 5000);
	});
</script>
@endsection