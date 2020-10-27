@extends('layouts.home')
@section('content')
<!-- page wrapper start -->
        <div class="wrapper">
            <div class="container-fluid">
                <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
                <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
                <link href="{{ asset('public/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="btn-group pull-right">
                                <ol class="breadcrumb hide-phone p-0 m-0">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">Dealer Commission List</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Dealer Commission List</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('comm-dealer-list') }}" id="comm-dealer-list-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Name</label>
                                                 <select class="form-control" name="dealer_id" id="dealerId" required>
                                                    <option value="">Choose</option>
                                                    @foreach ($dealer as $usr)
                                                        <option value="{{Crypt::encrypt($usr->id)}}">{{$usr->first_name.'-'.$usr->promocode}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div> 
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Plan Type</label>
                                                <select name="plan_type" id="planType" class="form-control custom-select">
                                                    <option value="" selected disabled>Choose</option>
                                                    <option value="1">Plan</option>
                                                    <option value="2">Bundle</option>  
                                                </select>
                                            </div>
                                        </div> 
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Plan Name</label>
                                                <div class="input-group">
                                                    <select class="form-control" name="plan_id" id="planId" required>
                                                    <option value="">Choose</option>
                                                    </select>
                                                </div>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class=" col-md-12">
                                            <button type="button" id="searchBtn" class="btn btn-primary ">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                            @if(Helper::has_permission('commission','create'))
                                            @if((in_array(Auth::user()->roles->short_code,['ADMIN'])) || $haschild)
                                            <button type="button" class="btn btn-warning waves-effect waves-light pull-right" id="addRevenue">Add Revenue</button>
                                            @endif
                                            @endif
                                        </div>
                                    </div>
                                    </form>
                                </div>  
                                <div class="col-md-12">
                                    @if(Helper::has_permission('commission','create'))
                                    @if(Auth::user()->roles->short_code == 'DEALER' && $haschild)
                                    <a href="{{ url('/commdealer-manage')}}">
                                        <button type="button" class="btn btn-info waves-effect waves-light pull-right">Add Sub-Dealer Commission</button>
                                    </a>
                                    @elseif(in_array(Auth::user()->roles->short_code,['ADMIN']))
                                    <a href="{{ url('/commdealer-manage')}}">
                                        <button type="button" class="btn btn-info waves-effect waves-light pull-right">Add Dealer Commission</button>
                                    </a>
                                    @endif
                                    @endif
                                </div>                              
                                <table id="commDealer-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Plan Type</th>
                                            <th>Plan Name</th>
                                           <!--  <th>Duration</th>
                                            <th>Commission Type</th>
                                            <th>Commission Rate</th>
                                            <th>Status</th> -->
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
                <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
                <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
                <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
                <script src="{{ asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
            </div>
        </div>
        <!-- page wrapper end -->
        <!-- custom modal popup start -->
        <div id="viewcommModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0">Plan Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" id="commviewbody"> 
                         
                    </div>
                </div>
            </div>
        </div>
        <!-- custom modal popup end -->
        <!-- custom modal popup start -->
        <div id="viewrevenueModal" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0">Plan Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" id="revenueviewbody"> 
                     <form id="new-revenue-form" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Choose Dealer</label>
                            <select id="addrev_dealer" class="form-control" name="addrev_dealer">
                                <option value="" selected>Choose</option>
                                @foreach($revenue as $dealer)
                                    <option value="{{Crypt::encrypt($dealer->id)}}">{{$dealer->first_name}} {{$dealer->last_name}} ({{$dealer->promocode}})</option>
                                @endforeach
                            </select>
                            <span></span>
                        </div>  
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="text" id="revenue_amount" class="form-control" name="revenue_amount" required maxlength="10">
                            <span></span>
                        </div>
                        <div class="form-group">
                            <label for="number">Expiry</label>
                               <input type="text" class="form-control datepicker" name="expiry_at" id="expiry_at" placeholder="Valid Till" required autocomplete="off">
                            <span></span>
                        </div>
                        <button type="submit" class="btn btn-primary" id="payRevenue">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </form>    
                    </div>
                </div>
            </div>
        </div>
        <!-- custom modal popup end -->
<script>
$(document).ready(function(){

    $('#resetBtn').on('click', function(e) {
       $('#comm-dealer-list-form')[0].reset();
       $('#commDealer-table').DataTable().draw();       
    });

    var commdealerTable = $('#commDealer-table').DataTable({
        dom: 'Bfrltip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'comm-dealer-list',
            data: function (d) {
                d.dealer_id = $("#dealerId").val();
            	d.plan_type = $("#planType").val();
                d.plan_id   = $("#planId").val();
            },
        },
        drawCallback:function(settings)
        {
            $('#preloader').hide();
        },
        "dataType": "jsonp",
        "columns": [
            {"data": "first_name", "name": "first_name"},   
            {"data": "plan_type", "name": "plan_type"},                     
            {"data" : "plan_name","name":"plan_name"},
            // {"data" : "name","name":"name"},
            // {"data" : "comm_type","name":"comm_type"},
            // {"data" : "comm_rate","name":"comm_rate"},
            // {"data" : "status","name":"status"},
            {"data" : "created_at","name":"created_at"},
            {"data" : "actions","name":"actions"},
        ],
        "columnDefs": [
            //{"defaultContent": "-","targets": "_all"},
            {"targets": [0,1,2,3,4],"orderable": false}
        ],
        buttons: [
        // {
        //  extend: 'excel',
        //  text: 'Export',
        //  className: 'btn-primary',
        //  exportOptions: {
        //      orthogonal: null
        //  }
        // },
        ],

    });

    $('#searchBtn').on('click', function(e) {
        commdealerTable.draw();
        e.preventDefault();
    });
    $(document).on("click","#addRevenue", function () {
        $("#viewrevenueModal").modal('show');
    });
    $(document).on("change", '#addrev_dealer', function (){
        var dealerid = $(this).val();
        if(dealerid != -1)
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                data: {dealer:dealerid},
                url: '<?php echo url('/'); ?>/get-revenue',
                success: function(response){ 
                    $('#revenue_amount').val("");
                    $('#expiry_at').val("");
                    if(response.status == 200){
                        $('#revenue_amount').val(response.message['amount']);
                        $('#expiry_at').val(moment(response.message['expiry_at']).format('DD-MM-YYYY'));
                    }
                }
            });
        
    });
    $('.datepicker').datepicker({
        autoclose: true,
        orientation:'bottom left',
        format: 'dd-mm-yyyy',
        todayHighlight: true
    });
});
</script>
@endsection