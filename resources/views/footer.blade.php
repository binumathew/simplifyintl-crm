
        <footer class="footer">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        {{ date('Y') }} © {{config('app.name')}}. All Rights Reserved.
                    </div>
                </div>
            </div>
        </footer>
        <style type="text/css">
        .drag-target {
            width: 25%;
            position: fixed;
            top: 133px;
            margin-left: 72%;
            z-index: 99;
            -webkit-animation: fading 5s; /* Safari 4.0 - 8.0 */
            animation: fading 5s;
            /*animation: fadeIn ease 10s;*/
            /*-webkit-animation: fading 5s infinite;*/
            /*animation: fading 5s infinite;*/

        }s
        @keyframes fading {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }
        </style>
        <script src="{{ asset('js/moment.js') }}"></script>
        <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('js/modernizr.min.js') }}"></script>
        <script src="{{ asset('js/waves.js') }}"></script>
        <script src="{{ asset('js/jquery.slimscroll.js') }}"></script>
        <script src="{{ asset('js/jquery.nicescroll.js') }}"></script>
        <script src="{{ asset('js/jquery.scrollTo.min.js') }}"></script>
        <script src="{{ asset('js/alertify.min.js') }}"></script>
        <script src="{{ asset('plugins/morris/morris.min.js') }}"></script>
        <script src="{{ asset('plugins/raphael/raphael-min.js') }}"></script>
        <script src="{{ asset('pages/morris.init.js') }}"></script>
        <script src="{{ asset('plugins/chartist/js/chartist.min.js') }}"></script>
        <script src="{{ asset('plugins/chartist/js/chartist-plugin-tooltip.min.js') }}"></script>
        <script src="{{ asset('plugins/chart/chart.js') }}"></script>
        <script src="{{ asset('plugins/flot-chart/jquery.flot.min.js') }}"></script>
        <script src="{{ asset('plugins/flot-chart/jquery.flot.time.js') }}"></script>
        <script src="{{ asset('plugins/flot-chart/jquery.flot.tooltip.min.js') }}"></script>
        <script src="{{ asset('plugins/flot-chart/jquery.flot.resize.js') }}"></script>
        <script src="{{ asset('plugins/flot-chart/jquery.flot.pie.js') }}"></script>
        <script src="{{ asset('plugins/flot-chart/jquery.flot.selection.js') }}"></script>
        <script src="{{ asset('plugins/flot-chart/jquery.flot.stack.js') }}"></script>
        <script src="{{ asset('plugins/flot-chart/curvedLines.js') }}"></script>
        <script src="{{ asset('plugins/flot-chart/jquery.flot.crosshair.js') }}"></script>
        <script src="{{ asset('plugins/highcharts.js') }}"></script>
        <script src="{{ asset('js/bootstrap4-toggle.min.js') }}"></script>
        <script src="{{ asset('js/settings.js') }}"></script>
        <script src="{{ asset('js/support.js') }}"></script>
        <script src="{{ asset('js/commission.js') }}"></script>

        <!-- <script src="https://maps.google.com/maps/api/js?key=AIzaSyCtSAR45TFgZjOs4nBFFZnII-6mMHLfSYI"></script> -->


        <!-- App js -->
        <script src="{{ asset('js/app.js') }}"></script>
        <!-- <script src="{{ asset('js/ajax.js') }}"></script> -->
        <script type="text/javascript">
            $(document).ready(function(){
                alertify.set('notifier','position', 'top-right');
                // setTimeout(function(){
                //     $('.drag-target').html('<div class="alert alert-success alert-colored" role="alert"><strong>Notification</strong> Settings Updated</div> AVO69615617');
                // }, 5000);
                // alertify.dismissAll();

                // alertify.success('<div class="alert alert-success alert-colored" role="alert"><strong>Notification</strong> Settings Updated</div>');
                // setTimeout(function(){
                //     alertify.error('<div class="alert alert-danger alert-colored mb-0" role="alert"><strong>Update Failure.</strong> Please Try Again After Sometime.</div>');
                // }, 5000);
                $(document).on('click', '.notify_log', function(e) {
                    e.preventDefault();
                    $(this).attr('disabled','true');
                    var notify_id  = $(this).attr('data-id');
                    var notify_type = $(this).attr('data-type');
                    var param       = $(this).attr('data-param');
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: base_url+'/notify-process',
                        data: {notify_id:notify_id},
                        beforeSend: function(){
                            $("#preloader,#status").show();
                        },
                        complete: function(){
                            $("#preloader,#status").hide();
                        },
                        success:function(data){
                            if (data.error) {
                               alertify.error('Error in fetching notifications');
                            } else {
                                if(notify_type == 'order_activation'){
                                    var route = "{{URL::to('/order-details')}}";
                                    var html = '<form method="post" id="notify_get_order" action="'+route+'">@csrf<input type="hidden" name="order_id" value="'+param+'"></form>';
                                    $('.notify_form').html('').html(html);
                                    $("#notify_get_order").submit();
                                }
                                // location.href = base_url+data.redirect;  
                            }
                        }
                    });
                });
            });

        </script>
        <!-- custom modal popup start -->
        <div id="orderCustomModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mt-0" id="orderCustomLabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" id="orderCustombody">
                    </div>
                </div>
            </div>
        </div>
        <!-- custom modal popup end -->
    </body>
</html>
