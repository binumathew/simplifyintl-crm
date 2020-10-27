@extends('layouts.home')

@section('content')

<div class="Cwrapper">
   <div class="container-fluid">
      <div class="row dashboard_blocks">
         <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <a class="card" href="#">
               <span class="card_count">{{ $today_request }}</span>
               <span class="card_label">Today Orders</span>
            </a>
         </div>
         <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <a class="card" href="#">
               <span class="card_count">{{ $shipped }}</span>
               <span class="card_label">Today Packed</span>
            </a>
         </div>
         <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <a class="card" href="#">
               <span class="card_count">{{ $activated }}</span>
               <span class="card_label">Today Activated</span>
            </a>
         </div>
         <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <a class="card" href="#">
               <span class="card_count">{{ $sim_sold }}</span>
               <span class="card_label">Today Sim Sold</span>
            </a>
         </div>
      </div>
   </div>
</div>
<div id="order_bell"></div>
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
@endif
@endsection
