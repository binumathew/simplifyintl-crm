@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('plugins/smartwizard/smart_wizard.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>

            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Porting Process</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Porting Process</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <form id="form-horizontal" class="form-horizontal wizard clearfix">
                                @csrf
                                <div class="steps clearfix activation-process-page">
                                    <ul role="tablist">
                                        <li role="tab" class="first current" aria-disabled="false" aria-selected="true">
                                            <a id="form-horizontal-t-0" href="javascript:void(0);" aria-controls="form-horizontal-p-0">
                                                <span class="current-info audible"></span><span class="number">1.</span> Porting Request
                                            </a>
                                        </li>
                                        <li role="tab" class="done" aria-disabled="false" aria-selected="false">
                                            <a id="form-horizontal-t-1" href="javascript:void(0);" aria-controls="form-horizontal-p-1">
                                                <span class="number">2.</span> Register Request
                                            </a>
                                        </li>
                                        <li role="tab" class="disabled" aria-disabled="true">
                                            <a id="form-horizontal-t-2" href="javascript:void(0);" aria-controls="form-horizontal-p-2">
                                                <span class="number">3.</span> Initiated
                                            </a>
                                        </li>
                                        <li role="tab" class="disabled" aria-disabled="true">
                                            <a id="form-horizontal-t-2" href="javascript:void(0);" aria-controls="form-horizontal-p-2">
                                                <span class="number">4.</span> Processed
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="content clearfix">
                                    <table id="datatable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Temporary No ({{ config('settings.app_name') }})</th>
                                                <th>Number To Keep (Current Provide)</th>
                                                <th>PAC Number</th>
                                                <th>Provider</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="odd">
                                               <td>1234567890</td>
                                               <td><input type="text" class="form-control"></td>
                                               <td><input type="text" class="form-control"></td>
                                               <td><select class="form-control">
                                                   <option>Select</option>
                                                   <option>EE</option>
                                                   <option>Vodafone</option>
                                               </select></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="actions clearfix">
                                    <ul role="menu" aria-label="Pagination">
                                        <li><a href="{{ url('port-list') }}" role="menuitem">Cancel</a></li>
                                        <li><a href="" role="menuitem">Next</a></li>
                                        <li style="display: none;"><a href="#finish" role="menuitem">Finish</a></li>
                                    </ul>
                                </div>
                            </form>

                            <div id="smartwizard">
                                <ul class="nav">
                                   <li>
                                       <a class="nav-link" href="#step-1">
                                          Step 1
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-2">
                                          Step 2
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-3">
                                          Step 3
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-4">
                                          Step 4
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-5">
                                          Step 5
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-6">
                                          Step 6
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-7">
                                          Step 7
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-8">
                                          Step 8
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-9">
                                          Step 9
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-10">
                                          Step 10
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-7">
                                          Step 7
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-8">
                                          Step 8
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-9">
                                          Step 9
                                       </a>
                                   </li>
                                   <li>
                                       <a class="nav-link" href="#step-10">
                                          Step 10
                                       </a>
                                   </li>
                                </ul>

                                <div class="tab-content">
                                   <div id="step-1" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-2" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-3" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-4" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-5" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-6" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-7" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-8" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-9" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-10" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-7" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-8" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-9" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                   <div id="step-10" class="tab-pane" role="tabpanel">
                                      Step content
                                   </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="{{ asset('plugins/smartwizard/smart_wizard.js') }}"></script>
            <script src="{{ asset('plugins/jquery-steps/jquery.steps.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>


            <script type="text/javascript">
                $(document).ready(function(){
                    $('#smartwizard').smartWizard({
                        selected: 0,
                        theme: 'arrows',
                        transitionEffect:'fade',
                        useURLhash: false,
                        showStepURLhash: false,
                        toolbarSettings: {
                            toolbarButtonPosition: 'right',
                            showPreviousButton: false,
                        },
                        anchorSettings: {
                            anchorClickable: false,
                        },
                    });

                    // var selected = '<?php echo $port->status; ?>';
                    // $('#smartwizard').smartWizard({
                    //     selected: <?php echo $port->status; ?>, // Initial selected step, 0 = first step
                    //     theme: 'default', // theme for the wizard, related css need to include for other than default theme
                    //     justified: true, // Nav menu justification. true/false
                    //     autoAdjustHeight: true, // Automatically adjust content height
                    //     cycleSteps: false, // Allows to cycle the navigation of steps
                    //     backButtonSupport: false, // Enable the back button support
                    //     enableURLhash: true, // Enable selection of the step based on url hash
                    //     transition: {
                    //       animation: 'none', // Effect on navigation, none/fade/slide-horizontal/slide-vertical/slide-swing
                    //       speed: '400', // Transion animation speed
                    //       easing:'' // Transition animation easing. Not supported without a jQuery easing plugin
                    //     },
                    //     toolbarSettings: {
                    //       toolbarPosition: 'bottom', // none, top, bottom, both
                    //       toolbarButtonPosition: 'right', // left, right, center
                    //       showNextButton: true, // show/hide a Next button
                    //       showPreviousButton: false, // show/hide a Previous button
                    //       toolbarExtraButtons: [] // Extra buttons to show on toolbar, array of jQuery input/buttons elements
                    //     },
                    //     anchorSettings: {
                    //       anchorClickable: false, // Enable/Disable anchor navigation
                    //       enableAllAnchors: false, // Activates all anchors clickable all times
                    //       markDoneStep: true, // Add done css
                    //       markAllPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
                    //       removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
                    //       enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
                    //     },
                    //     lang: { // Language variables for button
                    //       next: 'Next',
                    //       previous: 'Previous'
                    //     },
                    //     disabledSteps: [], // Array Steps disabled
                    //     errorSteps: [], // Highlight step with errors
                    //     hiddenSteps: [] // Hidden steps
                    // });


                    $(document).on('click','.delete_user',function(){
                        var id = $(this).attr('user-id');
                        if(confirm('Do you really want to delete this contact ?')){
                            $('#delete_user_'+id).submit();
                        }
                    });

                    $('#searchBtn').on('click', function(e) {
                        $('#portlist').DataTable().draw();
                        e.preventDefault();
                    });

                    $(document).on('click', '.show_user_data', function(e) {
                        e.preventDefault();
                        var id = $(this).attr('user-id');
                        $('#show_user_'+id).submit();
                    });

                    $('.datepicker').datepicker({
                        autoclose: true,
                        orientation:'bottom left',
                        format: 'yyyy-mm-dd',
                        todayHighlight: true
                    });

                    $('#resetBtn').on('click', function(e) {
                       $('#port-search-form')[0].reset();
                    });
                    $('#port-search-form').on('submit', function(e) {
                       $(this).submit();
                    });

                });
            </script>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->
@endsection
