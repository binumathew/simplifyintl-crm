@extends('layouts.home')
@section('content')

<div class="Cwrapper">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8">
        <div class="Cleftpart">
          <h1>Assign To Dealer</h1>
        </div>
        <form action="{{ url('/sim-allocate') }}" method="post">
          @csrf
          <div class="Paymentsbg ctablebg clearfix">
            <div class="row">
              <div class="col-md-12 col-sm-6">                
                  <label class="control-label" for="dealer_id">Select dealer</label>
                  <select name="dealer_id" id="dealer_id" class="form-control">  
                    @foreach($dealers as $dealer)
                    <option value="{{$dealer->id}}">{{$dealer->first_name.' '.$dealer->last_name}}</option>
                    @endforeach
                  </select>               
              </div>              
            </div>
            <div class="row">
               <div class="col-md-12 col-sm-6">        
                <br/><label class="control-label">Search Type</label><br/>  
                <label class="custom-control custom-radio">
                  <input type="radio" class="search_type" name="search_type" value="2" checked>
                  <span class="custom-control-label">SIM Range</span>
                </label> &nbsp;&nbsp;&nbsp;         
                <label>
                  <input type="radio" class="search_type" name="search_type" value="1">
                  <span class="custom-control-label">Phone Number</span>
                </label>               
              </div>
            </div>

            <div class="row chng_search_category hidden">
              <div class="col-md-5 col-sm-5">
                <div class="popTbox">
                  <i>Phone Number</i>
                  <input type="text" id="phone_number_from" name="phone_number_from">
                </div>
              </div>
              <div class="col-md-5 col-sm-5">
                <div class="popTbox">
                  <i>Phone Number To</i>
                  <input type="text" id="phone_number_to" name="phone_number_to">
                </div>
              </div>
              <div class="col-md-2 col-sm-2">
                <div class="popTbox">  
                  <i style="visibility:hidden">Find</i>                
                  <a href="#" class="greenbtn" id="phone_number_find">Find</a>
                </div>
              </div>
            </div>

            <div class="row chng_search_category">
              <div class="col-md-5 col-sm-5">
                <div class="popTbox">
                  <i>Sim Number From</i>
                  <input type="text" id="imsi_range_from" name="imsi_range_from">
                </div>
              </div>

              <div class="col-md-5 col-sm-5">
                <div class="popTbox">
                  <i>Sim Number To</i>
                  <input type="text" id="imsi_range_to" name="imsi_range_to">
                </div>
              </div>
              <div class="col-md-2 col-sm-2">
                <div class="popTbox">  
                  <i style="visibility:hidden">Find</i>                
                  <a href="#" class="greenbtn" id="imsi_range_find">Find</a>
                </div>
              </div>    
            </div> 
            <br/>
            <div class="row"><ul id="custom_option"></ul></div>         
          </div>          
            <div class="row">
              <div class="col-md-12">
                <div class="addbtnsbg">
                  <a class="cancel_btn" href="{{ url('/sim-management') }}">CANCEL</a>
                  <input name="" type="submit" class="greenbtn" value="SUBMIT">
                </div>
              </div>
            </div>                  
          </div>
        </form>
      </div>
      <div class="col-md-4">
      </div>
    </div>
  </div>
</div>


<script>

  $(document).ready(function () {    
    $(document).on("click", '#phone_number_find', function(){            
        var number_from = $('#phone_number_from').val();
        var number_to = $('#phone_number_to').val();             
        if(number_from.length == 12 && number_to.length == 12){
          $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            data: {number_from:number_from,number_to:number_to,limit:50},
            url: base_url+'/search-number-range',
            success: function(response){            
              var html = '';
              if(response.success){                
                if(response.stock.length == 0){
                  html += '<p class="form-error">Sim not available!</p>';
                } 
                $.each(response.stock, function( index, value ) {  
                  html += '<label><li><span><input type="checkbox" class="dealers_num" id="dealers_no'+ value.id+'" name="stock_id[]" value="'+ value.id+'" checked></span> '+ value.phone_number +'</li></label> &nbsp;&nbsp;';
                });   
              }else{
                  html = '<p class="form-error">'+response.message+'</p>';
              }               
              $('#custom_option').html(html);                   
            }
          });
        }
    });

    $(document).on('change','.search_type',function () {
        $('.chng_search_category').toggleClass('hidden');
        $('#custom_option').html('');
    });

    $(document).on('click','#imsi_range_find',function () {
        var imsi_from = $('#imsi_range_from').val();
        var imsi_to = $('#imsi_range_to').val();
        if(imsi_from.length >= 14 && imsi_to.length >= 14 ){
          $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            data: {imsi_from:imsi_from,imsi_to:imsi_to},
            url: base_url+'/search-imsi-number',
            success: function(response){            
              var html = '';
              if(response.success){                
                if(response.stock.length == 0){
                  html += '<p class="form-error">Sim not available!</p>';
                } 
                $.each(response.stock, function( index, value ) {  
                  html += '<label><li><span><input type="checkbox" class="dealers_num" id="dealers_no'+ value.id+'" name="stock_id[]" value="'+ value.id+'" checked></span> '+ value.phone_number +'('+ value.sim_number +')' +'</li></label> &nbsp;&nbsp;';
                });   
              }else{
                  html = '<p class="form-error">'+response.message+'</p>';
              }            
              $('#custom_option').html(html);                   
            }
          });
        } else {      
          $('#custom_option').html('<p class="form-error">Invalid IMSI Number Range</p>');
        }
    });
  });

</script>

@endsection