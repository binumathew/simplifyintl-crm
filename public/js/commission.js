jQuery(function($) {'use strict';
$(document).ready(function (){

/*jQuery validation adding custom validation rule */
    // Date field validation.
    jQuery.validator.addMethod("dateFormat", function(value, element) {
        return this.optional(element) || moment(value,"DD-MM-YYYY").isValid();
    }, "Please enter a valid date in the format DD-MM-YYYY");
/* End */
/* Overide inorder to achieve dynamic array field validation */
$.validator.prototype.checkForm = function() {
    //overriden in a specific page
    this.prepareForm();
    for (var i = 0, elements = (this.currentElements = this.elements()); elements[i]; i++) {
        if (this.findByName(elements[i].name).length !== undefined && this.findByName(elements[i].name).length > 1) {
            for (var cnt = 0; cnt < this.findByName(elements[i].name).length; cnt++) {
                this.check(this.findByName(elements[i].name)[cnt]);
            }
        } else {
            this.check(elements[i]);
        }
    }
    return this.valid();
};
/* END */

/* Plan commission dynamic add & Remove div elements */
$(document).on('click', '.toadd', function(){
	if( DurationCount < duration ){
		var clone = $(".toClone").children().clone();
		clone.find('button').removeClass('btn-primary toadd').addClass('toremove btn-warning').html('-');
		//clone.attr('toClone').removeClass('toClone').addClass('cloned');
		clone.find('input:text').val('');
        clone.find('select').val('');
		$(".forCloned").append(clone);
		DurationCount++;
	}
});
$(document).on('click', '.toremove', function(){
	$(this).parent().parents().eq(2).remove();
	DurationCount--;
});
/* END */
/* Define plan commission get plan names based on plan type */
$(document).on('change', '#planType', function(){
	getplan();
});
function getplan($planid = ""){
	var type = $('select[name=plan_type]').val();
    var dealer = $('select[name=dealer_id]').val();
	if(type != ""){
	$('#planId').find('option').not(':first').remove();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'POST',
			data:{type:type,dealer:dealer},
			url: base_url+'/comm-getplan',
			success: function(response){ 
				var data = JSON.parse(response);
				$.each(data,function(k,val){
					var simbundle = "";
					if(val.sim_count != undefined)
						{ simbundle = " Bundle -"+val.sim_count;}
					var option = "<option value='"+val.id+"'>"+val.plan_name+simbundle+"</option>";
					$("#planId").append(option); 
				});
			}
		});
	}
}
/* END */
/* Define plan commission validation */
$("#save-plancommission-form").validate({
    errorClass: "invalid form-error",
    errorElement: 'div',
    errorPlacement: function(error, element) {
        error.appendTo( element.parent().next("span") );
    },
    ignore: [],
    rules: {
            plan_type: {
                required: true,
            },
            plan_id: {
                required: true,
            },
            "comm_rate[]": {
                required: true,
                maxlength:10,
            },
            "comm_type[]": {
                required: true,
            },
            "comm_duration[]": {
                required: true,
            },
            "status[]": {
                required: true,
            },
        },
        messages: {
            plan_type: {
                required : "Choose plan type",
            } ,
            plan_id: {
                required : "Choose plan name",
            } ,
            "comm_rate[]": {
                required : "Enter commission rate",
                maxlength: "Maximum character limit exceeded",
            } ,
            "comm_type[]": {
                required : "Choose commission type",
            } ,
            "comm_duration[]": {
                required : "Choose commission duration",
            } ,
             "status[]": {
                required : "Choose commission status",
            } ,
        },
    submitHandler: function (form) {
        var data = $("#save-plancommission-form").serialize();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            data:data,
            url: base_url+'/save-plancommission',
            dataType:"json",
            success:function(data){
                if(data.status == 200){
                    alertify.success(data.msg);
                    setTimeout(function(){
                        window.location.href = base_url+data.redirect;
                    }, 1500);
                }else{
                    $.each(data.msg,function(k,val){
                        alertify.error(val);
                    });
                }
            }
        });
    }
});
/* End */
/* Plan Commission Delete */
$(document).on("click",".delete_comm", function () {
    var id      = $(this).attr('data-id');
    var type    = $(this).attr('data-type');
    alertify.confirm('Delete Confirmation', 'Are you sure to delete the commission ?. All the commission assigned to the plan will be erased. Please view the details & confirm',
    function(){
        deleteComm(id,type);
    },function(){ alertify.error('Option cancelled')});
});
function deleteComm(id,type){  
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type:"POST",
        data:{id:id,type},
        url:base_url + '/delete-comm',
        success:function(data){
            if(data.status == 200){
                alertify.success(data.msg);
                $('#'+data.table).DataTable().draw();
            }else {
                alertify.error(data.msg);
            }
        }
    });
}
/* End */
/* Define Dealer commission validation */
$("#save-dealercommission-form").validate({
    errorClass: "invalid form-error",
    errorElement: 'div',
    errorPlacement: function(error, element) {
        error.appendTo( element.parent().next("span") );
    },
    ignore: [],
    rules: {
            dealer_id: {
                required: true,
            },
            plan_type: {
                required: true,
            },
            plan_id: {
                required: true,
            },
            "comm_rate[]": {
                required: true,
                maxlength:10,
            },
            "comm_type[]": {
                required: true,
            },
            "comm_duration[]": {
                required: true,
            },
            "status[]": {
                required: true,
            },
        },
        messages: {
            dealer_id: {
                required : "Choose dealer",
            } ,
            plan_type: {
                required : "Choose plan type",
            } ,
            plan_id: {
                required : "Choose plan name",
            } ,
            "comm_rate[]": {
                required : "Enter commission rate",
                maxlength: "Maximum character limit exceeded",
            } ,
            "comm_type[]": {
                required : "Choose commission type",
            } ,
            "comm_duration[]": {
                required : "Choose commission duration",
            } ,
             "status[]": {
                required : "Choose commission status",
            } ,
        },
    submitHandler: function (form) {
        var data = $("#save-dealercommission-form").serialize();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            data:data,
            url: base_url+'/save-dealercommission',
            dataType:"json",
            success:function(data){
                if(data.status == 200){
                    alertify.success(data.msg);
                    setTimeout(function(){
                        window.location.href = base_url+data.redirect;
                    }, 1500);
                }else{
                    $.each(data.msg,function(k,val){
                        alertify.error(val);
                    });
                }
            }
        });
    }
});
/* End */
/* View commission */
$(document).on("click",".view_comm", function () {
    var id      = $(this).attr('data-id');
    var type    = $(this).attr('data-type');
    viewComm(id,type);
});
function viewComm(id,type){  
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type:"POST",
        data:{id:id,type:type},
        url:base_url + '/comm-view',
        success:function(data){
            if(data.status == 200){
                $("#commviewbody").html('').html(data.page);
                $("#viewcommModal").modal('show');
            }else {
                alertify.error(data.msg);
            }
        }
    });
}
/* End */
/* Add Revenue form validation */
$("#new-revenue-form").validate({
    errorClass: "invalid form-error",
    errorElement: 'div',
    errorPlacement: function(error, element) {
        error.appendTo( element.next("span") );
    },
    ignore: [],
    rules: {
            addrev_dealer: {
                required: true,
            },
            revenue_amount: {
                required: true,
                maxlength:10
            },
            expiry_at: {
                required: true,
                dateFormat:true,
            },
        },
        messages: {
            addrev_dealer: {
                required : "Choose dealer",
            } ,
            revenue_amount: {
                required : "Enter amount",
                maxlength: "Maximum character limit exceeded",
            } ,
            expiry_at: {
                required : "Enter expiry date",
                dateFormat: "Please enter a valid date in the format DD-MM-YYYY",
            } ,
        },
    submitHandler: function (form) {
        var data = $("#new-revenue-form").serialize();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            data:data,
            url: base_url+'/add-revenue',
            dataType:"json",
            success:function(data){
                if(data.status == 200){
                    alertify.success(data.msg);
                    $('#new-revenue-form').trigger("reset");
                }else{
                    $.each(data.msg,function(k,val){
                        alertify.error(val);
                    });
                }
            }
        });
    }
});
/* End */
/* Define clawback validation */
$("#save-clawback-form").validate({
    errorClass: "invalid form-error",
    errorElement: 'div',
    errorPlacement: function(error, element) {
        error.appendTo( element.parent().next("span") );
    },
    ignore: [],
    rules: {
            plan_type: {
                required: true,
            },
            plan_id: {
                required: true,
            },
            period: {
                required: true,
                maxlength:4,
            },
            status: {
                required: true,
            },
        },
        messages: {
            plan_type: {
                required : "Choose plan type",
            } ,
            plan_id: {
                required : "Choose plan name",
            } ,
            period: {
                required : "Enter period",
                maxlength: "Maximum character limit exceeded",
            } ,
            status: {
                required : "Choose commission status",
            } ,
        },
    submitHandler: function (form) {
        var data = $("#save-clawback-form").serialize();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            data:data,
            url: base_url+'/save-clawback',
            dataType:"json",
            success:function(data){
                if(data.status == 200){
                    alertify.success(data.msg);
                    setTimeout(function(){
                        window.location.href = base_url+data.redirect;
                    }, 1500);
                }else{
                    $.each(data.msg,function(k,val){
                        alertify.error(val);
                    });
                }
            }
        });
    }
});
/* End */
/* Clawback Delete */
$(document).on("click",".delete_clawback", function () {
    var id      = $(this).attr('data-id');
    var type    = $(this).attr('data-type');
    alertify.confirm('Delete Confirmation', 'Are you sure to delete the assigned clawback?',
    function(){
        deleteClaw(id,type);
    },function(){ alertify.error('Option cancelled')});
});
function deleteClaw(id,type){  
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type:"POST",
        data:{id:id,type},
        url:base_url + '/delete-clawback',
        success:function(data){
            if(data.status == 200){
                alertify.success(data.msg);
                $('#'+data.table).DataTable().draw();
            }else {
                alertify.error(data.msg);
            }
        }
    });
}
/* End */
/* Add pay commission form validation */
$("#new-paycomm-form").validate({
    errorClass: "invalid form-error",
    errorElement: 'div',
    errorPlacement: function(error, element) {
        error.appendTo( element.next("span") );
    },
    ignore: [],
    rules: {
            pay_dealer: {
                required: true,
            },
            pay_amount: {
                required: true,
                maxlength:10
            },
        },
        messages: {
            pay_dealer: {
                required : "Choose dealer",
            } ,
            pay_amount: {
                required : "Enter amount",
                maxlength: "Maximum character limit exceeded",
            } ,
        },
    submitHandler: function (form) {
        var data = $("#new-paycomm-form").serialize();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            data:data,
            url: base_url+'/pay-commission',
            dataType:"json",
            success:function(data){
                if(data.status == 200){
                    alertify.success(data.msg);
                    $('#new-paycomm-form').trigger("reset");
                    $('#commPayment-table').DataTable().draw(); 
                }else{
                    $.each(data.msg,function(k,val){
                        alertify.error(val);
                    });
                }
            }
        });
    }
});
/* End */

});
});