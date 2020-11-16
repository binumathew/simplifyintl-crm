@extends('layouts.home')
@section('content')
<!-- page wrapper start -->
<div class="wrapper">
    <div class="container-fluid">
    	<link href="{{ asset('plugins/smartwizard/smart_wizard.css') }}" rel="stylesheet" type="text/css"/>
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="btn-group pull-right">
                        <ol class="breadcrumb hide-phone p-0 m-0">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Direct Debit</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Direct Debit</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-20">
                    <div class="card-body">
                    	<div id="directdebitWizard">
						    <ul class="nav">
						       <li>
						           <a class="nav-link" href="#step-1" data-repo="customer">
						              Customer Details
						           </a>
						       </li>
						       <li>
						           <a class="nav-link" href="#step-2" data-repo="bank">
						              Bank Details
						           </a>
						       </li>
                   <li>
                       <a class="nav-link" href="#step-3" data-repo="mandate">
                          Direct Debit Setup
                       </a>
                   </li>
						    </ul>

						    <div class="tab-content">
						       <div id="step-1" class="tab-pane" role="tabpanel">
							    <div class="m-b-20">
							    	<form id="direct-debit-form-1">
							    		{!! $customer !!}
							    	</form>
	    						</div>
						       </div>
						       <div id="step-2" class="tab-pane" role="tabpanel">
						          <div class="m-b-20">
							    	<form id="direct-debit-form-2">

							    	</form>
	    						</div>
						       </div>
						       <div id="step-3" class="tab-pane" role="tabpanel">
						          <form id="direct-debit-form-3">

                    </form>
						       </div>
						    </div>
						</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="directdebitModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title mt-0" id="myModalLabel">Direct Debit Setup</h5>
          </div>
          <div class="modal-body directdebitModalbody">
          </div>
      </div>
  </div>
</div>
<!-- page wrapper end -->
<script src="{{ asset('plugins/smartwizard/smart_wizard.js') }}"></script>
<script>
$(document).ready(function(){
    var btnFinish = $('<button></button>').text('Finish')
                   .addClass('btn btn-info finishBtn');

    var wizard  = $('#directdebitWizard');

	// SmartWizard initialize
	wizard.smartWizard({
      selected: 0,
      theme:'arrows',
      transitionEffect:'fade',
      autoAdjustHeight: true,
      enableFinishButton: true,
      enableURLhash:false,
      justified:false,
      transition: {
          animation: 'slide-horizontal',
      },
      toolbarSettings: {
      	  showPreviousButton:false,
          toolbarPosition: 'bottom', // both bottom
          toolbarExtraButtons: [btnFinish]
      },
      anchorSettings: {
        anchorClickable: false, // Enable/Disable anchor navigation
      }
  	});
    $(document).on('click','.finishBtn',function(e){
      $(this).prop('disabled',true);
      e.preventDefault();
        var $this       = $('.finishBtn');
        var formData    = new FormData($("#direct-debit-form-3")[0]);
        formData.append('step', 3);
        $.ajax({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          type: 'POST',
          url: base_url+'/direct-debit-manage',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          beforeSend: function(){
              $this.html('Processing..');
              $this.addClass("disabled").prop("disabled", true);
          },
          complete: function(){
              $this.html('Finish');
              //$this.removeClass("disabled").prop("disabled", false);
          },
          success:function(data){
              $(".directdebitModalbody").html('').html(data.page);
              $('#directdebitModal').modal({backdrop: 'static', keyboard: false})
              $("#directdebitModal").modal('show');
              //if(data.status == 200) {
                  //alertify.success(data.message);
              //}else{
                  //alertify.error(data.message);
              //}
          }
        });
    });

  	wizard.on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) {
  		if(stepDirection == 'forward'){
        	if(!$("#direct-debit-form-"+(stepNumber+1)).valid()){
        		return false;
        	}
        }
  	});
    wizard.on("showStep", function(e, anchorObject, stepNumber, stepDirection, stepPosition) {
        $('.finishBtn').hide();
        $('.sw-btn-next').show();
        if(stepPosition == 'last'){
          $('.finishBtn').show();
          $('.sw-btn-next').hide();
        }
    });

  	wizard.on("stepContent", function(e, anchorObject, stepIndex, stepDirection, stepPosition) {
  		  var time = 500;
        wizard.smartWizard("loader", "show");
        var ajaxURL  = base_url + '/direct-debit-manage';

	    return new Promise((resolve, reject) => {
          var formData = new FormData($('#direct-debit-form-'+stepIndex)[0]);
          formData.append('step', (stepIndex));
          //formData.append('user_id', $('input[name="user_id"]').val());
	            // Ajax call to fetch your content
	            $.ajax({
	            	headers: {
	                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	                },
	                method  : "POST",
	                url     : ajaxURL,
	                data    : formData,
	                cache	: false,
                    contentType: false,
                    processData: false,
                    dataType:'json',
	                beforeSend: function( xhr ) {
	                    // Show the loader
	                    wizard.smartWizard("loader", "show");
	                }
	            }).done(function( res ) {
	                // console.log(res);
	                if(res.status == 200){
                        alertify.success(res.msg);
                        $("#direct-debit-form-"+(stepIndex+1)).html(res.page);
                        resolve();
	                }else{
	                	alertify.error(res.msg);
	                }
	                // Hide the loader
	                wizard.smartWizard("loader", "hide");
	            }).fail(function(err) {
	              // Reject the Promise with error message to show as tab content
	              reject( "An error loading the resource" );
	              // Hide the loader
	              wizard.smartWizard("loader", "hide");
	            });

	    });
	        // return new Promise((resolve, reject) => {
	        //    setTimeout( function() {
	        //      resolve("Success!" + (stepIndex + 1))  // Yay! Everything went well!
	        //      $('#smartwizard').smartWizard("loader", "hide");
	        //    }, time)
	        // });
	        //return true;
  	});

  	$("#direct-debit-form-1").validate({
        // errorClass: "invalid form-error",
        // errorElement: 'div',
        // errorPlacement: function(error, element) {
        //     element.addClass('border border-danger');
        //     error.insertAfter(element);
        // },
        ignore: ":hidden",
        rules: {
            first_name:{
                required: true,
                maxlength:25,
            },
            last_name:{
                required: true,
                maxlength:25,
            },
            country_id: {
                required: true,
            },
            email: {
                required: true,
                email:true,
                maxlength: 50,
            },
            address: {
                required: true,
                maxlength: 50,
            },
            city: {
                required: true,
                maxlength:25,
            },
            postal_code: {
                required: true,
                // regex:/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9][A-Za-z]?))))\s?[0-9][A-Za-z]{2})$/
            },
        },
    });

  	$("#direct-debit-form-2").validate({
        // errorClass: "invalid form-error",
        // errorElement: 'div',
        // errorPlacement: function(error, element) {
        //     element.addClass('border border-danger');
        //     error.insertAfter(element);
        // },
        ignore: ":hidden",
        rules: {
            first_name:{
                required: true,
                maxlength:25,
            },
            last_name:{
                required: true,
                maxlength:25,
            },
            country_id: {
                required: true,
            },
            account_no: {
                required: {
                    depends: function () {
                      return ($("input[name='iban']"). val() == '' )?true:false;
                    }
                },
                number:true,
                maxlength: 20,
            },
            branch_code: {
                required: {
                    depends: function () {
                      return ($("input[name='iban']"). val() == '')?true:false;
                    }
                },
                maxlength: 12,
                number:true,
            },
            iban: {
                required: {
                    depends: function () {
                      return ($("input[name='account_no']"). val() == '' )?true:false;
                    }
                },
                maxlength:35,
            },
        },
    });

	$.validator.addMethod("regex",function(value, element, regexp) {
		var re = new RegExp(regexp);
        return this.optional(element) || re.test(value);
    }, "Please check your input.");
});
</script>
@endsection
