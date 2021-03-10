@extends('layouts.home')
@section('content')
<style type="text/css">
  .text-danger{
    position: absolute;
    top: 100%;
  }
</style>
<div class="wrapper">
    <div class="container-fluid">
        <!-- <link href="{{ asset('plugins/smartwizard/smart_wizard.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('plugins/smartwizard/smart_wizard_theme_arrows.min.css') }}" rel="stylesheet" type="text/css"/> -->
        <link href="{{ asset('plugins/smartwizard/smart_wizard.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="btn-group pull-right">
                        <ol class="breadcrumb hide-phone p-0 m-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item active">Order Details</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Order Details</h4>
                </div>
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-sm-12">

                <div class="card m-b-20">
                    <div class="card-body">
                        <div id="datatable_filter" class="dataTables_filter m-b-20">
                          <form action="{{ url('/order-details')}}" method="POST" id="search-form">
                          @csrf
                            <div class="row">
                                <div class="col-md-5">
                                  <input type="text" class="form-control" name="sim_number" placeholder="Enter Customer Sim Number" value="{{($sim_number)? $sim_number :'' }}">
                                </div>
                                <div class="col-md-5">
                                  <input type="text" class="form-control" name="order_id" placeholder="Enter Customer Order Id" value="{{($order_id)? $order_id :'' }}">
                                </div>
                                <div class="col-md-1 pull-right"><input type="submit" value="Search" class="btn btn-primary"></div>
                            </div>
                          </form>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($sim_request))
            <div class="col-sm-12">
                <div class="card m-b-20">
                    <div class="card-body">
                        @php $i=0; $active_flag = false; @endphp
                        <table class="table table-striped dt-responsive nowrap table-vertical datatable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th width="25%">Name:  {{$sim_request[0]->user->name}}</th>
                                    <th width="25%">Email: {{$sim_request[0]->user->email}}</th>
                                    <th width="25%">Phone: {{str_replace('+44','0',$sim_request[0]->user->phone)}}</th>
                                    <th width="25%">Date: {{ Helper::date_format($sim_request[0]->created_at) }}</th>
                                </tr>
                            </thead>
                           <tbody>
                            @foreach ($sim_request as $sm_request)
                                <tr class="odd">
                                   <td colspan="2">Sim List: {{$sm_request->getSimList()}}</td>
                                    <td colspan="2">Address: {{$sm_request->getShippingAddress()}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card m-b-20">
                    <div class="card-body"  id="smartwizard">
                        <table class="table table-striped dt-responsive nowrap table-vertical datatable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                  <th>Plan</th>
                                  <th>SIM in Pack</th>
                                  <th>Number</th>
                                  <th>Status</th>
                                  <th>Web Request</th>
                                  <th>Action</th>
                                </tr>
                            </thead>
                           <tbody>
                                @foreach ($sim_list as $sim)
                                @php
                                  $provider = $sim->auto_plan->plan->provider;
                                  $simDetail = $sim->getSimDetails();
                                @endphp
                                <tr>
                                  <td>{{ $sim->auto_plan->plan->plan_name.' ('.$provider.')'}}</td>
                                  <td>{{ $sim->sim_count }}</td>
                                  <td>{{ $simDetail['phone_number'] }}</td>
                                  <td>{{ $simDetail['status'] }}</td>
                                  <td>{{ ($sim->web_request) ? 'Yes':'No'  }}</td>
                                  <td>
                                    <input type="hidden" id="sim_stock_{{ $simDetail['idetifier'] }}" name="act_sim_stock" value="{{ $simDetail['stockId'] }}">
                                    @if($provider == 'O2' || $provider == 'VUK' || $provider == 'EE_O2' || $provider == 'E_SIM')
                                      @if($simDetail['provision'] < 3)
                                      <button class="btn btn-info btn-xs sim_provisioning" data-stock_id="{{ $simDetail['stockId'] }}">Provision</button>
                                      @elseif($simDetail['provision'] == 3)
                                      @if($provider == 'E_SIM')
                                        Activation Pending
                                      @else
                                      <button class="btn btn-info btn-xs check_provision_status" data-id="{{ $simDetail['idetifier'] }}">Check</button>  
                                      @endif                                    
                                      @elseif($simDetail['status']=='Not Active' && $simDetail['provision'] == 4)
                                      <button class="btn btn-warning btn-xs activate_sim" data-id="{{ $simDetail['idetifier'] }}">Activate</button>
                                      @endif
                                    @else
                                      @if($simDetail['status'] == 'Not Active')
                                        <button class="btn btn-warning btn-xs activate_sim" data-id="{{ $simDetail['idetifier'] }}">Activate</button>
                                      @endif
                                    @endif
                                    <button class="btn btn-primary btn-xs sim_detail_view" data-id="{{ $simDetail['idetifier'] }}">Details</button>
                                  </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <input type="hidden" id="act_stock_id" name="act_stock_id" value="">
            <input type="hidden" id="act_request_id" name="act_request_id" value="{{ json_encode($request_id)}}">
            @endif
        </div>
    </div>
</div>

<!-- <script src="{{ asset('plugins/smartwizard/jquery.smartWizard.js') }}"></script> -->
<script src="{{ asset('plugins/smartwizard/smart_wizard.js') }}"></script>
<script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function(){
        // $('.datepicker').datepicker({
        //     autoclose: true,
        //     orientation:'bottom left',
        //     format: 'yyyy-mm-dd',
        //     todayHighlight: true
        // });

        $(document).on('click','.activate_sim',function () {
            var sim_id = $(this).attr('data-id');
            var stock_id = $('#sim_stock_'+sim_id).val();
            var req_id = $('#act_request_id').val();
            $.ajax({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              type: 'POST',
              url: base_url+'/sim-list',
              data : {stock_id:stock_id, req_id:req_id},
              success:function(data){
                if (data.error) {
                  alert(data.message);
                }else{
                  $('#act_stock_id').val(stock_id);
                  $('#smartwizard').html(data.html);
                  $('#smartwizard').smartWizard({
                    selected: 0,
                    theme: 'arrows',
                    transitionEffect:'fade',
                    useURLhash: false,
                    showStepURLhash: false,
                    toolbarSettings: {
                      toolbarButtonPosition: 'right',
                      showPreviousButton: false,
                    },
                    anchorSettings: {
                      anchorClickable: false,
                    },
                  });

                  // $("#smartwizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) {
                  //   if (stepNumber <= 2) {
                  //     var value = $('#wizard-error_'+stepNumber).val();
                  //     if (value != 0) {
                  //       // console.log(stepNumber);
                  //       // alert("Please complete activation before move to next step.");
                  //       // // swal("Error", "Please complete activation before move to next step.", "error");
                  //       // return false;
                  //     }
                  //   }
                  //   return true;
                  // });


                  $("#smartwizard").on("stepContent", function(e, anchorObject, stepIndex) {
                    var time = 500;
                    var value = $('#wizard-error_'+stepIndex).val();
                    // if (value != 0) {
                      // $('.drag-target').html('<div class="alert alert-danger alert-colored" role="alert"><strong>Notification</strong> Please complete all the process before move to next step.</div>');
                      // reject( "An error loading the resource" );
                    // }else{
                      $('#smartwizard').smartWizard("loader", "show");
                      var ajaxURL = anchorObject.data('content-url');
                      var stock_id = $('#act_stock_id').val();
                      var request_id = $('#act_request_id').val();
                      return new Promise((resolve, reject) => {
                        var formData = new FormData($('#wizard-form-'+stepIndex)[0]);
                        formData.append('step', (stepIndex+1));
                        formData.append('stock_id', stock_id);
                        formData.append('request_id', request_id);
                        $.ajax({
                            headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            method  : 'POST',
                            url     : ajaxURL,
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            dataType:'json',
                            beforeSend: function( xhr ) {
                                $('#smartwizard').smartWizard("loader", "show");
                            }
                        }).done(function( res ) {
                            if(res.error){
                              $('.drag-target').html('<div class="alert alert-danger alert-colored" role="alert"><strong>Notification</strong> '+res.message+'</div>');
                            }else{
                              if(res.complete){
                                $('.drag-target').html('<div class="alert alert-success alert-colored" role="alert"><strong>Notification</strong> '+res.message+'</div>');
                                $(".sw-btn-next").hide(); 
                                setInterval(function(){ window.location.href = base_url+'/orders'; }, 3000);
                              }
                              resolve(res.html);
                            }
                            $('#smartwizard').smartWizard("loader", "hide");
                        }).fail(function(err) {
                            reject( "An error loading the resource" );
                            $('#smartwizard').smartWizard("loader", "hide");
                        });
                      });
                      // return new Promise((resolve, reject) => {
                      //     setTimeout( function() {
                      //       resolve("Success!" + (stepIndex + 1))
                      //       $('#smartwizard').smartWizard("loader", "hide");
                      //     }, time)
                      //  });
                      // return true;
                    // }
                  });
                }
              }
            });
        });

        $(document).on('click','.sim_provisioning',function () {
            var stock_id = $(this).attr('data-stock_id');
            var req_id = $('#act_request_id').val();
            $.ajax({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              type: 'POST',
              url: base_url+'/order-provision',
              data : {stock_id:stock_id, req_id:req_id},
              success:function(data){
                if (data.error) {
                  alert(data.message);
                }else{
                  $('#smartwizard').html(data.html);
                }
              }
            });
        });

        $(document).on('click','#provision_request',function () {
            var $this = $(this);
            $this.attr('disabled',true);
            var stock_id = $this.attr('data-stock_id');
            var req_id = $('#act_request_id').val();
            $.ajax({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              type: 'POST',
              url: base_url+'/provision-request',
              data : {stock_id:stock_id, req_id:req_id},
              success:function(data){
                if (data.error) {
                  $this.attr('disabled',false);
                  alert(data.message);
                }else{
                  $('#smartwizard').html(data.html);
                }
              }
            });
        });

        $(document).on('click','#terms-check',function () {
            if($(this).is(':checked')){
                $('input[name="wizard_error"]').val(0);
            }else{
                $('input[name="wizard_error"]').val(1);
            }
        });

        $(document).on('click','.sim_detail_view',function () {
            var sim_id = $(this).attr('data-id');
            var stock_id = $('#sim_stock_'+sim_id).val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: base_url+'/item-detail',
                data: {stock_id:stock_id},
                success:function(data){
                    $('#orderCustomLabel').text('Sim List - Details');
                    if(data.error){
                        $('#orderCustombody').html(data.message);
                    } else {
                       $('#orderCustombody').html(data.html);
                    }
                    $('#orderCustomModal').modal('show');
                }
            });
        });

        $(document).on('change','.connection_type',function () {
            var sim_id = $(this).attr('data-id');
            var status = $(this).val();
            $.ajax({
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: base_url+'/port-request',
                data : {sim_id:sim_id, status:status},
                success:function(data){
                  if (data.error) {
                    alert(data.message);
                  }else{

                  }
                }
            });
        });

        $(document).on('click','.verify_sim_number',function () {
            var $this = $(this);
            var sim_id = $this.data('sim_id');
            var sim_number = $('#sim_number_'+sim_id).val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: base_url+'/verify-sim-number',
                data: {sim_number:sim_number},
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

        $(document).on('click','.check_provision_status',function () {
            var sim_id = $(this).attr('data-id');
            $.ajax({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              type: 'POST',
              url: base_url+'/provision-check',
              data : {sim_id:sim_id},
              success:function(data){
                $('#orderCustomLabel').text('Provision Status');
                if(data.error){
                  $('#orderCustombody').html(data.message);
                } else {
                  $('#orderCustombody').html(data.html);
                }
                $('#orderCustomModal').modal('show');
                setInterval(function(){ location.reload(); }, 5000);
              }
            });
        });

        $(document).on('click', '.verify_pac_code', function () {
            var $this = $(this);
            var sim_id = $this.data('sim_id');
            var pac_code = $('#pac_code_'+sim_id).val();
            var cli = $('#port_cli_'+sim_id).val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: base_url+'/verify-pac-code',
                data: {pac_code:pac_code,list_id:sim_id,cli:cli},
                success:function(data){
                  if (data.error) {
                      $this.addClass('text-danger');
                      $this.attr('title',data.message);
                  }else{
                      // $("#transfer-date").datepicker({
                      //     startDate: '2020-05-01',
                      //     endDate: '2020-05-31'
                      //   });
                      $this.attr('title','');
                      $this.addClass('text-success');
                  }
                }
            });
        });

        $(document).on('click', '#provision_process', function () {
            var $this = $(this);
            if($("#provision-form").valid()){
                $this.prop('disabled', true);
                var provision = $("#provision-form").serialize();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: base_url+'/provision-process',
                    data: {provision:provision},
                    success:function(data){
                        if (data.error) {
                            $this.prop('disabled', false);
                            alert(data.message);
                        } else {
                            location.reload();
                        }
                    }
                });
            }
        });
        $(document).on( 'keypress', '.number', function(event){
            if(event.charCode >= 48 && event.charCode <= 57){
                return true;
            }else{  return false; }
        });
    });
</script>
@endsection
