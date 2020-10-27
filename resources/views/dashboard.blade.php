@extends('layouts.home')
@section('content')
   <!-- page wrapper start -->
   <div class="wrapper">
      <div class="container-fluid" id="result">
         <link href="{{ asset('public/plugins/c3/c3.min.css') }}" rel="stylesheet" type="text/css" />
         <div class="row">
            <div class="col-sm-12">
               <div class="page-title-box">
                  <div class="btn-group pull-right">                        
                     <div class="form-group">                                 
                        <select name="currency" class="form-control currency">
                            <option value="GBP" {{($currency == 'GBP')?'selected':''}}>GBP</option>
                            <option value="USD" {{($currency == 'USD')?'selected':''}}>USD</option>  
                            <option value="EUR" {{($currency == 'EUR')?'selected':''}}>EUR</option>
                        </select>
                     </div>
                     &nbsp;&nbsp; 
                     <div class="form-group ">                                 
                        <select name="date-range" class="form-control date-range">
                           <option value="today" {{($filter=='today')?'selected':''}}>Today </option>
                           <option value="this_week" {{($filter=='this_week')?'selected':''}}>This Week </option>
                           <!-- {{ date('d M', strtotime('last week')).' - '.date('d M', strtotime('last week'))}}
                           {{ date('Y-m-d', strtotime('-'.date('w').' days')).'/'.date('Y-m-d')}} -->
                           <option value="last_week" {{($filter=='last_week')?'selected':''}}>Last Week </option>
                           <option value="this_month" {{($filter=='this_month')?'selected':''}}>This Month </option>
                           <option value="last_month" {{($filter=='last_month')?'selected':''}}>Last Month </option>
                           <option value="this_year" {{($filter=='this_year')?'selected':''}}>This Year </option>
                           <!-- <option value="last_year">Last Year </option> -->
                           <!-- <option value="custom">Custom Period</option> -->
                        </select>
                     </div>                          
                    <!-- <ol class="breadcrumb hide-phone p-0 m-0">                           
                        <li class="breadcrumb-item active">Home</li>
                    </ol> -->
                  </div>
                  <h4 class="page-title">Dashboard</h4>
               </div>
            </div>
         </div>

         <div class="row">
            <div class="col-md-6 col-lg-6 col-xl-3">
               <div class="mini-stat clearfix bg-white">
                  <span class="float-right">
                     <input class="knob" data-width="60" data-height="60" data-linecap=round data-fgColor="#004a80" value="{{abs($act_percent)}}" data-skin="tron" data-angleOffset="180" data-readOnly=true data-thickness=".1"/>
                  </span>
                  <!--  <span class="mini-stat-icon bg-purple mr-0 float-right"><i class="mdi mdi-basket"></i></span> -->
                  <div class="mini-stat-info">
                     <span class="counter text-purple">{{$activated}}</span>
                     New Activations <a href="{{url('/')}}"><i class="mdi mdi-eye"></i></a>
                  </div>
                  <div class="clearfix" style="width:100%; float:left;"></div>
                  <span class="pull-right"><i class="fa fa-caret-{{($act_percent >0)?'up':'down'}} m-r-5"></i>{{abs($act_percent)}}%</span>
               </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-3">
               <div class="mini-stat clearfix bg-white">
                  <span class="float-right"><input class="knob" data-width="60" data-height="60" data-linecap=round data-fgColor="#34bb54" value="{{abs($ord_percent)}}" data-skin="tron" data-angleOffset="180" data-readOnly=true data-thickness=".1"/></span>
                  <!-- <span class="mini-stat-icon bg-blue-grey mr-0 float-right"><i class="mdi mdi-black-mesa"></i></span> -->
                  <div class="mini-stat-info">
                     <span class="counter text-blue-grey">{{$ordered}}</span>
                     New Orders <a href="{{url('/')}}"><i class="mdi mdi-eye"></i></a>
                  </div>
                  <div class="clearfix" style="width:100%; float:left;"></div>
                  <span class="pull-right"><i class="fa fa-caret-{{($ord_percent >0)?'up':'down'}} m-r-5"></i>{{abs($ord_percent)}}%</span>
               </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-3">
               <div class="mini-stat clearfix bg-white">
                  <span class="float-right">
                     <input class="knob" data-width="60" data-height="60" data-linecap=round data-fgColor="#8d6e63" value="{{abs($rev_percent)}}" data-skin="tron" data-angleOffset="180" data-readOnly=true data-thickness=".1"/>
                  </span>
                  <!-- <span class="mini-stat-icon bg-brown mr-0 float-right"><i class="mdi mdi-buffer"></i></span> -->
                  <div class="mini-stat-info">
                     <span class="counter text-brown">{{$cur_list[$currency].Helper::number_format($revenue)}}</span>
                     Revenue <a href="{{url('/')}}"><i class="mdi mdi-eye"></i></a>
                  </div>
                  <div class="clearfix" style="width:100%; float:left;"></div>
                  <span class="pull-right"><i class="fa fa-caret-{{($rev_percent >0)?'up':'down'}} m-r-5"></i>{{abs($rev_percent)}}%</span>
               </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-3">
               <div class="mini-stat clearfix bg-white">
                  <span class="float-right">
                     <input class="knob" data-width="60" data-height="60" data-linecap=round data-fgColor="#90a4ae" value="{{abs($rec_percent)}}" data-skin="tron" data-angleOffset="180" data-readOnly=true data-thickness=".1"/>
                  </span>
                  <!-- <span class="mini-stat-icon bg-teal mr-0 float-right"><i class="mdi mdi-coffee"></i></span> -->
                  <div class="mini-stat-info">
                     <span class="counter text-teal">{{$recurring}}</span>
                     Recurring <a href="{{url('/')}}"><i class="mdi mdi-eye"></i></a>
                  </div>
                  <div class="clearfix" style="width:100%; float:left;"></div>
                  <span class="pull-right"><i class="fa fa-caret-{{($rec_percent >0)?'up':'down'}} m-r-5"></i>{{abs($rec_percent)}}%</span>
               </div>
            </div>
         </div>
         @if(Helper::has_permission('reports'))
         <div class="row">
            <div class="col-xl-9">
               <div class="row">
                  <div class="col-md-9 pr-md-0">
                     <div class="card m-b-20" style="height: 486px;">
                        <div class="card-body">
                           <h4 class="mt-0 header-title">Monthly Earnings</h4>
                           <div class="text-center">
                              <div class="btn-group m-t-20" role="group" aria-label="Earnings">
                                 <button type="button" class="btn earning_filter btn-secondary {{ ($duration == 'day')?'active':''}}" data-filter="day">Day</button>
                                 <button type="button" class="btn earning_filter btn-secondary {{ ($duration == 'month')?'active':''}}" data-filter="month">Month</button>
                                 <button type="button" class="btn earning_filter btn-secondary {{ ($duration == 'year')?'active':''}}" data-filter="year">Year</button>
                              </div>
                           </div>
                           <div id="combine-chart" class="m-t-20"></div>                           
                        </div>
                     </div>
                  </div>

                  <div class="col-md-3 pl-md-0">
                     <div class=" card m-b-20" style="height: 486px;">
                        <div class="card-body">
                           <div class="m-b-20">
                              <p>Weekly Earnings</p>
                              {{$cur_list[$currency].Helper::number_format(array_sum($earnings['weekly']))}}</h5>
                              <span class="peity-line" data-width="100%" data-peity='{ "fill": ["rgba(103,168,228,0.3)"],"stroke": ["rgba(103,168,228,0.8)"]}' data-height="60">{{ implode(',',$earnings['weekly']) }}</span>
                           </div>
                           <div class="m-b-20">
                              <p>Monthly Earnings</p>
                              {{$cur_list[$currency].Helper::number_format(array_sum($earnings['monthly']))}}</h5>
                              <span class="peity-line" data-width="100%" data-peity='{ "fill": ["rgba(74,193,142,0.3)"],"stroke": ["rgba(74,193,142,0.8)"]}' data-height="60">{{ implode(',',$earnings['monthly']) }}</span>
                           </div>
                           <div class="m-b-20">
                              <p>Yearly Earnings</p>
                              {{$cur_list[$currency].Helper::number_format(array_sum($earnings['yearly']))}}</h5>
                              <span class="peity-line" data-width="100%" data-peity='{ "fill": ["rgba(232, 65, 38,0.3)"],"stroke": ["rgba(232, 65, 38,0.8)"]}' data-height="60">{{ implode(',',$earnings['yearly']) }}</span> 
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-xl-3">
               <div class="card m-b-20">
                  <div class="card-body">
                     <h4 class="mt-0 m-b-15 header-title">Recent Activity Feed</h4>
                     <ol class="activity-feed mb-0">
<!--                        <li class="feed-item">
                           <div class="activity-text">Request Recieved on</div>
                           <span class="date font-600 text-muted">{{ date('M d, Y H:i',strtotime($sim_request->created_at))}}</span>                        
                       </li> -->
                       @foreach ($order_status as $status)
                       <li class="feed-item">                                            
                          <span class="activity-text ">{{$status->note}}</span>
                          <span class="date font-600 text-muted">{{ date('M d, Y H:i',strtotime($status->time))}}</span>
                       </li>                            
                        @endforeach
                     </ol>
<!--                      <ol class="activity-feed mb-0">
                        <li class="feed-item">
                           <span class="date">Sep 25</span>
                           <span class="activity-text">Added a new customer</span>
                        </li>
                        <li class="feed-item">
                           <span class="date">Sep 24</span>
                           <span class="activity-text">Customer credit card updated</span>
                        </li>
                        <li class="feed-item">
                           <span class="date">Sep 23</span>
                           <span class="activity-text">New delivery note added</span>
                        </li>
                        <li class="feed-item">
                           <span class="date">Sep 21</span>
                           <span class="activity-text">New order shippied</span>
                        </li>
                        <li class="feed-item">
                           <span class="date">Sep 21</span>
                           <span class="activity-text">New order shippied</span>
                        </li>

                     </ol> -->

                     <!-- <div class="text-center">
                        <a href="#" class="btn btn-sm btn-primary">View More</a>
                     </div> -->
                  </div>
               </div>
            </div>
         </div>
         @endif
         <div class="row">
            <div class="col-xl-6">
               <div class="card m-b-20">
                  <div class="card-body">
                     <h4 class="mt-0 m-b-30 header-title">Latest Transactions</h4>
                     <div class="table-responsive">
                        <table class="table table-vertical">
                           <tbody>
                              <tr  class="m-0 text-muted font-14">
                                 <th> Name </th>
                                 <th> Status </th>
                                 <th> Amount </th>
                                 <th> Gateway </th>
                                 <th> Created On </th>         
                              </tr>
                              @foreach($transaction as $payment)
                                 <tr>
                                    <td>                                                   
                                       {{ $payment->name }}
                                    </td>
                                    <td>
                                    @if( $payment->status == 1 )
                                       <i class="mdi mdi-checkbox-blank-circle text-success"></i> Confirm
                                    @elseif( $payment->status == 3 )
                                       <i class="mdi mdi-checkbox-blank-circle text-warning"></i> Refund
                                    @else
                                       <i class="mdi mdi-checkbox-blank-circle text-danger"></i> Failed                                       
                                    @endif
                                    </td>
                                    <td class="text-right">
                                       {{$cur_list[$payment->currency]}}{{ Helper::number_format($payment->total_amount) }}                                       
                                    </td>
                                    <td>
                                       {{ $payment->payment_method }}                                      
                                    </td>
                                    <td>
                                       {{ Helper::date_format($payment->created_at) }}
                                    </td>
                                 </tr>
                              @endforeach   
                           </tbody>
                        </table>
                     </div>
                     <div class="text-center">
                        <a href="{{url('/payment-history')}}" class="btn btn-sm btn-primary">View More</a>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-xl-6">
               <div class="card m-b-20">
                  <div class="card-body">
                     <h4 class="mt-0 m-b-30 header-title">Latest Orders</h4>
                     <div class="table-responsive">
                        <table class="table table-vertical mb-1">
                           <tbody>
                              <tr  class="m-0 text-muted font-14">
                                 <th> Order ID </th>
                                 <th> Customer </th>
                                 <th> Status </th>
                                 <th> Amount </th>
                                 <!-- <th> Agent </th> -->
                                 <th> Created On </th>
                                 <!-- <th> </th> -->
                              </tr>
                              @foreach($orders as $order)
                                 <tr>
                                    <td>{{$order->order_id}}</td>
                                    <td>{{$order->user->name}}</td>
                                    <td>
                                       @if($order->delivery_status == 0)
                                          <span class="badge badge-pill badge-secondary">Received</span>
                                       @elseif($order->delivery_status == 1)
                                          <span class="badge badge-pill badge-info">Shipped</span>
                                       @elseif($order->delivery_status == 2)
                                          <span class="badge badge-pill badge-warning">Activated</span>
                                       @elseif($order->delivery_status == 3)
                                          <span class="badge badge-pill badge-success">Finish</span>
                                       @else
                                          <span class="badge badge-pill badge-danger">Canceled</span>   
                                       @endif
                                    </td>
                                    <td class="text-right">
                                       {{$cur_list[$order->payment->currency]}}{{ Helper::number_format($order->payment->total_amount) }}
                                    </td>
                                    <!-- <td>{{$order->promocode}}</td>                                  -->
                                    <td>
                                       {{ Helper::date_format($order->created_at) }}
                                    </td>
                                 </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                     <div class="text-center">
                        <a href="{{url('/orders')}}" class="btn btn-sm btn-primary">View More</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <form id="dash-form" action="dashboard" method="post">
            @csrf
            <input type="hidden" id="currency" name="currency" value="{{$currency}}">
            <input type="hidden" id="duration" name="duration" value="{{$duration}}">
            <input type="hidden" id="filter" name="filter" value="{{$filter}}">
            <input type="hidden" id="custom_from" name="custom_from" value="">
            <input type="hidden" id="custom_to" name="custom_to" value="">
         </form>
         <script>
            chart = '<?php echo json_encode($chart); ?>';
            chart = JSON.parse(chart);
         </script>  
         <script src="{{ asset('public/plugins/peity-chart/jquery.peity.min.js') }}"></script>
         <script src="{{ asset('public/plugins/d3/d3.min.js') }}"></script>
         <script src="{{ asset('public/plugins/c3/c3.min.js') }}"></script>
         <script src="{{ asset('public/plugins/jquery-knob/excanvas.js') }}"></script>
         <script src="{{ asset('public/plugins/jquery-knob/jquery.knob.js') }}"></script>
         <script src="{{ asset('public/pages/dashboard.js') }}"></script>

         <script>
            $(document).ready(function () {
               $(document).on('click','.earning_filter',function(){
                  var filter = $(this).data('filter');
                  $('#duration').val(filter);
                  $('#dash-form').submit();
               });
               $(document).on('change','.currency',function(){
                  var currency = $(this).val();
                  $('#currency').val(currency);
                  $('#dash-form').submit();
               });
               
               
               $(document).on('change','.date-range',function(){
                  var filter = $(this).val();
                  $('#filter').val(filter);
                  $('#dash-form').submit();
               });
            });
         </script>
      </div>
      <!-- end container-fluid -->
   </div>
   <!-- page wrapper end -->

<!-- <div id="order_bell"></div> 
@if(Auth::user()->role == 1 || Auth::user()->role == 6)
<script>
   $(document).ready(function () {
      setInterval(function(){  
         $.ajax({
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',                                                
            url: base_url+'/new-order',            
            success:function(data){ 
               if(data > 0) {
                  $('#order_bell').html("<audio  autoplay='true' hidden='true'><source  id='myAudioElement'  src='"+base_url+"/public/bell/beep.mp3' type='audio/mpeg'></audio>");
                  // location.reload();
               }                  
            }
         });
      }, 10000);
   });
</script>
@endif -->
@endsection
