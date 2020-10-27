jQuery(function($) {'use strict';
$(document).ready(function (){


/* Sim Plan validation */
$("#sim-plans-actions-form").validate({
    errorClass: "invalid form-error",
    errorElement: 'div',
    errorPlacement: function(error, element) {
        error.appendTo( element.parent().next("span") );
    },
    rules: {
            plan_name: {
                required: true,
                maxlength:50,
            },
            switch_billing_plan: {
                required: true,
                maxlength:10,
            },
            sim_billing_plan: {
                required: true,
                maxlength:10,
            },
            description: {
                required: true,
                maxlength:150,
            },
            buy_price: {
                required: true,
                maxlength:8,
            },
            sell_price: {
                required: true,
                maxlength:8,
            },
            data_limit: {
                required: true,
                maxlength:25,
            },
            call_limit: {
                required: true,
                maxlength:25,
            },
            in_call_limit: {
                required: true,
                maxlength:25,
            },
            msg_limit: {
                required: true,
                maxlength:25,
            },
            status: {
                required: true,
            },
            period: {
                required: true,
                maxlength:8,
            },
            provider: {
                required: true,
            },
            dealer_id: {
                required: true,
            },
            listed: {
                required: true,
                maxlength:8,
            },
            order_by: {
                required: true,
                maxlength:8,
            },
        },
        messages: {
            plan_name: {
                required : "Enter plan name",
                maxlength: "Maximum character limit exceeded",
            } ,
            switch_billing_plan: {
                required : "Enter switch billing plan",
                maxlength: "Maximum character limit exceeded",
            } ,
            sim_billing_plan: {
                required : "Enter sim billing plan",
                maxlength: "Maximum character limit exceeded",
            } ,
            description: {
                required : "Enter description",
                maxlength: "Maximum character limit exceeded",
            } ,
            buy_price: {
                required : "Enter buy price",
                maxlength: "Maximum character limit exceeded",
            } ,
            sell_price: {
                required : "Enter sell price",
                maxlength: "Maximum character limit exceeded",
            } ,
            data_limit: {
                required : "Enter data limit",
                maxlength: "Maximum character limit exceeded",
            } ,
            call_limit: {
                required : "Enter call limit",
                maxlength: "Maximum character limit exceeded",
            } ,
            in_call_limit: {
                required : "Enter international call limit",
                maxlength: "Maximum character limit exceeded",
            } ,
            status: {
                required : "Choose status",
            } ,
            msg_limit: {
                required : "Enter message limit",
                maxlength: "Maximum character limit exceeded",
            } ,
            period: {
                required : "Enter period",
                maxlength: "Maximum character limit exceeded",
            } ,
            provider: {
                required : "Choose provider",
            } ,
            dealer_id: {
                required : "Choose Dealer",
            } ,
            listed: {
                required : "Enter listed",
                maxlength: "Maximum character limit exceeded",
            } ,
            order_by: {
                required : "Enter order by",
                maxlength: "Maximum character limit exceeded",
            } ,
        },
    submitHandler: function (form) {
        var data = $("#sim-plans-actions-form").serialize();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            data:data,
            url: base_url+'/sim-plans-actions',
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

/* Plan Delete */
$(document).on("click",".delete_plan", function () {
	var id = $(this).attr('data-id');
	var type = $(this).attr('data-type');
	alertify.confirm('Delete Confirmation', 'Are you sure delete the plan?',
    function(){
        deletePlans(id,type);
    },function(){ alertify.error('Option cancelled')});
});
function deletePlans(id,type){  
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type:"POST",
        data:{id:id,type:type},
        url:base_url + '/plan-delete',
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
/* View Plan */
$(document).on("click",".view_plan", function () {
	var id = $(this).attr('data-id');
	var type = $(this).attr('data-type');
	viewPlan(id,type);
});
function viewPlan(id,type){  
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type:"POST",
        data:{id:id,type:type},
        url:base_url + '/plan-view',
        success:function(data){
            if(data.status == 200){
                $("#planviewbody").html('').html(data.page);
                $("#viewplanModal").modal('show');
            }else {
                alertify.error(data.msg);
            }
        }
    });
}
/* End */
/* Switch Plan validation */
$("#switch-plans-actions-form").validate({
    errorClass: "invalid form-error",
    errorElement: 'div',
    errorPlacement: function(error, element) {
        error.appendTo( element.parent().next("span") );
    },
    rules: {
            plan_name: {
                required: true,
                maxlength:50,
            },
            switch_billing_plan: {
                required: true,
                maxlength:10,
            },
            description: {
                required: true,
                maxlength:150,
            },
            buy_price: {
                required: true,
                maxlength:8,
            },
            sell_price: {
                required: true,
                maxlength:8,
            },
            minutes: {
                required: true,
                maxlength:12,
            },
            period: {
                required: true,
                maxlength:8,
            },
            flag: {
                required: true,
                maxlength:25,
            },
            switch_id: {
                required: true,
                maxlength:8,
            },
            in_app_id: {
                required: true,
                maxlength:25,
            },
            plan_type: {
                required: true,
                maxlength:5,
            },
            rate_minutes: {
                required: true,
                maxlength:10,
            },
            dealer_id: {
                required: true,
            },
        },
        messages: {
            plan_name: {
                required : "Enter plan name",
                maxlength: "Maximum character limit exceeded",
            } ,
            switch_billing_plan: {
                required : "Enter switch billing plan",
                maxlength: "Maximum character limit exceeded",
            } ,
            description: {
                required : "Enter description",
                maxlength: "Maximum character limit exceeded",
            } ,
            buy_price: {
                required : "Enter buy price",
                maxlength: "Maximum character limit exceeded",
            } ,
            sell_price: {
                required : "Enter sell price",
                maxlength: "Maximum character limit exceeded",
            } ,
            minutes: {
                required : "Enter Minutes",
                maxlength: "Maximum character limit exceeded",
            } ,
            period: {
                required : "Enter period",
                maxlength: "Maximum character limit exceeded",
            } ,
            flag: {
                required : "Enter flag details",
                maxlength: "Maximum character limit exceeded",
            } ,
            switch_id: {
                required : "Enter switch id",
                maxlength: "Maximum character limit exceeded",
            } ,
            plan_type: {
                required : "Enter plan type",
                maxlength: "Maximum character limit exceeded",
            } ,
            in_app_id: {
                required : "Enter APP id",
                maxlength: "Maximum character limit exceeded",
            } ,
            rate_minutes: {
                required : "Enter rates minutes",
                maxlength: "Maximum character limit exceeded",
            } ,
            dealer_id: {
                required : "Choose Dealer",
            },
        },
    submitHandler: function (form) {
        var data = $("#switch-plans-actions-form").serialize();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            data:data,
            url: base_url+'/switch-plans-actions',
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
/* Conference Plan validation */
$("#conf-plans-actions-form").validate({
    errorClass: "invalid form-error",
    errorElement: 'div',
    errorPlacement: function(error, element) {
        error.appendTo( element.parent().next("span") );
    },
    rules: {
            plan_name: {
                required: true,
                maxlength:50,
            },
            switch_billing_plan: {
                required: true,
                maxlength:10,
            },
            description: {
                required: true,
                maxlength:150,
            },
            buy_price: {
                required: true,
                maxlength:8,
            },
            sell_price: {
                required: true,
                maxlength:8,
            },
            minutes: {
                required: true,
                maxlength:12,
            },
            period: {
                required: true,
                maxlength:8,
            },
            flag: {
                required: true,
                maxlength:25,
            },
            switch_id: {
                required: true,
                maxlength:8,
            },
            in_app_id: {
                required: true,
                maxlength:25,
            },
            provider: {
                required: true,
                maxlength:25,
            },
            participant_limit: {
                required: true,
                maxlength:5,
            },
            plan_term_annual: {
                required: true,
                maxlength:10,
            },
            status: {
                required: true,
            },
        },
        messages: {
            plan_name: {
                required : "Enter plan name",
                maxlength: "Maximum character limit exceeded",
            } ,
            switch_billing_plan: {
                required : "Enter switch billing plan",
                maxlength: "Maximum character limit exceeded",
            } ,
            description: {
                required : "Enter description",
                maxlength: "Maximum character limit exceeded",
            } ,
            buy_price: {
                required : "Enter buy price",
                maxlength: "Maximum character limit exceeded",
            } ,
            sell_price: {
                required : "Enter sell price",
                maxlength: "Maximum character limit exceeded",
            } ,
            minutes: {
                required : "Enter Minutes",
                maxlength: "Maximum character limit exceeded",
            } ,
            period: {
                required : "Enter period",
                maxlength: "Maximum character limit exceeded",
            } ,
            flag: {
                required : "Enter flag details",
                maxlength: "Maximum character limit exceeded",
            } ,
            switch_id: {
                required : "Enter switch id",
                maxlength: "Maximum character limit exceeded",
            } ,
            provider: {
                required : "Enter provider",
                maxlength: "Maximum character limit exceeded",
            } ,
            in_app_id: {
                required : "Enter APP id",
                maxlength: "Maximum character limit exceeded",
            } ,
            participant_limit: {
                required : "Enter participant limit",
                maxlength: "Maximum character limit exceeded",
            } ,
            plan_term_annual: {
                required : "Enter plan term annual",
                maxlength: "Maximum character limit exceeded",
            } ,
            status: {
                required : "Enter status",
                maxlength: "Maximum character limit exceeded",
            } ,
        },
    submitHandler: function (form) {
        var data = $("#conf-plans-actions-form").serialize();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            data:data,
            url: base_url+'/conf-plans-actions',
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

});
});