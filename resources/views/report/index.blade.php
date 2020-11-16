@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link rel="stylesheet" href="{{ asset('plugins/morris/morris.css') }}">
            <script src="{{ asset('plugins/chart/Chart.js') }}"></script>
            <script src="{{ asset('plugins/chart/utils.js') }}"></script>

            <script src="{{ asset('plugins/morris/morris.min.js') }}"></script>
            <script src="{{ asset('plugins/raphael/raphael-min.js') }}"></script>
            <script src="{{ asset('pages/morris.init.js') }}"></script>

            <script src="{{ asset('plugins/chartist/js/chartist.min.js') }}"></script>
            <script src="{{ asset('plugins/chartist/js/chartist-plugin-tooltip.min.js') }}"></script>
            <script src="{{ asset('pages/chartist.init.js') }}"></script>


            <!-- <link rel="stylesheet" href="a{{ asset('plugins/chartist/chartist.min.css') }}"> -->
            <!-- <link rel="stylesheet" href="a{{ asset('plugins/chartist/css/chartist.css') }}"> -->
            <!-- <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/> -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Report</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Report</h4>
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
                    <div class="col-lg-6">
                        <div class="card m-b-20">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Line Chart</h4>

                                <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">25610</h5>
                                        <p class="text-muted font-14">Activated</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">56210</h5>
                                        <p class="text-muted font-14">Pending</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">12485</h5>
                                        <p class="text-muted font-14">Deactivated</p>
                                    </li>
                                </ul>

                                <div id="morris-line-example" class="morris-charts" style="height: 300px"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->

                    <div class="col-lg-6">
                        <div class="card m-b-20">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Bar Chart</h4>

                                <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">6,95,412</h5>
                                        <p class="text-muted font-14">Activated</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">1,63,542</h5>
                                        <p class="text-muted font-14">Pending</p>
                                    </li>
                                </ul>

                                <div id="morris-bar-example" class="morris-charts" style="height: 300px"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card m-b-20">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Area Chart</h4>

                                <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">86541</h5>
                                        <p class="text-muted font-14">Activated</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">2541</h5>
                                        <p class="text-muted font-14">Pending</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">102030</h5>
                                        <p class="text-muted font-14">Deactivated</p>
                                    </li>
                                </ul>

                                <div id="morris-area-example" class="morris-charts" style="height: 300px"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->

                    <div class="col-lg-6">
                        <div class="card m-b-20">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Donut Chart</h4>

                                <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">3201</h5>
                                        <p class="text-muted font-14">Activated</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">85120</h5>
                                        <p class="text-muted font-14">Pending</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">65214</h5>
                                        <p class="text-muted font-14">Deactivated</p>
                                    </li>
                                </ul>

                                <div id="morris-donut-example" class="morris-charts" style="height: 300px"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div>


            <div class="row">
                    <div class="col-lg-6">
                        <div class="card m-b-20">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Area Chart</h4>

                                <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">86541</h5>
                                        <p class="text-muted font-14">Activated</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">2541</h5>
                                        <p class="text-muted font-14">Pending</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">102030</h5>
                                        <p class="text-muted font-14">Deactivated</p>
                                    </li>
                                </ul>

                                <div id="morris-area-example" class="morris-charts" style="height: 300px"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->

                    <div class="col-lg-6">
                        <div class="card m-b-20">
                            <div class="card-body">

                                <h4 class="mt-0 header-title">Donut Chart</h4>

                                <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">3201</h5>
                                        <p class="text-muted font-14">Activated</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">85120</h5>
                                        <p class="text-muted font-14">Pending</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="mb-0">65214</h5>
                                        <p class="text-muted font-14">Deactivated</p>
                                    </li>
                                </ul>

                                <div id="morris-donut-example" class="morris-charts" style="height: 300px"></div>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card m-b-20">
                        <div class="card-body">

                            <h4 class="mt-0 header-title">Area Chart</h4>

                            <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                <li class="list-inline-item">
                                    <h5 class="mb-0">86541</h5>
                                    <p class="text-muted font-14">Activated</p>
                                </li>
                                <li class="list-inline-item">
                                    <h5 class="mb-0">2541</h5>
                                    <p class="text-muted font-14">Pending</p>
                                </li>
                                <li class="list-inline-item">
                                    <h5 class="mb-0">102030</h5>
                                    <p class="text-muted font-14">Deactivated</p>
                                </li>
                            </ul>

                            <div id="morris-area-example" class="morris-charts" style="height: 300px"></div>

                        </div>
                    </div>
                </div> <!-- end col -->

                <div class="col-lg-6">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <h4 class="mt-0 header-title">Donut Chart</h4>

                            <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                <li class="list-inline-item">
                                    <h5 class="mb-0">3201</h5>
                                    <p class="text-muted font-14">Activated</p>
                                </li>
                                <li class="list-inline-item">
                                    <h5 class="mb-0">85120</h5>
                                    <p class="text-muted font-14">Pending</p>
                                </li>
                                <li class="list-inline-item">
                                    <h5 class="mb-0">65214</h5>
                                    <p class="text-muted font-14">Deactivated</p>
                                </li>
                            </ul>
                            <div id="canvas-holder">
                                <canvas id="chart-area"></canvas>
                            </div>
                            <button id="randomizeData">Randomize Data</button>
                            <button id="addDataset">Add Dataset</button>
                            <button id="removeDataset">Remove Dataset</button>
                            <!-- <h4 class="mt-0 header-title">Donut Chart</h4>

                            <ul class="list-inline widget-chart m-t-20 m-b-15 text-center">
                                <li class="list-inline-item">
                                    <h5 class="mb-0">3201</h5>
                                    <p class="text-muted font-14">Activated</p>
                                </li>
                                <li class="list-inline-item">
                                    <h5 class="mb-0">85120</h5>
                                    <p class="text-muted font-14">Pending</p>
                                </li>
                                <li class="list-inline-item">
                                    <h5 class="mb-0">65214</h5>
                                    <p class="text-muted font-14">Deactivated</p>
                                </li>
                            </ul>

                            <div id="morris-donut-example" class="morris-charts" style="height: 300px"></div>
 -->
                        </div>
                    </div>
                </div> <!-- end col -->
            </div>

            <!-- <iframe src="http://149.36.7.16/iCallMateAEC1/faces/audioConfLive.xhtml?audioconfid=5251&serviceno=443339980048" title="Conf"></iframe> -->

            <!-- <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script> -->


            <script type="text/javascript">
                var randomScalingFactor = function() {
                        return Math.round(Math.random() * 100);
                    };

                    var config = {
                        type: 'pie',
                        data: {
                            datasets: [{
                                data: [
                                    randomScalingFactor(),
                                    randomScalingFactor(),
                                    randomScalingFactor(),
                                    randomScalingFactor(),
                                    randomScalingFactor(),
                                ],
                                backgroundColor: [
                                    window.chartColors.red,
                                    window.chartColors.orange,
                                    window.chartColors.yellow,
                                    window.chartColors.green,
                                    window.chartColors.blue,
                                ],
                                label: 'Dataset 1'
                            }],
                            labels: [
                                'Red',
                                'Orange',
                                'Yellow',
                                'Green',
                                'Blue'
                            ]
                        },
                        options: {
                            responsive: true
                        }
                    };

                    window.onload = function() {
                        var ctx = document.getElementById('chart-area').getContext('2d');
                        window.myPie = new Chart(ctx, config);
                    };

                    document.getElementById('randomizeData').addEventListener('click', function() {
                        config.data.datasets.forEach(function(dataset) {
                            dataset.data = dataset.data.map(function() {
                                return randomScalingFactor();
                            });
                        });

                        window.myPie.update();
                    });

                    var colorNames = Object.keys(window.chartColors);
                    document.getElementById('addDataset').addEventListener('click', function() {
                        var newDataset = {
                            backgroundColor: [],
                            data: [],
                            label: 'New dataset ' + config.data.datasets.length,
                        };

                        for (var index = 0; index < config.data.labels.length; ++index) {
                            newDataset.data.push(randomScalingFactor());

                            var colorName = colorNames[index % colorNames.length];
                            var newColor = window.chartColors[colorName];
                            newDataset.backgroundColor.push(newColor);
                        }

                        config.data.datasets.push(newDataset);
                        window.myPie.update();
                    });

                    document.getElementById('removeDataset').addEventListener('click', function() {
                        config.data.datasets.splice(0, 1);
                        window.myPie.update();
                    });


                $(document).ready(function(){

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

                    // $(document).on('change', '#conf_status', function(e) {
                    //     if($(this).val() != 'custom'){
                    //         $('.datepicker').attr('disabled',true);
                    //         $('.datepicker').val('');
                    //     }else{
                    //         $('.datepicker').attr('disabled',false);
                    //     }
                    // });

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

                    // $('.datepicker').datepicker({
                    //     autoclose: true,
                    //     orientation:'bottom left',
                    //     format: 'yyyy-mm-dd',
                    //     todayHighlight: true
                    // });

                    // setInterval(function(){
                    //     $('#conferenceList').DataTable().draw();
                    // }, 30000);

                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection
