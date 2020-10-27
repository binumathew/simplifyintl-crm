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
                                    <label for="pwd">Promocode</label>
                                    <select name="promocode" class="form-control">
                                        <option value="-1">Pick option</option>
                                        @foreach ($dealers as $promo)
                                        <option value="{{ $promo->promocode }}">{{ $promo->promocode }}</option>
                                        @endforeach
                                    </select>
                                </div>
    					           <!-- <div class="form-group">
                                            <label for="number">From</label>
                                            <input type="text" class="form-control customdate" name="from_date" id="from_date" placeholder="From">
                                    </div>
                                    <div class="form-group">
                                            <label for="number">TO</label>
                                            <input type="text" class="form-control customdate" name="to_date" id="to_date" placeholder="To">
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
                                        <th>Name</th>
                                        <th>Promocode</th>
                                        <th>Total Commission</th>
                                        <th>Total commission Paid</th>
                                        <th>Advance Balance</th>
                                        <th>This month Commission</th>
                                        <th>This month Not Paid</th>
                                        <th>Amount to Pay</th>
                                        <th>Action</th>
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
                        <input type="hidden" name="dealer_type" value="dealer">   
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
                    d.promocode  = $('select[name=promocode]').val();
                }
            },
            columns: [
                //{data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'promocode', name: 'promocode'},
                {data: 'total', name: 'total'},
                {data: 'totalpaid', name: 'totalpaid'},
                {data: 'advancebal', name: 'advancebal'},
                {data: 'monthcomm', name: 'monthcomm'},
                {data: 'monthnotpaid', name: 'monthnotpaid'},
                {data: 'topay', name: 'topay'},
                {"data": function(data){
                var route = "{{URL::to('comm-user')}}";
                var html = '<form method="post" action="'+route+'">@csrf<input type="hidden" name="user_id" value="'+data.userid+'"><button type="submit" class="btn btn-success btn-xs" title="View Details"><i class="fa fa-eye"></i></button></form>';
                return  html;
                }, "name": "action"},
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
        $('.customdate').datetimepicker({
        format: 'DD-MM-YYYY'
        });
	});
</script>
@endsection