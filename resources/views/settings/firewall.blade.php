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
                                <li class="breadcrumb-item active">Firewall</li>
                            </ol>
                        </div>
                        <h4 class="page-title">White Listed IP</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <div class="card m-b-20">
                        
                        <div class="card-body right-nav">
                            <ul>
                                <li><a href="{{ url('/settings') }}">General</a></li>                                    
                                <li><a href="{{ url('/template') }}">Email Template</a></li>
                                <li><a href="{{ url('/roles') }}">Roles</a></li>
                                <li><a href="{{ url('/countries') }}">Countries</a></li>
                                <li><a href="{{ url('/credits') }}">Credit</a></li>
                                <li><a href="{{ url('/coupons') }}">Coupons</a></li>
                                <li><a href="{{ url('/switch') }}">Switch</a></li>
                                <li><a href="{{ url('/did-pool') }}">DID Pool</a></li>
                                <li><a href="{{ url('/throttles') }}">Throttles</a></li>
                                <li><a href="{{ url('/firewall') }}" class="selected">Firewall</a></li>
                                <li><a href="{{ url('/scheduled-tasks') }}">Cron Jobs</a></li>
                                <li><a href="{{ url('/payment-gateway') }}">Payment Gateways</a></li>
                                <li><a href="{{ url('/stock-list') }}">Stock</a></li>
                                <li><a href="{{ url('/api-logger') }}">API Log</a></li>

                                <!-- <li><a href="#">Leads</a></li>
                                <li><a href="#">SMS</a></li>
                                <li><a href="#">Calendar</a></li>
                                <li><a href="#">PDF</a></li>
                                <li><a href="#">E-Sign</a></li>
                                <li><a href="#">Cron Job</a></li>
                                <li><a href="#">Tags</a></li>
                                <li><a href="#">Pusher.com</a></li>
                                <li><a href="#">Google</a></li>
                                <li><a href="#">Misc</a></li> -->
                            </ul>                            
                        </div>
                    </div>
                </div>

                <div class="col-md-10">
                    <div class="card m-b-20">
                        <div class="card-body">  
                             <button class="btn btn-success pull-right" id="add_firewall">ADD IP</button>
                            <table id="firewalllist" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                <thead>
                                    <tr> 
                                        <tr>
                                        <th>#</th>
                                        <th>IP Address</th>                                     
                                        <th>Description</th>
                                        <th>Created On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 0; @endphp                
                                    @foreach ($firewall as $ip)                                        
                                    <tr class="odd" id="ip-row{{$ip->id}}">
                                        <td>{{ ++$i }}</td>
                                        <td id="address_{{$ip->id}}">{{ $ip->ip_address }}</td>
                                        <td id="desc_{{$ip->id}}">{{ $ip->description }}</td>
                                        <td>{{ Helper::date_format($ip->created_at) }}</td>
                                        <td>
                                            @if(Helper::has_permission('firewall', 'edit'))
                                                <a data-toggle="tooltip" title="Edit"  data-list="{{$ip->id}}" class="text-muted edit_firewall" href="javascript:void(0);"><i class="mdi mdi-pencil mdi-24px"></i></a>
                                            @endif
                                            @if(Helper::has_permission('firewall', 'delete'))
                                                &nbsp;&nbsp;&nbsp;<a data-toggle="tooltip" title="Delete" data-list="{{ $ip->id }}" class="text-danger delete_firewall" href="javascript:void(0);"><i class="mdi mdi-delete mdi-24px"></i></a>
                                             @endif
                                        </td>                                            
                                    </tr>
                                    @php @endphp
                                    @endforeach
                                </tbody>
                            </table>                         
                        </div>
                    </div>
                </div>
            </div>
            <div id="firewallPopup" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title mt-0" id="firewallLabel"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ url('/save-firewall') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label>IP Address</label>
                                    <input type="hidden" id="whitelist_id" name="id">
                                    <input type="text" id="ip_address" class="form-control" name="ip_address">
                                </div>
                                <div class="form-group">
                                    <label>Descrtiption</label>                                 
                                    <input type="text" id="description" class="form-control" name="description">
                                </div>      
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success pull-right"  id="save_whitelist">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>                              

            <div id="firewallConfirmPopup" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title mt-0" id="firewallLabel">Confirm Delete!</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ url('/delete-firewall') }}" method="post">
                                @csrf                
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this item?</p>
                                    <input type="hidden" id="firewall_id" name="id">
                                    <div class="popup_btn_holder">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit"  class="btn btn-danger pull-right">Delete</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>    
                </div>
            </div>

            <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
            
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#firewalllist').DataTable({ responsive: true, bSort : true, pageLength: 25 });

                    $('.dataTables_filter input').attr('placeholder', 'Search');

                    $(document).on('click','.edit_firewall',function () {
                        var id = $(this).data('list');
                        $('#firewallLabel').text('Edit White Listed IP');
                        $('#whitelist_id').val(id);
                        $('#ip_address').val($('#address_'+id).text());
                        $('#description').val($('#desc_'+id).text());
                        $('#firewallPopup').modal('show');
                    });

                    $(document).on('click','#add_firewall',function () {
                        $('#firewallLabel').text('Add White Listed IP');
                        $('#whitelist_id').val('');
                        $('#ip_address').val('');
                        $('#description').val('');
                        $('#firewallPopup').modal('show');
                    });

                    $(document).on('click','.delete_firewall',function () {
                        var id = $(this).data('list');
                        $('#firewall_id').val(id);
                        $('#firewallConfirmPopup').modal('show');
                    });                   
                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection