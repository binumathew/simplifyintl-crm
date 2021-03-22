@extends('layouts.home')
@section('content')
    <style type="text/css">
        .custom-select {
            width: auto !important;
        }
    </style>
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
            <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a></li>
                                <li class="breadcrumb-item active">Delivery Management</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Delivery Management - <small>Orders Awaiting Processing</small></h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card m-b-20">
                        <div class="card-body">

                            <select id="delivery_filter" class="custom-select" style="float: right;">
                                <option value="1" selected>Request Recieved</option>
                                <option value="2">Packed / Shipped</option>
                                <option value="4">Canceled</option>
                                <option value="5">Canceled & Refund</option>
                                <option value="3">All</option>
                            </select>
                            <table id="orderlist" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Order ID</th>
                                        <th>Order Date</th>
                                        <th>Customer</th>
                                        <th>Shipping Address</th>
                                        <th>SIM in Pack</th>
                                        <th>Agent</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>

                            <div class="row pull-right">
                                <div class="col-md-12">
                                    <button class="btn btn-primary" id="bulk_pack">Bulk Shipment</button>
                                    <button class="btn btn-primary print_welcome_letter">Print</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="enquiry-history"></div>

    <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->

    <!-- Find addess popup start -->
    <div id="addressChangeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0">Update Shipping Address</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="address_simlist_id" id="address_simlist_id">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <form id="address-form" class="form-row">
                                <div class="form-group col-md-6">
                                    <input type="text" id="first_name" name="first_name" placeholder="First Name" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <input type="text" id="last_name" name="last_name" placeholder="Last Name" class="form-control">
                                </div>
                                <div class="form-group col-md-5">
                                    <input type="text" id="postal_code" name="postal_code" placeholder="Postal Code" class="form-control">
                                    <div id="postalcode-error" class="text-danger"></div>
                                </div>
                                <div class="form-group col-md-5">

                                      <input type="text" class="form-control" id="house_no" name="house_no" placeholder="House No">
                                </div>
                                <div class="form-group col-md-2">
                                    <button type="button" class="btn btn-success pull-right find_address">Find Now</button>
                                </div>
                                <div class="form-group col-md-12">
                                    <select  class="form-control d-none" id="delivery_address"></select>
                                    <div class="text-danger" id="delivery_address_error"></div>
                                </div>
                                <div class="form-group col-md-12 shipping_address d-none">
                                    <input type="text" class="form-control" id="shipping_street" name="shipping_street" placeholder="Street Address">
                                </div>
                                <div class="form-group col-md-6 shipping_address d-none">
                                    <input type="text" class="form-control" id="shipping_city" name="shipping_city" placeholder="City">
                                </div>
                                <div class="form-group col-md-6 shipping_address d-none">
                                    <input type="text" class="form-control" id="shipping_country" name="shipping_country" placeholder="Country">
                                </div>
                                <div class="form-group col-md-12 shipping_address d-none">
                                    <div id="update_shipping_status" class="text-danger"></div>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" id="update_delivery_address" class="btn btn-success pull-right">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Find addess popup end -->


    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/jquery.print.js') }}"></script>


    <script type="text/javascript">
        $(document).ready(function(){
            $('#orderlist').DataTable({
                responsive: true,
                "bSort" : true,
                pageLength: 25,
                language: { search: "" },
                processing: true,
                serverSide: true,
                "ajax": {
                    "url": "delivery-list",
                    "data": function ( d ) {
                        d.filter_type = $('#delivery_filter').val();
                    }
                },
                "dataType": "jsonp",
                "columns": [
                {"data": function(data){
                    return '<div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input request_list_chkbx" id="request_id_'+ data.id +'" name="request_id[]" value="'+ data.id +'"><label class="custom-control-label" for="request_id_'+ data.id +'"></label></div>';
                    return '<input type="checkbox" name="request_id[]" value="'+ data.id +'" class="request_list_chkbx">';
                }, "orderable": false, "searchable": false, "name":"_id" },
                {"data": "order_id", "name": "rq.order_id"},
                {"data": "created_at", "name": "rq.created_at"},
                {"data": "name", "name": "usr.name"},
                {"data": function(data){
                    var htmlDecode =$.parseHTML(data.shipping_address)[0]['wholeText'];
                    var address = JSON.parse(htmlDecode);
                    return address.street+', '+address.city+', '+address.country+', '+address.postal_code.toUpperCase();
                }, "name": "rq.shipping_address"},
                {"data" : "sim_count","name":"sim_count", "searchable": false},
                {"data" : "short_code","name":"short_code", "searchable": false },
                {"data": function ( data ) {
                    switch(data.delivery_status){
                        case 0:
                        return '<select id="delivery_item_status_'+data.id+'" class="custom-select delivery_item_status" data-id="'+ data.id +'" data-value="'+ data.delivery_status +'"><option value="0" selected="selected">Request Recieved</option><option value="1">Packed / Shipped</option><option value="4">Canceled</option></select>';
                        break;
                        case '1':
                        return '<select id="delivery_item_status_'+data.id+'" class="custom-select delivery_item_status" data-id="'+ data.id +'" data-value="'+ data.delivery_status +'"><option value="1" selected>Packed / Shipped</option><option value="4">Canceled</option></select>';
                        break;
                        break;
                        case '2':
                        return '<span class="badge badge-info">Callback</span>';
                        break;
                        case '3':
                        return '<span class="badge badge-success">Completed</span>';
                        break;
                        case '4':
                        return '<span class="badge badge-danger">Canceled</span>';
                        break;
                    }

                }, "name": "rq.delivery_status", "searchable": false},
                {"data": function(data){
                    var html_data = '<a href="javascript:void(0);" class="text-muted order_status" data-toggle="tooltip" data-id="'+data.id+'" title="View Status"><i class="mdi mdi-eye mdi-24px"></i></a>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" class="text-muted enquiry_history" data-id="'+data.id+'"data-toggle="tooltip" title="Enquiry History"><i class="mdi mdi-history mdi-24px"></i></a> &nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" class="text-muted sim_details sim_detail_btn_'+data.id+'"  data-id="'+data.id+'" data-toggle="tooltip" title="SIM Details"><i class="mdi mdi-information-outline mdi-24px"></i></a>';
                    // if(data.delivery_status <= 1 ||){
                        html_data += '&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" class="re_delivery text-muted m-r-10" data-toggle="tooltip" data-placement="top" title="Re Delivery" data-id="'+data.id+'"><i class="mdi mdi-truck-delivery mdi-24px"></i></a>';
                    // }
                    if (data.print_status == 0) {
                        html_data += '&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" class="text-muted print_welcome_letter" data-toggle="tooltip" title="Print" data-id="'+data.id+'"><i class="mdi mdi-printer mdi-24px"></i></a>';
                    }
                    if(data.total_amount != 0 && data.total_amount != null && data.delivery_status != 5){
                        html_data += '&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" class="text-muted order_refund" data-total-amount="'+data.total_amount+'" data-toggle="tooltip" title="Cancel order and refund" data-id="'+data.id+'"><i class="mdi mdi-undo-variant mdi-24px"></i></a>';
                    }
                    return html_data;

                }, 'name': 'action','orderable': false, 'searchable': false},
                ],
                'order':[[2, 'desc']],
                'columnDefs': [
                { 'targets': 6,'width': '110px'},
                {'defaultContent': '-','targets': '_all'}
                ],
            });

            $('.dataTables_filter input').attr("placeholder", "Search");

            $(document).on('change','#delivery_filter',function () {
                $('#orderlist').DataTable().draw();
            });

            $(document).on('change','.delivery_item_status', function () {
                var id = $(this).attr('data-id');
                if ($(this).val() == 1) {
                    $('#orderCustomLabel').text('Update Order Status');
                    $('#orderCustombody').html('<div class="form-group"><label class="form-label">Shipping Via</label> <select id="shiping_process" class="form-control" name="shiping_process"> <option value="Royal Mail First Class Delivery" selected>Royal Mail First Class</option> <option value="Royal Mail Special Delivery">Royal Mail Special Delivery</option> <option value="Royal Mail Next Day Delivery">Royal Mail Next Day Delivery</option><option value="Soft Delivery">Soft Delivery</option> <option value="other">Other</option> </select></div> <div class="form-group d-none other_delivery"> <input type="text" name="shiping_agent" id="shiping_agent" class="form-control" required placeholder="Shipping Agent Name" value="Royal Mail First Class Delivery"></div> <div id="custom_status"></div> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="action_shipping" data-id="'+ id +'" class="btn btn-success pull-right">Save & Send</button>');
                    $('#orderCustomModal').modal('show');
                }else{
                    $('#orderCustomLabel').text('Order Cancellation');
                    $('#orderCustombody').html('<div class="form-group"> <label class="form-label">Cancellation Reasons</label> <input type="text" name="cancel_reason" id="cancel_reason" class="form-control" required placeholder="Cancellation Reasons"> <div id="custom_status"> </div> </div> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="action_cancel_order" data-id="'+ id +'" class="btn btn-success pull-right">Save</button>');
                    $('#orderCustomModal').modal('show');
                }
                $('#orderlist').DataTable().draw();
            });

            $(document).on('click','#action_cancel_order',function (e) {
                var id =  $(this).attr('data-id');
                var reason = $('#cancel_reason').val();
                $('#custom_status').html('');
                if (reason == '') {
                    $('#custom_status').html('<div class="text-danger">Please enter the reason</div>');
                } else {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: base_url+'/cancel-order',
                        data: {id:id,reason:reason},
                        success:function(data){
                            $('#preloader').hide();
                            if (data.error) {
                                $('#custom_status').html('<div class="text-danger">'+data.message+'</div>');
                            } else {
                                $('#custom_status').html('<div class="text-success">Order cacceled successfully</div>');
                                $('#orderCustomModal').modal('hide');
                            }
                        }
                    });
                }
            });

            $(document).on('click','.order_status',function (e) {
                var id = $(this).attr('data-id');
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: 'order-status',
                    data: {id:id},
                    success:function(data){
                        if(data.error){
                            $('#enquiry-history').html('<div class="alert alert-danger">'+data.message+'</div>');
                        } else {
                            $('#enquiry-history').html(data.html);
                        }
                        $('#oderStatusModal').modal('show');
                    }
                });
            });

            $(document).on('click','.enquiry_history',function (e) {
                var id = $(this).attr('data-id');
                $('#enquiry_status').html('');
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: base_url+'/enquiry',
                    data: {id:id},
                    success:function(data){
                        if (data.error) {
                            $('#enquiry-history').html('<div class="alert alert-danger">'+data.message+'</div>');
                        } else {
                            $('#enquiry-history').html(data.html);
                        }
                        $('#enquiryHistory').modal('show');
                    }
                });
            });

            $(document).on('click','#save_enquiry',function (e) {
                var id = $('#enq_request_id').val();
                var note = $('#enquiry_note').val();
                $('#enquiry_status').html('');
                if (note == '') {
                    $('#enquiry_status').html('<div class="text-danger">Please enter the note</div>');
                } else {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: base_url+'/save-enquiry',
                        data: {id:id,note:note},
                        success:function(data){
                            $('#preloader').hide();
                            if (data.error) {
                                $('#enquiry_status').html('<div class="text-danger">'+data.message+'</div>');
                            } else {
                                $('#enquiry_status').html('<div class="text-success">Updated successfully</div>');
                                $('#enquiryHistory').modal('hide');
                            }
                        }
                    });
                }
            });

            $(document).on('click','.print_welcome_letter',function (e) {
                var checked = [];
                var data_id = $(this).attr('data-id');
                if (typeof data_id !== typeof undefined && data_id !== false) {
                    checked.push($(this).attr('data-id'));
                }else{
                    $(".request_list_chkbx:checked").each(function() {
                        checked.push($(this).val());
                    });
                }

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: 'welcome-letter',
                    data: {selected:checked},
                    success:function(data){
                        $('#orderCustomLabel').text('Welcome Letter');
                        if (data.error) {
                            $('#orderCustombody').html('<div class="text-danger">'+data.message+'</div>');
                            $('#orderCustomModal').modal('show');
                        } else {
                            $('#orderCustombody').html(data.html);
                            $('#orderCustomModal').modal('show');
                            $.print(data.html);
                            $('#orderCustomModal').modal('hide');
                        }
                    }
                });
            });

            $(document).on('click','#bulk_pack',function (e) {
                var checked = [];
                $(".request_list_chkbx:checked").each(function() {
                    checked.push($(this).val());
                });
                $('#orderCustomLabel').text('Bulk Shipment');
                if (checked == '') {
                    $('#orderCustombody').html('<div class="text-danger">Please select any order</div>');
                } else {
                    $('#orderCustombody').html('<div class="form-group"><label class="form-label">Shipping Via</label> <select id="shiping_process" class="form-control custom-select" name="shiping_process"> <option value="Royal Mail First Class Delivery" selected>Royal Mail First Class</option> <option value="Royal Mail Special Delivery">Royal Mail Special Delivery</option> <option value="Royal Mail Next Day Delivery">Royal Mail Next Day Delivery</option> <option value="other">Other</option> </select></div> <div class="form-group other_delivery d-none"> <input type="text" name="shiping_agent" id="shiping_agent" class="form-control" required placeholder="Shipping Agent Name" value="Royal Mail First Class Delivery"></div> <div id="custom_status"></div> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="action_shipping" class="btn btn-success pull-right">Save & Send</button>');
                }
                $('#orderCustomModal').modal('show');
            });

            $(document).on('change','#shiping_process', function (e) {
                var shipping = $(this).val();
                if(shipping != 'other'){
                    $('.other_delivery').addClass('d-none');
                    $('#shiping_agent').val(shipping);
                }else{
                    $('.other_delivery').removeClass('d-none');
                    $('#shiping_agent').val('');
                }
            });

            $(document).on('click','#action_shipping',function (e) {
                var checked = [];
                var data_id = $(this).attr('data-id');
                if (typeof data_id !== typeof undefined && data_id !== false) {
                    checked.push($(this).attr('data-id'));
                }else{
                    $(".request_list_chkbx:checked").each(function() {
                        checked.push($(this).val());
                    });
                }
                if (checked == '') {
                    $('#orderCustomLabel').text('Bulk Shipment');
                    $('#orderCustombody').html('<div class="text-danger">Please select any order</div>');
                    $('#orderCustomModal').modal('show');
                } else {
                    var agent_name = $('#shiping_agent').val();
                    if (agent_name == '') {
                        $('#custom_status').html('<div class="text-danger">Please enter the agent details</div>');
                    } else {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: 'order-shipment',
                            data: {selected:checked,agent:agent_name},
                            beforeSend: function(){
                                $("#action_shipping").html('Processing..');
                            },
                            complete: function(){
                                $("#action_shipping").html('Save & Send');
                            },
                            success:function(data){
                                if (data.error) {
                                    $('#custom_status').html('<div class="text-danger">'+data.message+'</div>');
                                } else {
                                    $('#custom_status').html('<div class="text-success">'+data.message+'</div>');
                                    $('#orderCustomModal').modal('hide');
                                    $('#orderlist').DataTable().draw();
                                }
                            }
                        });
                    }
                }
            });

            $(document).on('click','.sim_details',function (e) {
                var id = $(this).attr('data-id');
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: 'sim-details',
                    data: {id:id},
                    success:function(data){
                        if (data.error) {
                            $('#orderCustombody').html('<div class="text-danger">'+data.message+'</div>');
                        } else {
                            $('#orderCustombody').html(data.html);
                        }
                        $('#orderCustomLabel').text('SIM Details');
                        $('#orderCustomModal').modal('show');
                    }
                });
            });

            $(document).on('click','.re_delivery',function (e) {
                var id = $(this).data('id');
                $('#orderCustomLabel').text('Process Re-Delivery');
                $('#orderCustombody').html('<div class="form-group"> <label class="form-label">Confirm re-delivery request</label><div id="custom_status"></div></div><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="re_delivery" data-id="'+ id +'" class="btn btn-warning pull-right">Confirm</button>');
                $('#orderCustomModal').modal('show');
            });

            $(document).on('click','#re_delivery',function (e) {
                var order_id = $(this).data('id');
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: base_url+'/re-order',
                    data: {order_id:order_id},
                    success:function(data){
                        if (data.error) {
                            $('#custom_status').html('<div class="text-danger">'+data.message+'</div>');
                        } else {
                            $('#orderCustomModal').modal('hide');
                            $('#orderlist').DataTable().draw();
                        }
                    }
                });
            });

            $(document).on('click','.order_duplicate',function (e) {
                var id = $(this).attr('data-id');
                $('#orderCustomLabel').text('Order Duplicate');
                $('#orderCustombody').html('<div class="form-group"> <label class="form-label">Phone Number / Sim Number</label> <input type="hidden"  id="simlist_id" value="'+ id +'"><input type="text" class="form-control" id="new_phone_number" placeholder="Phone Number / Sim Number"><div id="custom_status"></div></div><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="update_order" class="btn btn-success pull-right">Update</button>');
                $('#orderCustomModal').modal('show');
            });

            $(document).on('click','#update_order',function (e) {
                var sim_id = $('#simlist_id').val();
                var phone = $('#new_phone_number').val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: 'order-update',
                    data: {sim_id:sim_id,phone:phone},
                    success:function(data){
                        if (data.error) {
                            $('#custom_status').html('<div class="text-danger">'+data.message+'</div>');
                        } else {
                            $('#orderCustomModal').modal('hide');
                            $('#orderlist').DataTable().draw();
                        }
                    }
                });
            });

            $(document).on('click','#print_sim_detail',function () {
                $.print("#sim_detail_content");
            })

            $(document).on('click','.update_address',function (e) {
                var id = $(this).attr('data-id');
                $('#address_simlist_id').val(id);
                $('#orderCustomModal').modal('hide');
                $('#addressChangeModal').modal('show');
            });

            $(document).on('click','.find_address',function (e) {
                var postal_code = $('#postal_code').val();
                var house_no = $('#house_no').val();
                if (postal_code == '') {
                    $('#postalcode-error').text('Please enter postal code');
                    $('#postalcode-error').show();
                    setTimeout(function(){
                        $('#postalcode-error').hide();
                    }, 3000);
                } else {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: 'find-address',
                        data: {postal_code:postal_code,house_no:house_no},
                        success:function(data){
                            if (data.error) {
                                if (data.type == 1) {
                                    $('#delivery_address').html(data.list);
                                    $('#delivery_address').removeClass('d-none');
                                    $('#delivery_address_error').text(data.message);
                                } else if (data.type == 2) {
                                    $('#address_error').text(data.message);
                                    $('.shipping_address').removeClass('d-none');
                                }
                            } else {
                                $('#address_error').text('');
                                $('#delivery_address_error').text('');
                                $('#delivery_address').addClass('d-none');
                                var address = data.address.split(' , ');
                                $('#shipping_street').val(address[0]);
                                $('#shipping_city').val(address[1]);
                                $('#shipping_country').val(address[2]);
                                $('#postal_code').val(address[3]);
                                $('.shipping_address').removeClass('d-none');
                            }
                        }
                    });
                }
            });

            $(document).on('change','#delivery_address',function () {
                var address = $(this).val().split(' , ');
                $('#shipping_street').val(address[0]);
                $('#shipping_city').val(address[1]);
                $('#shipping_country').val(address[2]);
                $('#postal_code').val(address[3]);
                $('#delivery_address_error').text('');
                $('.shipping_address').removeClass('d-none');
            });

            $(document).on('click','#update_delivery_address',function () {
                var id = $('#address_simlist_id').val();
                if($("#address-form").valid()){
                    var address = $("#address-form").serialize();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: 'update-address',
                        data: {id:id,address:address},
                        success:function(data){
                            if (data.error) {
                                $('#update_shipping_status').text(data.message);
                                $('#update_shipping_status').show();
                                setTimeout(function(){
                                    $('#update_shipping_status').hide();
                                }, 5000);
                            } else {
                                $('#orderlist').DataTable().draw();
                                $('#addressChangeModal').modal('hide');
                            }
                        }
                    });
                }
            });

            $.validator.addMethod("lettersonly", function(value, element)
            {
                return this.optional(element) || /^[a-z ]+$/i.test(value);
            }, "This field allows only aphabets");

            $("#address-form").validate({
                rules: {
                    first_name: {
                      required: true,
                      lettersonly: true
                    },
                    last_name: {
                      required: true,
                      lettersonly: true
                    },
                    postal_code:'required',
                    shipping_street:'required',
                    shipping_city:'required',
                    shipping_country:'required'
                }
            });
            $(document).on('click','.order_refund',function (e) {
                var id = $(this).attr('data-id');
                var total = $(this).attr('data-total-amount');
                $('#orderCustomLabel').text('Cancel Order and Refund');
                $('#orderCustombody').html('<div class="form-group"><label class="form-label">Order Amount <b>'+total+'</b></label><br> <label class="form-label">Refund Amount</label> <input type="hidden"  id="total_amount" value="'+ total +'"><input type="hidden"  id="refund_simlist_id" value="'+ id +'"><input type="text" class="form-control" id="refund_amount" placeholder="Refund Amount" maxlength="7"><br> <label class="form-label">Refund Reason</label><input type="text" class="form-control" id="refund_reason" placeholder="Refund Reason" maxlength="50"><div id="custom_status"></div></div><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="refund_order_request" class="btn btn-success pull-right">Cancel & Refund</button>');
                $('#orderCustomModal').modal('show');
            });
            $(document).on('click','#refund_order_request',function () {
                var id = $('#refund_simlist_id').val();
                var amount = $('#refund_amount').val();
                var total  = $('#total_amount').val();
                var reason = $("#refund_reason").val();
                if(amount == ""){
                    alertify.error('Refund amount missing');
                    return;
                }else{ amount = parseFloat(amount); total = parseFloat(total);}
                if(amount > total ){
                    alertify.error('Refund amount cannot be greater than order amount');
                    return;
                }
                if(reason == ""){
                    alertify.error('Please provide a reason for cancelling the order');
                    return; 
                }
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: 'order-cancel-refund',
                    data: {id:id,refundamount:amount,reason:reason},
                    beforeSend: function(){
                        $("#refund_order_request").html('Processing..');
                    },
                    complete: function(){
                        $("#refund_order_request").html('Cancel & Refund');
                    },
                    success:function(data){
                        if (data.error) {
                            alertify.error(data.message);
                        } else {
                            alertify.success(data.message);
                            $('#orderlist').DataTable().draw();
                            $('#orderCustomModal').modal('hide');
                        }
                    }
                });
            });
        });
    </script>
@endsection
