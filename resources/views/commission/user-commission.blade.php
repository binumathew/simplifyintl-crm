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
                                    <li class="breadcrumb-item active">Commission Users List</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Commission Users List</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('comm-user-list') }}" id="comm-user-list-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>From</label>
                                                <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="Date From" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>To</label>
                                                <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="Date To" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class=" col-md-12">
                                            <button type="button" id="searchBtn" class="btn btn-primary ">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                        </div>
                                    </div>
                                    </form>
                                </div>                             
                                <table id="commUser-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>                                       
                                            <th>Plan Name</th>
                                            <th>Commission</th>
                                            <th>Commission Gained</th>
                                            <th>Commission Paid</th>  
                                            <th>Clawback Amount</th>  
                                            <th>Created at</th>                             
                                            <th>Action</th>
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
        <div id="viewcommuserModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0">Commission Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" id="commuserviewbody"> 
                         
                    </div>
                </div>
            </div>
        </div>
        <!-- custom modal popup end -->
<script>
$(document).ready(function(){

    $('#resetBtn').on('click', function(e) {
       $('#comm-user-list-form')[0].reset();
       $('#commUser-table').DataTable().draw();       
    });

    var commuserTable = $('#commUser-table').DataTable({
        dom: 'Bfrltip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'comm-user-list',
            data: function (d) {
                d.userid  = "<?php echo $userid;?>";
                d.from    = $('input[name=from_date]').val();
                d.to      = $('input[name=to_date]').val();
            },
        },
        drawCallback:function(settings)
        {
            $('#preloader').hide();
        },
        "dataType": "jsonp",
        "columns": [
            {data: 'dealer_name', name: 'dealer_name'},
            {data: 'planname', name: 'planname'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'gained_comm', name: 'gained_comm'},
            {data: 'paid_comm', name: 'paid_comm'},
            {data: 'clawback_amount', name: 'clawback_amount'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action'},
        ],
        "columnDefs": [
            //{"defaultContent": "-","targets": "_all"},
            {"targets": [0,1,2,3,4,5,6],"orderable": false}
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
        commuserTable.draw();
        e.preventDefault();
    });
    $(document).on("click", ".comm-breakdown", function () {
        var datas = $(this).data('id');
         $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            data:{datas:datas},
            url: '<?php echo url('/'); ?>/comm-breakdown',
            success: function(response){ 
                if(response.status == 200){
                    $("#commuserviewbody").html('').html(response.page);
                    $("#viewcommuserModal").modal('show');
                }
            }
        });
    });
    // $(document).on("click","#addRevenue", function () {
    //     $("#viewrevenueModal").modal('show');
    // });
    // $(document).on("change", '#addrev_dealer', function (){
    //     var dealerid = $(this).val();
    //     if(dealerid != -1)
    //         $.ajax({
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //             },
    //             type: 'POST',
    //             data: {dealer:dealerid},
    //             url: '<?php echo url('/'); ?>/get-revenue',
    //             success: function(response){ 
    //                 $('#revenue_amount').val("");
    //                 $('#expiry_at').val("");
    //                 if(response.status == 200){
    //                     $('#revenue_amount').val(response.message['amount']);
    //                     $('#expiry_at').val(moment(response.message['expiry_at']).format('DD-MM-YYYY'));
    //                 }
    //             }
    //         });
        
    // });
    $('.datepicker').datepicker({
        autoclose: true,
        orientation:'bottom left',
        format: 'dd-mm-yyyy',
        todayHighlight: true
    });
});
</script>
@endsection