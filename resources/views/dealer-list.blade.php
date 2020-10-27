@extends('layouts.home')
@section('content')
<style>
.selection{
    background-color: #eeeeee;
    border-width: 1px;
    border-style: dotted;
    border-color: black;
}
</style>
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Dealers</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Dealers</h4>
                    </div>
                </div>
            </div>
            @if(Helper::has_permission('reports'))
                <div class="row">
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-primary mr-0 float-right"><i class="mdi mdi-account-multiple"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="total_user">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Total Staff<span class="pull-right"></span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-success mr-0 float-right"><i class="mdi mdi-account-check"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="active_user">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Active Staff<span class="pull-right"></span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-danger mr-0 float-right"><i class="mdi mdi-account-alert"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="inactive_user">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">Not Active Staff <span class="pull-right">
                                </span></p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="mini-stat clearfix bg-white">
                            <span class="font-40 text-warning mr-0 float-right"><i class="mdi mdi-account"></i></span>
                            <div class="mini-stat-info">
                                <h3 class="counter font-light mt-0" id="weekly_user">0</h3>
                            </div>
                            <div class="clearfix"></div>
                            <p class=" mb-0 m-t-10 text-muted">-<span class="pull-right"></span></p>
                        </div>
                    </div>                    
                </div>
                @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div class="row">
                                @if(Helper::has_permission('dealer','create'))
                                <div class="col-md-12 m-b-20">
                                    <div class=" text-right">
                                        <a href="{{ url('/create-dealer') }}" class="btn btn-primary ">Add Dealer</a>
                                    </div>                                   
                                </div>
                                @endif
                            </div>
                            <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <table id="staffList" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th class="d-none">#</th>
                                            <th>Name</th>                                           
                                            <th>Email</th>
                                            <th>Phone No</th> 
                                            <th>Promo Code</th>                                     
                                            <th>Role</th>
                                            <th>Created Date</th>
                                            <th>Status</th>                          
                                            <th>Action</th>
                                        </tr>                                    
                                    </thead>
                                    <tbody> 
                                        @php $i=0; @endphp
                                            @foreach ($dealers as $user)
                                            <tr id="{{ ++$i }}">
                                                <td class="d-none">{{ $i }}</td>
                                                <td>{{ $user->first_name .' '. $user->last_name }}</td>                     
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->phone }}</td>
                                                <td>{{ $user->promocode }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ Helper::date_format($user->created_at) }}</td>
                                                <td>
                                                    @if($user->status)
                                                    <span class="badge badge-success">Active</span>
                                                    @else
                                                    <span class="badge badge-danger">In-Active</span>
                                                    @endif                                      
                                                <td>
                                                    <a data-toggle="tooltip" href="javascript:void(0);" data-original-title="Assign Stock" class="text-muted list_stock" data-id="{{ Crypt::encrypt($user->id) }}" data-name="{{ $user->first_name .' '. $user->last_name }}"><i class="mdi mdi-stackoverflow mdi-24px"></i></a>&nbsp;&nbsp;&nbsp;
                                                    @if(Helper::has_permission('staff','edit'))
                                                    <a title="" href="{{ url('/edit-dealer', Crypt::encrypt($user->id)) }}" data-original-title="Edit" data-toggle="tooltip" class="text-muted"><i class="mdi mdi-pencil mdi-24px"></i></a>
                                                    @endif
                                                    @if(Helper::has_permission('staff','delete'))
                                                    <!-- <a data-toggle="tooltip" title="Delete" href="{{ url('/delete-staff', Crypt::encrypt($user->id)) }}" class="fa fa-trash-o"></a> -->
                                                    @endif
                                                    @if(auth::user()->role == 1)
                                                    &nbsp;&nbsp;&nbsp;<a title="" href="{{ url('/authenticate', Crypt::encrypt($user->id)) }}" data-original-title="Login" data-toggle="tooltip"><i class="mdi mdi-login mdi-24px"></i></a>
                                                    @endif
                                                    &nbsp;&nbsp;&nbsp;<a data-toggle="tooltip" href="javascript:void(0);" data-original-title="Send Credentials" class="resend_credentials text-muted" data-id="{{ Crypt::encrypt($user->id) }}"><i class="mdi mdi-email mdi-24px"></i></a>
                                                </td>
                                            </tr>
                                            @endforeach                                      
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- The Modal -->
            <div class="modal fade" id="stockModal">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Assign Stock</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body" id="stockBody">
                <form id="stock_list_form">
                    <input type="hidden" id="dealer_id" value="" name="dealer_id"/>
                    <input type="hidden" id="dealer_name" value="" name="dealer_name"/>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <select name="change_providers" id="change_providers" class="form-control custom-select required">
                                    <option value="">Choose Provider</option>
                                    @if($providers->isNotEmpty())
                                    @foreach($providers as $key => $list)
                                    <option value="{{ Crypt::encrypt($list->id) }}">{{ $list->provider }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select name="change_boxtype" id="change_boxtype" class="form-control custom-select required">
                                    <option value="">Choose Box</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select name="stock_limit" id="stock_limit" class="form-control custom-select ">
                                    <option value="">Choose Limit</option>
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                    <option value="500">500</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input search_type" name="search_type" value="0" checked>None
                                </label>
                            </div>
                            <div class="form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input search_type" name="search_type" value="1">SIM Range
                                </label>
                                </div>
                                <div class="form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input search_type" name="search_type" value="2">Phone Number
                                </label>
                            </div>
                        </div>
                        <div class="row col-md-6 chng_search_category_phone d-none">
                            <div class="col-md-6">
                                <div class="form-group">                                                        
                                <input id="phone_number_from" name="phone_number_from" type="text" class="form-control" autocomplete="off" value="" placeholder="Phone Number From">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                      
                                <input id="phone_number_to" name="phone_number_to" type="text" class="form-control" autocomplete="off" value="" placeholder="Phone Number To">
                                </div>
                            </div>
                        </div>
                        <div class="row col-md-6 chng_search_category_sim d-none">
                            <div class="col-md-6">
                                <div class="form-group">                                                       
                                <input id="imsi_range_from" name="imsi_range_from" type="text" class="form-control" autocomplete="off" value="" placeholder="Sim Number From">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">                                                        
                                <input id="imsi_range_to" name="imsi_range_to" type="text" class="form-control" autocomplete="off" value="" placeholder="Sim Number To">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                                <button type="button" class="btn btn-success waves-effect waves-light get_stock">Get Stock</button>
                        </div> 
                    </div>
                    <br>
                    </form>
                    <div class="row">
                        <div class="col-md-12 stock_list d-none">
                        <form id="stock_assign_form">
                            <h7>Available Stock</h7>
                            <div class="selection ">
                                <ul id="nonselected">
                                </ul>
                            </div>
                        </form>
                        </div>
                        <!-- <div class="col-md-6">
                            <h7>Reserved <span class="rs_stock">0</span></h7>
                            <div class="selection">
                                <ul id="selected">
                                </ul>
                            </div>
                        </div> -->
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-success waves-effect waves-light pull-right d-none assign_stock">Assign</button>
                        </div> 
                    </div>
                </div>
                </div>
            </div>
            </div>
            <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

            
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#staffList').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search: '' },});

                    $('.dataTables_filter input').attr('placeholder', 'Search');

                    $(document).on('click','.resend_credentials',function (e) {  
                        var $this = $(this);    
                        var id =  $this.attr('data-id');
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: base_url+'/send-password',
                            data: {id:id},
                            success:function(data){                                
                                if (data.error) {
                                    alert(data.message);
                                } else {
                                    alert(data.message);
                                    $this.hide();
                                }
                            }
                        });
                    });
                    $(document).on('click','.list_stock',function (e) {
                        $("#dealer_id").val($(this).attr('data-id'));
                        $("#dealer_name").val($(this).attr('data-name'));
                        $('#stock_list_form')[0].reset();
                        $("#nonselected").html('');
                        $("#stockModal").modal('show');
                    });
                    $(document).on('click','.search_type',function (e) {
                        var search_type = $(this).val();
                        $('.chng_search_category_phone,.chng_search_category_sim').addClass('d-none');  
                        if(search_type == 1){
                            $('.chng_search_category_sim').removeClass('d-none');
                        }else if(search_type == 2){
                            $('.chng_search_category_phone').removeClass('d-none');  
                        }
                        
                    });
                    $(document).on('change','#change_providers',function (e) {
                        var provider = $(this).val();
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: base_url+'/get-box',
                            data: {provider:provider},
                            beforeSend: function(){
                                $("#preloader,#status").show();
                            },
                            complete: function(){
                                $("#preloader,#status").hide();
                            },
                            success:function(data){   
                                var html = '';                             
                                if (data.success) {
                                    var resp   = JSON.parse(data.box);
                                    var html   = '';
                                    $.each(resp,function(k,val){
                                        html += '<option value="'+val.box_no+'">'+val.box_no+'</option>';
                                    });
                                    $('#change_boxtype').find('option:not(:first)').remove();
                                    $("#change_boxtype").append(html);
                                }else {
                                    
                                }
                            }
                        });
                    });
                    $(document).on('click','.get_stock',function (e) {
                        if($("#stock_list_form").valid()){
                        var search_type = $(".search_type").val();
                        var formData    = new FormData($('#stock_list_form')[0]);
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            url: base_url+'/get-stock',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            beforeSend: function(){
                                $("#preloader,#status").show();
                                $("#nonselected").html('');
                            },
                            complete: function(){
                                $("#preloader,#status").hide();
                            },
                            success:function(data){   
                                var html = '';                             
                                if (data.success) {
                                    var resp = JSON.parse(data.stock_list);
                                    if(resp.length == 0){
                                        html += '<p class="form-error">Sim not available!</p>';
                                    } 
                                    $.each(resp,function(index,value){
                                    if(search_type == 2){
                                      html += '<label><li style="list-style-type:none;"><span><input type="checkbox" class="dealers_num" id="dealers_no'+ value.id+'" name="stock_id[]" value="'+ value.id+'" checked></span> '+ value.phone_number +'</li></label> &nbsp;&nbsp;';
                                    }else{
                                        html += '<label><li style="list-style-type:none;"><span><input type="checkbox" class="dealers_num" id="dealers_no'+ value.id+'" name="stock_id[]" value="'+ value.id+'" checked></span> '+ value.sim_number+'</li></label> &nbsp;&nbsp;';
                                    }
                                    //    html += '<li style="list-style-type:none;">'+val.sim_number
                                    //         +'<a class="btn btn-gray btn-sm getSelect" href="javascript:void(0);"><i class="mdi mdi-arrow-right-bold mdi-24px"></i>'
                                    //         +'</a>';
                                    });
                                    $(".stock_list,.assign_stock").removeClass('d-none');
                                    $("#nonselected").append(html);
                                } else {
                                    
                                }
                            }
                        });
                        }
                    });
                    $(document).on('click','.assign_stock',function (e) {
                        if($('.dealers_num:checkbox:checked').length == 0){
                            alertify.error('Please select atleast one stock');
                        }else{
                            var dealername = $("#dealer_name").val();
                            alertify.confirm('Stock Assign Confirmation', 'Are you sure you want to assign stock to '+dealername+'?',
                        function(){  
                            var formData    = new FormData($('#stock_assign_form')[0]);
                            var dealer_id   = $("#dealer_id").val();
                            formData.append('dealer_id', dealer_id);
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: 'POST',
                                url: base_url+'/assign-stock',
                                data: formData,
                                processData: false,
                                contentType: false,
                                dataType: 'json',
                                beforeSend: function(){
                                    $("#preloader,#status").show();
                                    $("#nonselected").html('');
                                },
                                complete: function(){
                                    $("#preloader,#status").hide();
                                },
                                success:function(data){                              
                                    if (data.success) {
                                        alertify.success(data.message);
                                        $('#stock_list_form')[0].reset();
                                    } else {
                                        alertify.error(data.message);
                                    }
                                }
                            });
                        },function(){ alertify.error('Option cancelled')});
                        }
                    });
                    // $(document).on('click','.getSelect',function (e) {
                    //     var av_stock = parseInt($('.av_stock').html());
                    //     var rs_stock = parseInt($('.rs_stock').html());

                    //     if($(this).parent().parent().attr('id') == "nonselected"){
                    //         $(this).children().removeClass('mdi-arrow-right-bold').addClass('mdi-close-box');
                    //         $(".rs_stock").html(rs_stock + 1);
                    //         $(".av_stock").html(av_stock - 1);
                    //         $(this).parent().detach().appendTo('#selected');
                    //     }else{
                    //         $(".rs_stock").html(rs_stock - 1);
                    //         $(".av_stock").html(av_stock + 1);
                    //         $(this).children().removeClass('mdi-close-box').addClass('mdi-arrow-right-bold');
                    //         $(this).parent().detach().appendTo('#nonselected'); 
                    //     }
                    // }); 
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection