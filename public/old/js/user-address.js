jQuery(function($) {'use strict';


	$(document).ready(function(){
		

		$(document).on( 'click', '#change-address', function(){	   
			$('#existing-address').addClass('hidden');
			$('#edit-address').removeClass('hidden');
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
				regex:/^([A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}|GIR ?0A{2})$/,
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

		$('#buy_now_btn').click(function() {
			console.log($("#billing-form").valid());
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
	}); 
 
});