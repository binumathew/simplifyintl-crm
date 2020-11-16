@extends('layouts.home')
@section('content')
<!-- page wrapper start -->
        <div class="wrapper">
            <div class="container-fluid">
                <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
                <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
                <link href="{{ asset('plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="btn-group pull-right">
                                <ol class="breadcrumb hide-phone p-0 m-0">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">Auto Plan</li>
                                </ol>
                            </div>
                            <h4 class="page-title">Auto Plan</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('list-cardexpiry') }}" id="cardexpiry-search-form" method="POST">
                                        @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Expiry</label>
                                                <select name="card_expiry" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                    <option value="1">This Week</option>
                                                    <option value="2">This Month</option>
                                                    <option value="3">Next Month</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Gateway</label>
                                                <select name="card_gateway" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                    @foreach($gateway as $gkey => $list)
                                                    <option value="{{ $list->gateway}}">{{ $list->gateway}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class=" col-md-12">
                                            <button type="button" id="searchBtn" class="btn btn-primary ">Search</button>
                                            <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                                            @if(Helper::has_permission('reports'))
                                            <button type="submit" class="btn btn-info pull-right" id="export" name="exportdata" value="1">Export</button>
                                            @endif

                                        </div>
                                    </div>
                                    </form>
                                </div>
                                <table id="cardexpiry-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Phone Number</th>
                                            <th>Email</th>
                                            <th>Card Type</th>
                                            <th>Card Expiry</th>
                                            <th>Gateway</th>
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
                <script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
            </div>
        </div>
        <!-- page wrapper end -->
<script>
$(document).ready(function(){
    $('#resetBtn').on('click', function(e) {
       $('#cardexpiry-search-form')[0].reset();
       $('#cardexpiry-table').DataTable().draw();
    });

    var cardexpiryTable = $('#cardexpiry-table').DataTable({
        dom: 'Bfrtip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'list-cardexpiry',
            data: function (d) {
                d.card_expiry = $('select[name=card_expiry]').val();
                d.card_gateway = $('select[name=card_gateway]').val();
            }
        },
        drawCallback:function(settings)
        {
            $('#preloader').hide();
        },
        "dataType": "jsonp",
        "columns": [
            {"data": "name", "name": "name"},
            {"data": "phone", "name": "phone"},
            {"data" : "email", "name":"email"},
            {"data" : "card_type","name":"card_type"},
            {"data" : "card_expiry","name":"card_expiry"},
            {"data" : "gateway","name":"gateway"},
        ],
        "order":[[0, 'asc']],
        "columnDefs": [
            {"defaultContent": "-","targets": "_all"},
            {"targets": [1,2,3],"orderable": false}
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
        cardexpiryTable.draw();
        e.preventDefault();
    });
});
</script>
@endsection
