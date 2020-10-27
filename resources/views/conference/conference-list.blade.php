@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('public/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Conference</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Conference</h4>
                    </div>
                </div>
            </div>
            @if(Helper::has_permission('reports'))
            <div class="row">
                <div class="col-md-6 col-lg-6 col-xl-2">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-info mr-0 float-right"><i class="mdi mdi-account-multiple"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0" id="total_list">0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Total Conference<span class="pull-right"></span></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-2">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-primary mr-0 float-right"><i class="mdi mdi-phone-in-talk"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0" id="active_list">0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Active Conference<span class="pull-right"></span></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-2">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-primary mr-0 float-right"><i class="mdi mdi-account-network"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0" id="active_port">0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Port In Use<span class="pull-right"></span></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-2">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-warning mr-0 float-right"><i class="mdi mdi-account"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0" id="upcoming_list">0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Upcoming<span class="pull-right"></span></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-2">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-warning mr-0 float-right"><i class="mdi mdi-account-network"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0" id="adv_port">0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Port<span class="pull-right"></span></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-2">
                    <div class="mini-stat clearfix bg-white">
                        <span class="font-40 text-secondary mr-0 float-right"><i class="mdi mdi-history"></i></span>
                        <div class="mini-stat-info">
                            <h3 class="counter font-light mt-0" id="completed_list">0</h3>
                        </div>
                        <div class="clearfix"></div>
                        <p class=" mb-0 m-t-10 text-muted">Completed<span class="pull-right">
                            </span></p>
                    </div>
                </div>               
            </div>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <div class="order-search">
                                <form action="{{ url('/conference-list') }}" id="conf-form" method="POST">
                                    @csrf
                                    <div class="row">                                        
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input class="form-control" name="sendcli" id="sendcli" type="text" placeholder="CLI" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input class="form-control" name="conf_name" id="conf_name" type="text" placeholder="Conference Name" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="Date From" autocomplete="off" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="Date To" autocomplete="off" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <select name="country_id" id="country_id" class="form-control custom-select">
                                                    <option value="">All</option>
                                                    @foreach (Helper::getCountry() as $country)
                                                    <option value="{{$country->id}}">{{$country->country_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                 <select name="bridge_id" id="bridge_id" class="form-control custom-select">
                                                    <option value="">All</option>
                                                    @foreach($bridgeips as $bridge)
                                                    <option value="{{$bridge->id}}">{{$bridge->server}}</option>
                                                    @endforeach                                               
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <select name="serviceno" id="serviceno" class="form-control custom-select">
                                                    <option value="">All</option>
                                                    @foreach($bridgeips as $bridge)
                                                    <option value="{{$bridge->serviceno}}">{{$bridge->service_no}}</option>
                                                    @endforeach                                               
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">                            
                                                <select name="conf_status" id="conf_status" class="form-control custom-select">
                                                    <option value="1">Active List</option>
                                                    <option value="2">Upcomming</option> 
                                                    <option value="3">Completed</option>
                                                    <option value="custom">Custom</option> 
                                                    <option value="4">All</option>
                                                </select>
                                            </div>
                                        </div>                                                                                
                                        <div class="col-md-12">
                                                @if(Helper::has_permission('reports'))
                                                <button type="submit" class="btn btn-info" id="export" name="exportdata" value="1">Export</button>
                                                @endif
                                                <button type="button" id="searchBtn" class="btn btn-primary pull-right">Search</button>
                                                <button type="button" id="resetBtn" class="btn btn-secondary" style="float: right; margin-right:10px;">Reset</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div style="width: 100%; float: left; overflow: scroll;">
                                <table id="conferenceList" class="table table-striped table-vertical table-responsive b-0 fixed-solution" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>    
                                            <th style="width:1% !important">Bridge ID</th> 
                                            <th>Company</th>                                       
                                            <th>Conference Name</th>
                                            <th>Host</th>
                                            <th style="width:1% !important">Host Pin</th>
                                            <th style="width:1% !important">User Pin</th>
                                            <th style="width:1% !important">Ports</th>
                                            <th style="width:2% !important">Service No</th>
                                            <th>CLI</th>
                                            <th>Start At</th>                                            
                                            <th>End At</th>
                                            <th>Created On</th>
                                            <th>Status</th>
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
            </div>

            <!-- <iframe src="http://149.36.7.16/iCallMateAEC1/faces/audioConfLive.xhtml?audioconfid=5251&serviceno=443339980048" title="Conf"></iframe> -->

            <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

            
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#conferenceList').DataTable({
                        responsive: true,
                        bSort : true,
                        pageLength: 25,
                        language: { search: '' },
                        processing: true,
                        serverSide: true,
                        ajax: {
                            'url': 'conference-list', 
                            data: function (d) {
                                d.sendcli = $('#sendcli').val();
                                d.conf_name = $('#conf_name').val();
                                d.from_date = $('#from_date').val();
                                d.to_date = $('#to_date').val();
                                d.country_id = $('#country_id').val();
                                d.bridge_id = $('#bridge_id').val();
                                d.serviceno = $('#serviceno').val();
                                d.conf_status = $('#conf_status').val();
                            }              
                        },
                        drawCallback:function(settings)
                        {
                            $('#total_list').html(settings.json.total);
                            $('#active_list').html(settings.json.active);
                            $('#active_port').html(settings.json.active_port);
                            $('#upcoming_list').html(settings.json.upcoming);
                            $('#adv_port').html(settings.json.adv_port);
                            $('#completed_list').html(settings.json.finised);
                        },
                        'dataType': 'json',
                        'columns': [
                            {'data': 'audioconfid', 'name': 'audioconfid'},
                            {'data': 'business_name', 'name': 'business_name'},
                            {'data': 'confname', 'name': 'confname'},
                            {'data': 'name', 'name': 'u.name'},
                            {'data': 'chairperson_pin', 'name': 'chairperson_pin'},
                            {'data': 'participant_pin', 'name': 'participant_pin'},
                            {'data': 'ports', 'name': 'ports','searchable':false},  
                            {'data': 'serviceno', 'name': 'serviceno'},                 
                            {'data' : 'sendcli', 'name': 'sendcli'},            
                            {'data' : 'startdatetime','name' : 'startdatetime'},
                            {'data' : 'enddatetime', 'name' : 'enddatetime'},
                            {'data' : 'created_at', 'name' : 'created_at'},
                            {'data': function(data){
                                switch(parseInt(data.status)){
                                    case 0:
                                        return '<span class="badge badge-warning">In-Active</span>';
                                    break;
                                    case 1:
                                        return '<span class="badge badge-success">Active</span>';
                                    break;
                                    case 2:
                                        return '<span class="badge badge-danger">Failed</span>';
                                    break;
                                }
                            },'name':'status'},
                            
                            {'data': 'action', 'name': 'action','orderable': false, 'searchable': false},
                        ],
                        'order':[[9, 'asc']],
                        'columnDefs': [
                        { 'targets': 1,'width': '80px'},
                        {'defaultContent': '-','targets': '_all'}
                        ],
                        // 'buttons' : ['print', 'csv', 'excel', 'pdf'],
                    });

                    $('.dataTables_filter input').attr('placeholder', 'Search');

                    $(document).on('click','.delete_conference',function(){
                        var id = $(this).data('id');
                        $('#orderCustomLabel').text('Delete Conference');
                        $('#orderCustombody').html('<div class="form-group">Do you really want to delete this conference?<div id="custom_status"></div> </div> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" id="action_delete_conference" data-id="'+ id +'" class="btn btn-danger pull-right">Delete</button>'); 
                        $('#orderCustomModal').modal('show');
                    });
                    $(document).on('click','#action_delete_conference',function(){ 
                        var conference_id = $(this).data('id');
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',                                                
                            url: base_url+'/delete-conference',
                            data: {conference_id:conference_id},
                            success:function(data){                                 
                                if (data.error) {
                                    $('#custom_status').html('<div class="text-danger">'+data.message+'</div>');
                                } else {
                                    $('#custom_status').html('<div class="text-success">Conference deleted successfully</div>');
                                    $('#conferenceList').DataTable().draw();    
                                }
                            }
                        });                                    
                    });

                    $(document).on('click','.show_conference_details',function(){
                        var conference_id = $(this).data('id');
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',                                                
                            url: base_url+'/conference-details',
                            data: {conference_id:conference_id},
                            success:function(data){                                 
                                if (data.error) {
                                    $('#orderCustombody').html('<div class="text-danger">'+data.message+'</div>');
                                } else {
                                    $('#orderCustombody').html(data.html);   
                                }
                                $('#orderCustomModal').modal('show');
                            }
                        });
                    });                    

                    $(document).on('click', '.show_user_data', function(e) {              
                        e.preventDefault();   
                        var id = $(this).data('id');                
                        $('#show_user_'+id).submit();
                    });
                    
                    $(document).on('change', '#conf_status', function(e) {
                        if($(this).val() != 'custom'){
                            $('.datepicker').attr('disabled',true);
                            $('.datepicker').val('');
                        }else{
                            $('.datepicker').attr('disabled',false);
                        }
                    });

                    $('.custom-select').on('change', function(e) {
                        $('#conferenceList').DataTable().draw();                        
                    });

                    $('#searchBtn').on('click', function(e) {
                        $('#conferenceList').DataTable().draw();
                        e.preventDefault();
                    });

                    $('#resetBtn').on('click', function(e) {
                       $('#conf-form')[0].reset();
                       $('#conferenceList').DataTable().draw();
                    });

                    $('#conf-form').on('submit', function(e) {
                       $(this).submit();
                    });

                    $('.datepicker').datepicker({
                        autoclose: true,
                        orientation:'bottom left',
                        format: 'yyyy-mm-dd',
                        todayHighlight: true
                    });

                    setInterval(function(){
                        $('#conferenceList').DataTable().draw();
                    }, 30000);

                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection