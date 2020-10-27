jQuery(function($) {'use strict';

	var discount_value = 0, is_disc_fixed = 0;

	$(document).ready(function(){
		const reel = document.querySelector('.tab_reel');
		const tab1 = document.querySelector('.tab1');
		const tab2 = document.querySelector('.tab2');
		const panel1 = document.querySelector('.tab_panel1');
		const panel2 = document.querySelector('.tab_panel2');

		var existing_card_rule = {
			credit_card: 'required',
			terms_cond: 'required'
		};
		var new_card_rule = {  
			first_name:'required',
			last_name:'required',
			user_email: {
				required: true, 
				email: true
			},  
			postal_code: {
				required:true,
				// regex:/^([A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}|GIR ?0A{2})$/,
        regex:/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9][A-Za-z]?))))\s?[0-9][A-Za-z]{2})$/
			},
			street: 'required',
			city: 'required',                                            
			card_number: 'required',
			expiry_year: {
				valueNotEquals: "0" 
			},
			expiry_month: {
				valueNotEquals: "0" 
			},
			card_cvv: {
				required:true,
				number:true,
			},
			terms_cond: 'required'
		};

		var billing_form_rules = new_card_rule;
		if($('input[type=radio][name=credit_card]').length > 0)
			billing_form_rules = existing_card_rule;


		var form_validator = $("#billing-form").validate({

			errorClass: "invalid form-error",
			errorElement: 'div',
			errorPlacement: function(error, element) {                       
				error.appendTo(element.parent());
			},
			rules: billing_form_rules,
			messages: { 
				postal_code: {
					required:"Enter your postal code!",
					regex:"Invalid Postalcode",               
				},
				expiry_year: { valueNotEquals: "Please select expiry year!" }, 
				expiry_month: { valueNotEquals: "Please select expiry month!" },                  
				terms_cond: 'Agree the Terms and Conditions to proceed!.', 
				credit_card: 'Please choose a credit card or add a new one'                
			}
		});

		$('input[type=radio][name=credit_card]').change(function() {
		    if (this.value == 'new') {
		        $('.newcreditcard').show();
		        billing_form_rules = new_card_rule;
		        console.log(billing_form_rules);
		    }
		    else {
		    	$('.newcreditcard').hide();
		    	billing_form_rules = existing_card_rule;
		    	console.log(billing_form_rules);
		    }

		    form_validator.destroy();

		    form_validator = $("#billing-form").validate({

				errorClass: "invalid form-error",
				errorElement: 'div',
				errorPlacement: function(error, element) {                       
					error.appendTo(element.parent());
				},
				rules: billing_form_rules,
				messages: { 
					postal_code: {
						required:"Enter your postal code!",
						regex:"Invalid Postalcode",               
					},
					expiry_year: { valueNotEquals: "Please select expiry year!" }, 
					expiry_month: { valueNotEquals: "Please select expiry month!" },                  
					terms_cond: 'Agree the Terms and Conditions to proceed!.', 
					credit_card: 'Please choose a credit card or add a new one'                
				}
			});
		});

		function slideLeft(e) {
			tab2.classList.remove('active');
			this.classList.add('active');
			reel.style.transform = "translateX(0%)";
		}

		function slideRight(e) {
			tab1.classList.remove('active');
			this.classList.add('active');
			reel.style.transform = "translateX(-50%)";
		}

		tab1.addEventListener('click', slideLeft);
		tab2.addEventListener('click', slideRight);


		$(document).on("paste keyup", '#referral_code', function(){ 
			var refval = $(this).val();
			$(".ref-action").hide();
			$(".msg-span").html('');
			if(refval.length == 6){
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',
					data: {refval:refval},
					url: base_url+'/check-referral',	
					dataType: 'json',
					success:function(data){
						if(data['allow']){                      		
							$(".ref-action").show();                    		
						}else{
							$("#ref-error").html(data['msg']);
						}
					}				
				});
			}else{
				$("#ref-error").html("");
			}
		});
			
		$(document).on( 'click', '.ref-action', function(){ 
			var $this  = $(this);	     	        	   		        		      
			var refval = $('#referral_code').val();
			var action  = $this.data('action');
			$(".msg-span").html('');
			if(refval && refval.length ==6)
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {action:action,refval:refval},
				url: base_url+'/action-referral',	
				dataType: 'json',
				success:function(data){
					if(data['status']){                      		
						$("#ref-success").html(data['msg']);  
						$this.hide();                 		
					}else{
						$("#ref-error").html(data['msg']);
					}
				}				
            });
		});


		$(document).on("paste keyup", '#discount_code', function(){ 
			var coupon_code = $(this).val();
			$(".disc-action").hide();
			$(".msg-span").html('');
			if(coupon_code.length >= 4){
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',
					data: {coupon_code:coupon_code},
					url: base_url+'/check-discount-coupon',	
					dataType: 'json',
					success:function(data){
						if(data['allow']){                      		
							$(".disc-action").show();   
							$("#disc-error").html('');              		
						}else{
							$("#disc-error").html(data['msg']);
						}
					}				
				});
			}else{
				$("#disc-error").html("");
			}
		});
			
		$(document).on( 'click', '.disc-action', function(){ 
			var $this  = $(this);	     	        	   		        		      
			var coupon_code = $('#discount_code').val();
			var action  = $this.data('action');
			$(".msg-span").html('');
			if(coupon_code && coupon_code.length >=4) {
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',
					data: {action: action,  coupon_code: coupon_code},
					url: base_url+'/action-discount',	
					dataType: 'json',
					success:function(data){
						if(data['status']){                      		
							$("#disc-success").html(data['msg']);  
							$("#disc-error").html(''); 
							if(action == 'apply') {
								discount_value = data['data']['discount_value'];
								is_disc_fixed = data['data']['is_fixed']; 
							}  
							else if(action == 'remove') {
								discount_value = 0;
								is_disc_fixed = 0; 
							}
							$this.hide();    
							getGrandTotal();             		
						}else{
							$('#discount_code').val('');
							$("#disc-error").html(data['msg']);
						}
					}				
	            });
			}
			else {

			}
		});

		// $(document).on('click','.cart_inc',function () {
		// 	var $button = $(this);
		// 	var oldValue = $button.parent().find("input").val();
		// 	var max_limit = $button.attr('data-max');
		// 	if (oldValue < max_limit) {
		// 		var newVal = parseFloat(oldValue) + 1;
		// 		$button.parent().find("input").val(newVal);
		// 		// if(newVal == max_limit){
		// 			//$button.parents('.')next().find('.change-number').addClass('disabled');				
		// 		// }
		// 	}		
		// });

		// $(document).on('click','.cart_dec',function () {
		// 	var $button = $(this);
		// 	var oldValue = $button.parent().find("input").val();
		// 	if (oldValue > 0) {
		// 		var newVal = parseFloat(oldValue) - 1;
		// 	} else {
		// 		newVal = 0;
		// 	}
		// 	$button.parent().find("input").val(newVal);
		// });
		$(document).on('mouseenter','.port_label',function (e) {
			var that = $(this);
			that.find('.port_label_left').html('<p>You can port your currently using sim to our network. Text  ‘PAC’ to 65075.</p>');
			that.find('.port_label_left').show();
		});

		$(document).on('mouseleave','.port_label',function (e) {
			$('.port_label_left').hide();
		});

		setInterval(function() {
			$.get(base_url+'/get-time', function( current_time ) {				
				var now = new Date(current_time).getTime();
				$(".timer").each(function() {
					var $this = $(this);				
					var expire_at = new Date($this.data('time')).getTime();				
					var distance = expire_at - now;

					// Time calculations for days, hours, minutes and seconds
					var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
					var seconds = Math.floor((distance % (1000 * 60)) / 1000);
					
					$this.html('<span>'+ minutes + 'm ' + seconds + 's '+'</span>');

					//if count down finished
					if (distance <= 1000) { 
						var stock_id = $this.data('stock_id');
						$.ajax({
							headers: {
								'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							},
							type: 'POST',
							data: {action:'remove', stock_id:stock_id},
							url: base_url+'/reserve-sim',	
							dataType: 'json',
							success:function(data){
								if(data.success){                      		
									$('#available_sim_'+stock_id).removeAttr('checked');
									$('.selected_sim_'+stock_id).remove();   
									getGrandTotal(); 
								}
							}				
						});						
					}
				})
			});
		}, 1000);

		$(document).on( 'click', 'a.remove_selected', function(){ 	      
			var $this = $(this);	        	   		        		      
			var stock_id = $this.data('stock_id');
			var action = 'remove';

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {action:action, stock_id:stock_id},
				url: base_url+'/reserve-sim',	
				dataType: 'json',
				success:function(data){
					if(data.success){
						$('.max_selected_sim').html('');
						$('#available_sim_'+stock_id).removeAttr('checked');
						$('.selected_sim_'+stock_id).remove();
						getGrandTotal();                       		
					}
				}				
			});	            	              
		});

		$(document).on( 'click', '.remove_item', function(){ 	      
			var $this = $(this);	        	   		        		      
			var cart_id = $this.data('cart_id');
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {cart_id:cart_id},
				url: base_url+'/remove-cart-item',	
				dataType: 'json',
				success:function(data){
					if(data.success){                      		
						$('.cart_item_'+ cart_id).remove();
						getGrandTotal();                       		
					}
				}				
			});	            	              
		});	

		$(document).on( 'click', '.add_crt_btn', function(){ 	      
			var $this = $(this);	        	   		        		      
			var item = $this.data('item');
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {item:item},
				url: base_url+'/add-cart',					
				success:function(data){
					$('#cart_wrapper').append(data);
					getGrandTotal();     
					$('#add_cart_popup').modal('hide');
				}				
			});	            	              
		});

		$(document).on( 'click', '.change-number', function(){
			var cart_id = $(this).data('cart_id');	 
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {cart_id:cart_id},
				url: base_url+'/get-cart-item-data',					
				success:function(response){
					var html = '<p class="max_selected_sim form-error"></p>';
					$.each(response.data, function( index, value ) {	
							var hidden_class = ((value.port ==1)? 'hidden':'');
							html += '<li class="selected_sim_'+value.stock_id+'"><div class="row gutter5px"><div class="col-xs-6"><b>'+ value.phone_number +'</b></div><div class="col-xs-4"><i class="timer" data-time="'+ value.expire_at +'" data-stock_id="'+ value.stock_id+'"></i></div><div class="col-xs-2"><a href="#" class="center-block remove_selected '+ hidden_class +'" data-stock_id="'+value.stock_id+'">X</a></div></div></li>';
						});
					$('.selected_sim').html(html);  
					$('#cart_id').val(cart_id);
					$('#changepop').modal('show')
				}				
			});				        			
		});

		$(document).on("keyup", '#custom-search', function(){    
        	var key = $(this).val();      	     
        	if(key.length >= 4){
        		var category = 'custom';
        		var dealer = $('#dealer').val();
        		$.ajax({
					headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
					type: 'POST',
					data: {key:key,dealer:dealer},
					url: base_url+'/search-number',
					success: function(response){ 						
						var html = '';
						$.each(response, function( index, value ) {							
							html += '<label><li><span><input type="checkbox" class="available_sim" id="available_sim_'+ value.id+'" name="available_sim[]" value="'+ value.id+'" data-phone="'+ value.phone_number +'"></span> '+ value.phone_number +'</li></label>';
						}); 							
						$('#custom_option').html(html);  					        
					}
				});
        	}
        });

		$(document).on( 'click', '#change-address', function(){	   
			$('#existing-address').addClass('hidden');
			$('#edit-address').removeClass('hidden');
		});
		
		$(document).on( 'change', '.extra-credit', function(){			
			var $this = $(this);	        	   		        		      
			var list_id = $this.data('list_id');
			if($this.is(":checked")){
				var credit = $this.val();	
			}else{
				var credit = 0;
			}

			$('.credit_'+list_id).prop('checked', false);									
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {id:list_id,credit:credit},
				url: base_url+'/update-cart',					
				success:function(data){	
					if(credit != 0){
						$this.prop('checked', true);	
					}			
					getGrandTotal();  
				}				
			});	 			      		
		});	

		$(document).on( 'change', '.port_request', function(){			
			var $this = $(this);	        	   		        		      
			var list_id = $this.data('list_id');
			if($this.is(":checked")){
				var porting = 1;	
				$('#remove_sim_'+list_id).addClass('hidden');
			}else{
				var porting = 0;
				$('#remove_sim_'+list_id).removeClass('hidden');
			}
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {id:list_id,port:porting},
				url: base_url+'/update-cart',					
				success:function(data){								
					getGrandTotal();  
				}				
			});	 			      		
		});	

		$(document).on( 'click', '.available_sim', function(){	 	      
			var $this = $(this);	        	   		        		      
			var stock_id = $(this).val();
			var phone_number = $(this).data('phone');
			var cart_id = $('#cart_id').val();
    
			if($(this).prop("checked") == true){
				var action = 'add';
			}else{
				var action = 'remove';
			}

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {action:action, cart_id:cart_id, stock_id:stock_id},
				url: base_url+'/reserve-sim',	
				dataType: 'json',
				success:function(data){
					if(data.success){
						if(action == 'add'){
							var view = '<li class="selected_sim_'+stock_id+'"><div class="row gutter5px"><div class="col-xs-6"><b>'+ phone_number +'</b></div><div class="col-xs-4"><i class="timer" data-time="'+ data.success +'" data-stock_id="'+stock_id+'"></i></div><div class="col-xs-2"><a href="#" class="center-block remove_selected" data-stock_id="'+stock_id+'">X</a></div></div></li>';
							$('.selected_sim').append(view);
						}else{							
							$('.selected_sim_'+stock_id).remove();
							getGrandTotal(); 
						}
					}else if(data.error == 1){  
						$this.removeAttr('checked');
						$this.parent().remove();
					}else if(data.error == 2){
						$this.removeAttr('checked');
						$('.max_selected_sim').html(data.message); 
					}
					$.ajax({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						type: 'get',						
						url: base_url+'/cart',					
						success:function(data){	
							$('#cart_wrapper').html(data);							
							getGrandTotal();  
						}				
					});
				}				
			});	            	              
		});

		$(document).on( 'click', '.reload_sim', function(){
			var category = ($(this).data('type'));
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {category:category},                                                    
				url: base_url+'/reload-sim',
				dataType: 'json',
				success:function(response){
					var html = '';
					$.each(response, function( index, value ) {							
						html += '<label><li><span><input type="checkbox" class="available_sim" id="available_sim_'+ value.id+'" name="available_sim[]" value="'+ value.id+'" data-phone="'+ value.phone_number +'"></span> '+ value.phone_number +'</li></label>';
					}); 							
					$('#'+ category +'_option').html(html);             
				}
			});	            	              
		});

		$.validator.setDefaults({
			ignore: []
		});

		$.validator.addMethod(
			"regex",
			function(value, element, regexp) {
				var re = new RegExp(regexp);
				return this.optional(element) || re.test(value);
			},
			"Please check your input."
		);

		

		$('#buy_now_btn').click(function() {
			if($("#billing-form").valid()) {
				$(".activatenow").attr('disabled', 'disabled');
				$('#loadingsign').show();
				$("#billing-form").submit();
			}
		});

		$.validator.addMethod("valueNotEquals", function(value, element, arg){
			return arg !== value;
		}, "Invalid Selection.");

		$('.find_address').on('click', function() {
			var $this =  $(this);
			var pc_type = ($this.hasClass('bill'))?'bill':'ship';
			var post_code = $('#'+ pc_type +'_postal_code').val();
			var house_no = $('#'+ pc_type +'_house_no').val();						
			if(post_code.length > 3){
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',
					data: {post_code:post_code,house_no:house_no},
					url: base_url+'/get_postcode',
					success: function(response){ 
						if(response.error){								
							$('#'+ pc_type +'_postalcode_error').html('<div class="invalid form-error">'+ response.message +'</div>');
						}else if(response.length == 1){
							var category = (pc_type == 'ship')?'ship_':'';
							var address = response[0].split(',');
							var street = address[0]+', '+address[1]+((address[2] != '')?', '+address[2]:'');
							$('#'+category+'street').val(street);
							$('#'+category+'city').val($.trim(address[3])+', '+address[4]);  
							$('#'+category+'country').val(address[5]);
							$('#'+ pc_type +'_postal_code').val(address[6]);  
							$('#'+ pc_type +'_postalcode_error').html('');
							$('.'+ pc_type +'_postcode_list').addClass('hidden');  
						}else{
							var postcode_select = '<select id="postcode_option" class="'+pc_type+'"><option value="">Choose Address</option>';
							$.each(response, function( index, value) {		
							  	postcode_select += '<option value="'+value+'">'+value+'</option>';
							}); 
							postcode_select += '</select>';   
							$('#'+ pc_type +'_postalcode_error').html('');   
							$('.'+ pc_type +'_postcode_list').html(postcode_select);   
							$('.'+ pc_type +'_postcode_list').removeClass('hidden');  
						}							        
					}
				});
			} else {
				$('#'+ pc_type +'_postal_code').focus();
			}
		});

		$(document).on('change','#postcode_option', function() {
			var type = ($(this).hasClass('ship'))?'ship_':'';
			var category = (type == 'ship_')?'ship':'bill';
			var postcode = $(this).val();  
			$('.'+category+'_postcode_list').addClass('hidden');            
			var address = postcode.split(',');
			var street = address[0]+', '+address[1]+((address[2] != '')?', '+address[2]:'');
			$('#'+type+'street').val(street);
			$('#'+type+'city').val($.trim(address[3])+', '+address[4]);  
			$('#'+type+'country').val(address[5]);
			$('#'+ category +'_postal_code').val(address[6]);            
		});


        $(document).on( 'change', '#card_number', function(){                  
            var result = $("#card_number").validateCreditCard();
            if(result.card_type !== null){
                $('#card_type').val(result.card_type.name);
            }else{
                $('#card_type').val('Card');
            }
            // if(!result.length_valid){                
            // }
        });
        $('#card_number').mask('0000-0000-0000-0000-0000');

        $(document).on('change','#terms_cond', function() {
        	if($(this).is(':checked')){
        		$(".activatenow").removeClass("disabled").prop("disabled", false);        		
        	}else{
        		$(".activatenow").addClass("disabled").prop("disabled", true);
        	}
        });


        $('#self-payment-btn').on('click', function() {
        	var user_id = $(this).data('userid');
        	$('#loadingsign').show();
        	$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',
				data: {'user_id' :user_id},
				url: base_url+'/self-payment-link',
				success: function(response){ 
					$('#loadingsign').hide();
					if(response.success == 1)
						$('#link-success').text(response.message);
					else
						$('#link-error').text(response.message);
				}
			});
        });
	}); 

	function getGrandTotal(){
		var cart_item = 0;
		var amount = 0;
		var sim_amount = 0;
		var discount_amount = 0;  
		var html_view = '';      		
		var currency = $('#currency_symbol').val();				
		$(".product_price").each(function() {
			cart_item += 1;
			amount += parseFloat($(this).val());
		});
		$(".sim_price").each(function() {
			sim_amount += parseFloat($(this).val());
		});
		$(".extra-credit").each(function() {        		
			if($(this).is(':checked')){
				amount += parseFloat($(this).val());
			}
		});
		
		if(cart_item == 0){
			$('.cart_amount_holder').html('<div class="row"><div class="col-xs-12"><span class="cart_amount">Your cart is empty!...</span></div></div>');
		}else{
			var premium = '';			
			if(sim_amount > 0){
				//var premium_no = $(".sim_price").length;				
				premium += '<div class="row"><div class="col-xs-10">Premium Numbers Charge : </div><div class="col-xs-2">'+ currency + parseFloat(sim_amount).toFixed(2) +'</div></div>';
			}

			var total_amount = amount + sim_amount;

			var discount_html = '';
			if(discount_value > 0) {
				if(is_disc_fixed == 1)
					discount_amount = discount_value;
				else {
					discount_amount = total_amount * discount_value * 1.0 / 100;
				}
				discount_html = '<div class="row"><div class="col-xs-10">Discount : </div><div class="col-xs-2">- '+ currency + parseFloat(discount_amount).toFixed(2) +'</div></div>';
			}

			total_amount = total_amount - discount_amount;

			html_view = '<div class="row"><div class="col-xs-10"><span class="cart_amount">Sub Total :</span></div><div class="col-xs-2"><span class="cart_amount sub_total">'+ currency + parseFloat(amount).toFixed(2) +'</span></div></div><div class="row"><div class="col-xs-10">Delivery Charge :</div><div class="col-xs-2">free</div></div>'+ premium + discount_html +'<div class="row"><div class="col-xs-10"><span class="cart_amount">Grand Total :</span></div><div class="col-xs-2"><span class="cart_amount grand_total">'+ currency + parseFloat(total_amount).toFixed(2) + '</span></div></div>';

			$('.cart_amount_holder').html(html_view);
		}
	} 

	getGrandTotal(); 
});