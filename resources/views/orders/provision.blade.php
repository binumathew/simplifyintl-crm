@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/new-order')}}">Order</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/select-plan')}}">Plans</a></li>
                                <li class="breadcrumb-item active">Select Bolt-ons</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Provision Information</h4>
                    </div>
                </div>
            </div>


			<div class="row">
				<div class="col-md-9">
					<div class="card m-b-20">
						<div class="card-body">
							<p class="text-muted font-14 m-b-30 notice-board-top"><i class="mdi mdi-fullscreen noti-icon"></i>Please select the connection type for each product</p>
							<form id="provision-form">
								<table id="datatable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
									<thead>
										<tr>
											<th>Product</th>
											<th>Provider</th>
											<th>Connection Type</th>
											<th>Number To Keep</th>
											<th>Pac Code</th>
											<th>Transfer/Activation Date</th>
										</tr>
									</thead>
									<tbody>
										@foreach($selected_plans as $selected)
					                    @foreach($selected->list as $list)
					                    <tr class="info">
					                        <td>{{ $selected->product->plan_name }}</td>
					                        <td>{{ $selected->product->provider }}</td>
					                        <td>
												<select name="connection[{{$list->id}}]" class="form-control connection_type" data-list_id="{{$list->id}}">
													<option value="1" {{ ($list->port)?'selected':''}}>
													Port/Migration</option>
													<option value="0" {{ (!$list->port)?'selected':''}}>New</option>
												</select>
											</td>
											<td>
												<input class="form-control list_item_{{$list->id}}" id="port_cli_{{$list->id}}" name="porting_to[{{$list->id}}]" type="text" value="{{($list->porting_to)?:''}}" maxlength="11"{{(!$list->port)?'readonly':''}}>
											</td>
											<td>
												<div class="input-group">
													<input class="form-control list_item_{{$list->id}}" id="pac_code_{{$list->id}}" name="pac_code[{{$list->id}}]" type="text" value="{{($list->pac_no)?:''}}" {{(!$list->port)?'readonly':''}}>
													<div class="input-group-append">
														<span class="input-group-text"><i class="mdi mdi-rotate-3d mdi-18px verify_pac_code" data-list_id="{{$list->id}}"></i></span>
													</div>
												</div>
											</td>
											<td>
												<div class="input-group">
													<input type="text" id="transfer-date" name="transfer[{{$list->id}}]" class="form-control datepicker" value="{{($list->provision_date)?:''}}" placeholder="yyyy-mm-dd">
													<div class="input-group-append">
														<span class="input-group-text">
															<i class="mdi mdi-calendar"></i>
														</span>
													</div>
												</div>
											</td>
					                    </tr>
					                    @endforeach
					                @endforeach
									</tbody>
								</table>
								<div>
					                <a href="javascript:void(0)" class="btn btn-success waves-effect waves-light pull-right" id="provision_process"><strong>Continue</strong></a>
					            </div>
				            </form>
						</div>
					</div>
				</div>

				<div class="col-md-3">
					<div class="card m-b-20">
					    <div class="card-body right-nav">
							<ul>
								<li><a href="#"><b>Step 1</b><br />Select voice and data</a></li>
								<li><a href="#" class="selected"><b>Step 2</b><br />Configure voice and data</a></li>
								<li><a href="#"><b>Step 3</b><br />Select bolt-ons</a></li>
								<li><a href="#"><b>Step 4</b><br />Provisioning information</a></li>
								<li><a href="#"><b>Step 5</b><br />Bill limits</a></li>
								<li><a href="#"><b>Step 6</b><br />Summary</a></li>
								<li><a href="#"><b>Step 7</b><br />Add Customer</a></li>
								<li><a href="#"><b>Step 8</b><br />Payment</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- page wrapper end -->

	<script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			var today = new Date();
			$('.datepicker').datepicker({
	            autoclose: true,
	            orientation:'bottom left',
	            minDate: today,
	            startDate: today,
	            format: 'yyyy-mm-dd',
	            daysOfWeekDisabled: [0,6],
	        });

			$(document).on('change','.connection_type',function () {
	            var list_id = $(this).attr('data-list_id');
	            if($(this).val() == 1){
	            	$('.list_item_'+list_id).prop('readonly', false);
	            }else{
	            	$('.list_item_'+list_id).prop('readonly', true);
	            }
	        });

	        $(document).on('click', '.verify_pac_code', function () {
	            var $this = $(this);
	            var list_id = $this.data('list_id');
	            var pac_code = $('#pac_code_'+list_id).val();
	            var cli = $('#port_cli_'+list_id).val();
	            $.ajax({
	                headers: {
	                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	                },
	                type: 'POST',
	                url: base_url+'/verify-pac-code',
	                data: {pac_code:pac_code,list_id:list_id,cli:cli},
	                success:function(data){
	                  if (data.error) {
	                      $this.addClass('text-danger');
	                      $this.attr('title',data.message);
	                  }else{
	                      $this.attr('title','');
	                      $this.addClass('text-success');
	                  }
	                }
	            });
	        });

	        $(document).on('click', '#provision_process', function () {
	            var $this = $(this);
                var provision = $("#provision-form").serialize();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: base_url+'/provision',
                    data: {provision:provision},
                    success:function(data){
                        if (data.error) {
                            alert(data.message);
                        } else {
                            location.href = base_url+'/billing';
                        }
                    }
                });

	        });

		});
	</script>
@endsection
