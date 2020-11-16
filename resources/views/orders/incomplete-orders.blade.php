@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>

            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">In-Complete Orders</li>
                            </ol>
                        </div>
                        <h4 class="page-title">In-Complete Orders</h4>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <!-- <p class="text-muted font-14 m-b-30 notice-board-top"><i class="mdi mdi-download"></i></i>Results</p> -->

                            <table id="orderlist" class="table table-striped dt-responsive nowrap table-vertical new-order-table" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <!-- <th>ID</th> -->
                                        <th>Customer</th>
                                        <th>Agent</th>
                                        <th>Details</th>
                                        <th>Created at</th>
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

            <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

            <script type="text/javascript">
                $(document).ready(function () {
                    $('#orderlist').DataTable({
                        responsive: true,
                        "bSort" : true,
                        pageLength: 25,
                        language: { search: "" },
                        processing: true,
                        serverSide: true,
                        "ajax": {
                            "url": "abandoned-list",

                        },

                        "dataType": "jsonp",
                        "columns": [
                            {"data" : "name","name" : "u.name"},
                            {"data": "promocode", "name": "promocode"},
                            {"data" : "details", "name": "details" },
                            {"data" : "created_at","name" : "created_at"},
                            {"data": function(data){
                                var route = "{{URL::to('/order-details')}}";
                                var html = '<form method="post" id="view_order_'+data.id+'" action="'+route+'">@csrf<input type="hidden" name="order_id" value="'+data.order_id+'"></form><a data-toggle="tooltip" title="View Details" href="javascript:void(0);" class="view_order_details text-muted m-r-10" data-id="'+data.id+'"><i class="mdi mdi-eye mdi-24px"></i></a><a data-toggle="tooltip" title="Enquiry History" href="javascript:void(0);" class="enquiry_history text-muted m-r-10" data-id="'+data.id+'"><i class="mdi mdi-comment-text mdi-24px"></i></a><a href="javascript:void(0);" class="manage_promocode text-muted m-r-10" data-toggle="tooltip" data-placement="top" title="Edit Promocode" data-id="'+data.id+'"><i class="mdi mdi-account mdi-24px"></i></a>';
                                return  html;
                            }, "name": "action","orderable": false, "searchable": false},
                        ],
                        'order':[[3, 'desc']],
                        "columnDefs": [
                        { "targets": 1,"width": '80px'},
                        {"defaultContent": "-","targets": "_all"}
                        ],
                    });

                    $('.dataTables_filter input').attr("placeholder", "Search");

                    $('#searchBtn').on('click', function(e) {
                        e.preventDefault();
                        $('#orderlist').DataTable().draw();
                    });

                    $(document).on('click', '.show_user_data', function(e) {
                        e.preventDefault();
                        var cart_id = $(this).data('cart');
                        $('#show_user_'+cart_id).submit();
                    });
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection
