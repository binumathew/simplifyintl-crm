jQuery(function($) {'use strict';
$(document).ready(function (){
    /*jQuery validation adding custom validation rule */
    // Phone number validation.
    $.validator.addMethod("phoneNumber", function(value, element) {
        return this.optional(element) || /^(?:[1-9]\d*|0)$/i.test(value);
    });
    // website validation.
    $.validator.addMethod("website", function(value, element) {
        return this.optional(element) || /^(((?!-))(xn--|_{1,1})?[a-z0-9-]{0,61}[a-z0-9]{1,1}\.)*(xn--)?([a-z0-9][a-z0-9\-]{0,60}|[a-z0-9-]{1,30}\.[a-z]{2,})$/.test( value );
    });
    /* End */
	/* Custom settings form validation */
	$("#custom_setting_form").validate({

        errorClass: "invalid form-error",
        errorElement: 'div',
        errorPlacement: function(error, element) {
            error.appendTo( element.parent().next("span") );
        },
        submitHandler: function (form) {
            var data = $("#custom_setting_form").serialize();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type:"POST",
                data:data,
                url: base_url+'/update-custom-settings',
                dataType:"json",
                success:function(data){
                    if(data.status == 200){
                        alertify.success(data.msg);
                        setTimeout(function(){
                            location.reload();
                        }, 1500);
                    }else{
                        alertify.error(data.msg);
                    }
                }
            });
        }
    });
    /* End */
    /* Custom settings - conference settings */
    $(document).on( 'change', '.conf_settings', function(event){
        if($(this).prop('checked')) {
            $(this).val(1);
            $(this).attr('checked');
        } else {
            $(this).val(0);
            $(this).removeAttr('checked');
        }
    });
    $(document).on( 'change', '.conf_settings', function(event){
        var elem    = $(this);
        var type    = elem.attr('data-type');
        var param   = elem.attr("name");
        var toggle  = elem.val(); //toggle
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            data:{type:type,param:param,toggle:toggle},
            url:base_url + '/custom-changesettings',
            success:function(data){
                if(data.status == 200){
                    alertify.success(data.msg);
                }else{
                   alertify.error(data.msg);
                }                
            }
        });
    });
    /* End */
    /* Custom settings company form validation */
    $("#company_settings_form").validate({

        errorClass: "invalid form-error",
        errorElement: 'div',
        errorPlacement: function(error, element) {
            error.appendTo( element.parent().next("span") );
        },
        rules: {
            company_name: {
                required: true,
                maxlength:50,
            },
            company_website: {
                required: true,
                maxlength:50,
                website:true,
            },
            company_phone: {
                required: true,
                minlength:8,
                maxlength:15,
                phoneNumber:true,
            },
            company_email: {
                required: true,
                maxlength:30,
                email:true,
            },
            company_street: {
                required: function(element) {
                return $("#company_street").val();
                },
                minlength:3,
                maxlength:30,
            },
            company_city: {
                required: function(element) {
                return $("#company_city").val();
                },
                minlength:3,
                maxlength:30,
            },
            company_state: {
                required: function(element) {
                return $("#company_state").val();
                },
                minlength:3,
                maxlength:30,
            },
            company_country: {
                required: true,
            },
            company_postcode: {
                required: function(element) {
                return $("#company_postcode").val();
                },
                minlength:3,
                maxlength:15,
            },
            company_vat: {
                required: function(element) {
                return $("#company_vat").val();
                },
                minlength:3,
                maxlength:20,
            },
            company_dialcode: {
                required: true,
                maxlength:6,
            },
            company_cntry_code: {
                required: true,
                maxlength:3,
            },
        },
        messages: {
            company_name: {
                required : "Enter your company name",
                maxlength: "Maximum character limit exceeded",
            } ,
            company_website: {
                required : "Enter your company website",
                maxlength: "Maximum character limit exceeded",
                website: "Enter a valid web address",
            } ,
            company_phone: {
                required : "Enter your company phone",
                minlength: "Enter a valid phone number",
                maxlength: "Maximum character limit exceeded",
                phoneNumber:"Enter a valid phone number",
            } ,
            company_email: {
                required : "Enter your company email",
                maxlength: "Maximum character limit exceeded",
                email:"Enter a valid email address",
            } ,
            company_street: {
                required : "Enter your company street address",
                maxlength: "Maximum character limit exceeded",
                minlength:"Enter a valid street address",
            } ,
            company_city: {
                required : "Enter your company city",
                maxlength: "Maximum character limit exceeded",
                minlength:"Enter a valid city",
            } ,
            company_state: {
                required : "Enter your company state",
                maxlength: "Maximum character limit exceeded",
                minlength:"Enter a valid state",
            } ,
            company_country: {
                required : "Choose your country",
            } ,
            company_postcode: {
                required : "Enter your company postcode",
                maxlength: "Maximum character limit exceeded",
                minlength:"Enter a valid postcode",
            } ,
            company_vat: {
                required : "Enter your company vat number",
                maxlength: "Maximum character limit exceeded",
                minlength:"Enter a valid vat number",
            } ,
            company_dialcode: {
                required : "Enter your company dial code",
                maxlength: "Maximum character limit exceeded",
            } ,
            company_cntry_code: {
                required : "Enter your company country code",
                maxlength: "Maximum character limit exceeded",
            } ,
        },
        submitHandler: function (form) {
            var data = $("#company_settings_form").serialize();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type:"POST",
                data:data,
                url: base_url+'/update-company-settings',
                dataType:"json",
                success:function(data){
                    if(data.status == 200){
                        alertify.success(data.msg);
                        setTimeout(function(){
                            location.reload();
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

    /* Custom settings company country change */
    $(document).on("change","#company_country", function () {
        var elem        = $( "#company_country option:selected" );
        var dialcode    = elem.attr('data-dialcode');
        var shortcode   = elem.attr('data-shortcode');
        $("#company_dialcode").val(dialcode).focus();
        $("#company_cntry_code").val(shortcode).focus();
        $("#company_postcode").focus();
    });
    /* End */
    /* company images upload */
    $(document).on('click', '.company_images', function() {
        var type = $(this).attr('data-type');
        $(document).on('change', ':file', function() {
            var input = $(this),
            file_data = input.prop('files')[0];
            alertify.confirm('Image change Confirmation', 'Are you sure change images?',
            function(){
                changeCompanyImages(file_data,type);
            },function(){ input.val(''); alertify.error('Option cancelled')});
        });
    });
    function changeCompanyImages(file_data,type){  
        var form_data = new FormData();                  
        form_data.append('images', file_data);
        form_data.append('type', type);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            data:form_data,
            processData: false,
            contentType: false,
            url:base_url + '/upload-company-images',
            success:function(data){
                if(data.status == 200){
                    alertify.success(data.msg);
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                }else {
                    $.each(data.msg,function(k,val){
                        alertify.error(val);
                    });
                }
            }
        });
    }
    /*End */
    /* Add Settings Popup */
    $(document).on("click","#add_settings", function () {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            url: base_url+'/add-settings-form',
            dataType:"json",
            success:function(data){
                if(data.status == 200){
                    $("#addSettingsLabel").html('').html(data.title);
                    $("#addSettingsbody").html('').html(data.page);
                    $("#addSettingsModal").modal('show');
                }else{
                    alertify.error(data.msg);
                }
            }
        });
        
    });
    /*End */
    /* add settings form validation */
    $(document).on("click","#addsettBtn", function () {
        $("#add_settings_form").validate({ 
            errorClass: "invalid form-error",
            errorElement: 'div',
            errorPlacement: function(error, element) {
                error.appendTo( element.parent().next("span") );
            },             
            rules: {
              option_name: {
                required: true,
              },
              option_value: {
                required: true,
              }
            },
            messages: {
                option_name: {
                    required : "Name is required",
                } ,
                option_value: {
                    required : "Value is required",
                } ,
            },
        }).form(); 
        if($("#add_settings_form").valid()) {
            var data = $("#add_settings_form").serialize();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type:"POST",
                data:data,
                url: base_url+'/add-settings-data',
                dataType:"json",
                success:function(data){
                    if(data.status == 200){
                        alertify.success(data.msg);
                        setTimeout(function(){
                            location.reload();
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
    /* add settings cancel button */
    $(document).on("click","#addsettcancelBtn", function () {
        $("#addSettingsModal").modal('hide');
    });
    /* End */
    /* Custom settings sms form validation */
    $("#custom_sms_form").validate({

        errorClass: "invalid form-error",
        errorElement: 'div',
        errorPlacement: function(error, element) {
            error.appendTo( element.parent().next("span") );
        },
        submitHandler: function (form) {
            var data = $("#custom_sms_form").serialize();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type:"POST",
                data:data,
                url: base_url+'/update-sms-settings',
                dataType:"json",
                success:function(data){
                    if(data.status == 200){
                        alertify.success(data.msg);
                        setTimeout(function(){
                            location.reload();
                        }, 1500);
                    }else{
                        alertify.error(data.msg);
                    }
                }
            });
        }
    });
    /* End */
    /* Custom settings switch form validation */
    $("#custom_switch_form").validate({

        errorClass: "invalid form-error",
        errorElement: 'div',
        errorPlacement: function(error, element) {
            error.appendTo( element.parent().next("span") );
        },
        submitHandler: function (form) {
            var data = $("#custom_switch_form").serialize();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type:"POST",
                data:data,
                url: base_url+'/update-switch-settings',
                dataType:"json",
                success:function(data){
                    if(data.status == 200){
                        alertify.success(data.msg);
                        setTimeout(function(){
                            location.reload();
                        }, 1500);
                    }else{
                        alertify.error(data.msg);
                    }
                }
            });
        }
    });
    /* End */
    /* Save Email template */
    $(document).on("click","#saveEmailTemplate", function () {
        var emailcontent = tinymce.get("email_template_editor").getContent();
        var edit_id   = $("#edit_id").val();
        var temp_name = $("#template_name").val();
        if(temp_name == ""){ alertify.error('Please provide template name');}
        else if(emailcontent == ""){ alertify.error('Please provide content');}
        else{
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type:"POST",
                data:{editid:edit_id,tempname:temp_name,emailcontent:emailcontent},
                url: base_url+'/email-template-actions',
                dataType:"json",
                success:function(data){
                    if(data.status == 200){
                        alertify.success(data.msg);
                        setTimeout(function(){
                            location.reload();
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
    /*End */
    /* Delete Email template */
    $(document).on("click",".delete_template", function () {
        var tempid    = $(this).attr('data-id');
        var tempname  = $(this).attr('data-tempname');
        alertify.confirm('Delete Confirmation', 'Are you sure delete the '+tempname+' template?',
        function(){
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type:"POST",
                data:{tempid:tempid},
                url: base_url+'/email-template-delete',
                dataType:"json",
                success:function(data){
                    if(data.status == 200){
                        alertify.success(data.msg);
                        setTimeout(function(){
                            location.reload();
                        }, 1500);
                    }else{
                        alertify.success(data.msg);
                    }
                }
            });  
        },function(){ alertify.error('Option cancelled')});
    });
    /* End */
});
});