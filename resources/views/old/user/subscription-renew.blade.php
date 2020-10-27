	<div id="subscriptionModal" class="modal fade avoopopup" role="dialog">
		<div class="modal-dialog mt-7p">
			<?php //print_r($autoPlan); ?>
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title">Subscription Renewal</h3>
				</div>
				<div class="modal-body">
					<form id="renewal-form" method="POST">
						<input type="hidden" name="autoplan_id" value="{{$autoPlan->id}}">
						<div class="form-group">
							<div class="radio">
								<label>
									<input type="radio" name="renewal_type" value="1" checked>Renew Now
								</label>
								<label>
									<input type="radio" name="renewal_type" value="2">On Next Renewal
								</label>
							</div>
						</div>	

						<div class="form-group" id="mode_select">
							<div class="radio">
								<label>
									<input type="radio" name="payment_mode" value="1" checked>Not Paid
								</label>								
								<label>
									<input type="radio" name="payment_mode" value="2">Paid
								</label>
							</div>						
						</div>

						<div class="hidden" id="transaction_details">
							<div class="form-group">
								<select name="payment_method" class="form-control">
									<option value="Direct Cash">Direct Cash</option>
									<option value="Bank Transfer">Bank Transfer</option>
									<option value="Paypal">Paypal</option>
									<option value="Braintree">Braintree</option>
								</select>
							</div>
							<div class="form-group">							
								<input class="form-control"  type="text" name="custom_txn_id" placeholder="Transaction/Reference ID">							
							</div>
							<div class="form-group">								
									<input class="form-control"  type="text" name="custom_description" placeholder="Description">							
							</div>			
						</div>

						@if($autoPlan->plan_type == 'sim')
						<div class="form-group">				
							<div class="radio">
								@if($autoPlan->bundle_id == 0)
								<select name="plan_id" class="form-control">
									<option> Choose Plan </option>					
									@foreach($plans as $plan)
									<option value="{{$plan->id}}" {{($autoPlan->plan_id == $plan->id)? 'selected':''}}> {{$plan->plan_name}} </option>
									@endforeach										
								</select>
								@else
								<select name="bundle_id" class="form-control">
									<option> Choose Plan </option>
									@foreach($plans as $plan)
									<option value="{{$plan->id}}" {{($autoPlan->bundle_id == $plan->id)? 'selected':''}}> {{$plan->plan_name.' '. $currency.$plan->sell_price.' ('.$plan->sim_count.' Sim)'}} </option>
									@endforeach										
								</select>
								@endif
							</div>
						</div>
						@endif						
						<div id="subscription_error"></div>									
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-success" id="subscriptionRenewal">Submit</button>
					</form>
					
				</div>
			</div>

		</div>
	</div>