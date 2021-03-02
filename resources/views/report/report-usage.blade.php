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
                                    <li class="breadcrumb-item active">Usage</li>
                                </ol>
                            </div>
                            <div class="row">
                            <div class="col-md-1">
                                <h4 class="page-title">Usage</h4>
                            </div>
                            <div class="col-md-4">
                                <p style="font-size:14px;"><b>An overview of Bundles and other chargeable items.</b></p>
                            </div>
                            <div class="col-md-7">
                                <p style="font-size:12px;color:#ff0000;">Usage- Estimated usage only, final bill update by end of the monthCDR- CDR’s updated until {{ Carbon::now()->subDays(1)->format('d-m-Y')}}</p>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card m-b-20">
                            <div class="card-body">
                                <div class="order-search">
                                    <form action="{{ url('list-usage') }}" id="usage-search-form" method="POST">
                                        @csrf
                                    <div class="row">
                                    	<div class="col-md-4">
                                            <div class="form-group">
                                                <label>Number</label>
                                                <input class="form-control number" name="number" type="text" minlength="8" maxlength="15" placeholder="eg. 447877776776">
                                            </div>
                                        </div>
  										<div class="col-md-4">
                                            <div class="form-group">
                                                <label>Month</label>
                                               <select name="usage_month" id="usage_month" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                   	@php
						                            $now   = Carbon::now()->format('Y-m-d');
						                            $month = strtotime($now);
						                            $currmonth = date('m', $month);
						                            @endphp
						                            @for($i=1; $i<=12; $i++)
						                            @php
						                            $month_name = date('M', $month);
						                            $monthid = date('m', $month);
						                            $month  = strtotime('+1 month', $month);
						                            @endphp
                                                    <option {{ ($currmonth == $monthid) ? 'selected':'' }} value="{{$monthid}}">{{$month_name}}</option>
                            						@endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Year</label>
                                                @php $firstYear = (int)Carbon::now()->format('Y');
					                            $lastYear = $firstYear - 5;
					                            @endphp
					                            <select name="usage_year" id="usage_year" class="form-control custom-select">
					                            <option value="">Choose</option>
					                                @for($i=$firstYear;$i >= $lastYear;$i--)

					                                <option {{ ($i == $firstYear) ? 'selected':'' }} value="{{$i}}" >{{$i}}</option>
					                                @endfor
					                            </select>
                                            </div>
                                        </div>
                                        <!-- <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="usage_status" id="usage_status" class="form-control custom-select">
                                                    <option value="">Choose</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">In Active</option>
                                                </select>
                                            </div>
                                        </div> -->
                                        <div class=" col-md-12">
                                            @if(Helper::has_permission('reports'))
                                            <button type="submit" class="btn btn-info" id="export" name="exportdata" value="1">Export</button>
                                            @endif
                                            <button type="button" id="resetBtn" class="btn btn-secondary pull-right">Reset</button>
                                            <button type="button" id="searchBtn" class="btn btn-primary  pull-right" style="margin-right: 4px;">Search</button>
                                            

                                        </div>
                                    </div>
                                    </form>
                                </div>
                                <table id="usage-table" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Phone Number</th>
                                            <th>Email</th>
                                            <th>Plan</th>
                                            <th>Voice</th>
                                            <th>SMS</th>
                                            <th>Data (GB)</th>
                                            <th>Call Duration</th>
                                            <!-- <th>Data Usage</th>
                                            <th>Sms Count</th> -->
                                            <th>Amount</th>
                                            <th>Total(inc vat/tax)</th>
                                            <th>Status</th>
                                            <th>Created At</th>
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
        <!-- custom modal popup start -->
        <div id="cdrviewModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content" style="max-height: 580px;">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0">CDR Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" id="cdrviewbody">
                    <table class="table table-hover cdrlisting"></table>
                    </div>
                </div>
            </div>
        </div>
        <!-- custom modal popup end -->
<script>
$(document).ready(function(){

    $('#resetBtn').on('click', function(e) {
       $('#usage-search-form')[0].reset();
       $('#usage-table').DataTable().draw();
    });

    var usageTable = $('#usage-table').DataTable({
        dom: 'Bfrtip', //Bfrtip
        responsive: true,
        bSort : true,
        pageLength:25,
        language: { search: "" },
        processing: true,
        serverSide: true,
        searching:false,
        ajax: {
            url: 'list-usage',
            data: function (d) {
            	d.number = $('input[name=number]').val();
            	d.usage_month  = $("#usage_month").val();
            	d.usage_year   = $("#usage_year").val();
            	// d.usage_status = $("#usage_status").val();
            },
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
            {"data" : "plan","name":"plan"},
            {'data': function(data){
                return data.currency_symbol+data.voice+'<br><a data-toggle="tooltip" title="Total Calls" href="javascript:void(0);" class="getcdrRecords" data-param="voice" data-usq="'+data.user_id+'" data-from="'+data.from+'" data-to="'+data.to+'">'+data.total_calls+'</a>';
            },'name':'voice'},
            {'data': function(data){
                return data.currency_symbol+data.sms+'<br><a data-toggle="tooltip" title="SMS count" href="javascript:void(0);" class="getcdrRecords" data-param="sms" data-usq="'+data.user_id+'" data-from="'+data.from+'" data-to="'+data.to+'">'+data.sms_count+'</a>';
            },'name':'sms'},
            {'data': function(data){
                return data.currency_symbol+data.data+'<br><a data-toggle="tooltip" title="Data" href="javascript:void(0);" class="getcdrRecords" data-param="data" data-usq="'+data.user_id+'" data-from="'+data.from+'" data-to="'+data.to+'">'+data.data_usage+'</a>';
            },'name':'data'},
            {"data" : "call_usage","name":"call_usage"},
            // {"data" : "data_usage","name":"data_usage"},
            // {"data" : "sms_count","name":"sms_count"},
            {"data" : "service_total","name":"service_total"},
            {"data" : "total","name":"service_total"},
            {"data" : "status","name":"status"},
            {"data" : "created_at","name":"created_at"},
        ],
        "order":[[0, 'asc']],
        "columnDefs": [
            {"defaultContent": "-","targets": "_all"},
            {"targets": [1,2,3,4,5,6,7,10,11],"orderable": false}
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
        usageTable.draw();
        e.preventDefault();
    });
    $(document).on('click','.getcdrRecords',function (e) {
        var user = $(this).attr('data-usq');
        var from = $(this).attr('data-from');
        var to   = $(this).attr('data-to');
        var param = $(this).attr('data-param');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: base_url+'/get-cdr-records',
            data: {user:user,from:from,to:to,param:param},
            beforeSend: function(){
                $("#preloader,#status").show();
            },
            complete: function(){
                $("#preloader,#status").hide();
            },
            success:function(data){
               if(data.status == 200){
                   var result = JSON.parse(data.response);
                   var table  = '';
                   if(result.length >= 1){
                        table += '<thead>'
                              +'<tr>'
                              +'<th>Date</th>'
                              +'<th>Time</th>'
                              +'<th>Dialled</th>'
                              +'<th>Duration</th>'
                              +'<th>Cost</th>'
                            +'</tr>'
                            +'</thead><tbody>';
                     if(param == 'voice'){
                        $.each(result,function(k,val){
                            var datei =  new Date( val.connect_date );
                            var cld   = (val.cld.length >=11 ) ? '+'+val.cld : val.cld;
                            table +='<tr>'
                                    +'<td>'+datei.toLocaleDateString('en-GB', {month: '2-digit',day: '2-digit',year: 'numeric'})+'</td>'
                                    +'<td>'+datei.toLocaleTimeString()+'</td>'
                                    +'<td>'+cld +'</td>'
                                    +'<td>'+new Date(val.duration * 1000).toISOString().substr(11, 8)+'</td>'
                                    +'<td>'+data.currency_symbol+val.cost+'</td>'
                                +'</tr>';
                        });
                        
                     }else if(param == 'data'){
                        $.each(result,function(k,val){
                            var datei =  new Date( val.date );
                            table +='<tr>'
                                    +'<td>'+datei.toLocaleDateString('en-GB', {month: '2-digit',day: '2-digit',year: 'numeric'})+'</td>'
                                    +'<td>'+datei.toLocaleTimeString()+'</td>'
                                    +'<td>WAP</td>'
                                    +'<td>'+(val.duration / (1024*1024*1024)).toFixed(2)+' GB</td>'
                                    +'<td>'+data.currency_symbol+val.amount+'</td>'
                                +'</tr>';
                        });
                     }else if(param == 'sms'){
                        $.each(result,function(k,val){
                            var datei =  new Date( val.date );
                            var cld   = (val.to_number.length >5 ) ? '0'+val.to_number : val.to_number;
                            table +='<tr>'
                                    +'<td>'+datei.toLocaleDateString('en-GB', {month: '2-digit',day: '2-digit',year: 'numeric'})+'</td>'
                                    +'<td>'+datei.toLocaleTimeString()+'</td>'
                                    +'<td>'+cld+'</td>'
                                    +'<td>'+val.duration+'</td>'
                                    +'<td>'+data.currency_symbol+val.amount+'</td>'
                                +'</tr>';
                        });
                     }
                     table +='</tbody>';
                   }
                   $('.cdrlisting').html('').html(table);
                   $('.cdrlisting').dataTable({
                    destroy: true,
                    "columnDefs": [ {
                    "targets": [0,1,2,3],
                    "orderable": false
                    } ]
                   });
                   $("#cdrviewModal").modal('show');
               }
            }
        });
    });
});
</script>
@endsection
