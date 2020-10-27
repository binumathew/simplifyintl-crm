@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Commission Payments</h1>
					</div>  
                    <div class="Unavbg">
                        <div class="row">
                            <div class="col-md-6 col-sm-5">
                                <ul class="Ulinenav clearfix">
                                    <li class="active"><a href="">Commission</a></li>
                                    <!--<li><a href="#">Contact Groups</a></li>-->
                                </ul>
                            </div>
                            <div class="col-md-6 col-sm-5">
                                <ul class="EAbtnbg">
                                    <li><a data-toggle="modal" data-target="#payCommissionModal" id="pay_commission_btn">PAY COMMISSION</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>    
					@csrf
					<div class="Paymentsbg ctablebg clearfix">
                        <div class="panel panel-default">
    					    <div class="panel-heading">
    					        <h3 class="panel-title">Filter</h3>
    					    </div>
    					    <div class="panel-body">
    					        <form method="POST" id="order-search-form" class="form-inline" role="form">

    					            <div class="form-group">
    					                <label for="Promocode">Promocode</label>
    					                <input type="text" class="form-control" name="promocode" id="promocode" placeholder="Search Promocode">
    					            </div>
    					            <!-- <div class="form-group">
    					                <label for="email">Email</label>
    					                <input type="text" class="form-control" name="email" id="email" placeholder="search email">
    					            </div> -->

    					            <button type="submit" class="btn btn-primary">Search</button>
    					        </form>
    					    </div>
    					</div>
						<div class="row">
							<div class="col-md-12">
                            <table id="report-order-table" class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>Dealer</th>
                                        <th>Amount to Pay</th>
                                        <th>Paid Amount</th>
                                        <th>Due Date</th>
                                        <th>Payment Status</th>
                                        <th>Created On</th>
                                        <th>Paid On</th>
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

    <div id="payCommissionModal" class="modal fade avoopopup" role="dialog">
        <div class="modal-dialog mt-7p">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h3 class="modal-title" id="addCreditModalTitle">Pay Commission</h3>
                </div>
                <div class="modal-body">
                    <form id="new-autorecharge-form" method="POST">
                        <div class="form-group">
                            <label>Choose Dealer</label>
                            <select id="dealer" class="form-control" name="dealer">
                                @foreach($dealers as $dealer)
                                    <option value="{{$dealer->id}}">{{$dealer->first_name}} {{$dealer->last_name}} ({{$dealer->promocode}})</option>
                                @endforeach
                            </select>
                            <!-- <input type="text" id="auto_amount" class="form-control" name="amount"> -->
                        </div>  
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="text" id="pay_amount" class="form-control" name="pay_amount">
                        </div>
                        <div id="auto_recharge_error" style="display:none;" class="alert alert-danger"></div> 
                        <div id="auto_recharge_success" style="display:none;" class="alert alert-success"></div>                                  
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="payCommission">Pay</button>
                    </form>
                    
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
                url: 'comm-payment-report',
                data: function (d) {
                    d.promocode  = $('input[name=promocode]').val();
                    //d.email = $('input[name=email]').val();
                }
            },
            columns: [
                //{data: 'id', name: 'id'},
                {data: 'dealer_name', name: 'dealer_name'},
                {data: 'pay_amount', name: 'pay_amount'},
                {data: 'amount_paid', name: 'amount_paid'},
                {data: 'payment_date', name: 'payment_date'},
                {data: 'is_paid', name: 'is_paid'},
                {data: 'created_at', name: 'created_at'},
                {data: 'paid_on', name: 'paid_on'},
            ],
            buttons: [
            	
    		],
        });

        $('#order-search-form').on('submit', function(e) {
            orderTable.draw();
            e.preventDefault();
        });

        $('#new-autorecharge-form').validate({
            errorClass: "my-error-class",
            rules: {
                dealer: 'required',
                pay_amount: 'required',
            },
        });  

        $(document).on("click", '#payCommission', function (){
            if($('#new-autorecharge-form').valid()) {
                $('#auto_recharge_error, #auto_recharge_success').hide();
                $('#loadingsign').show();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    data: $('#new-autorecharge-form').serialize(),
                    url: '<?php echo url('/'); ?>/pay-commission',
                    success: function(response){ 
                        $('#loadingsign').hide();
                        if(response.status == "success"){
                            $('#auto_recharge_success').text(response.message);
                            $('#auto_recharge_success').show();
                            $('#new-autorecharge-form').trigger("reset");
                            orderTable.draw();
                        }
                        else{
                            $('#auto_recharge_error').text(response.message);
                            $('#auto_recharge_error').show();
                        }
                    }
                });
            }
            
        });

	});
</script>
@endsection