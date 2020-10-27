@extends('layouts.home')
@section('content')
  
  <div class="Cwrapper">
      <div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
                <div class="Cleftpart">
                  <h1>Contacts</h1>
                  <!--<span>Home  |  My Avoo  | Contacts </span> -->
                </div>
                <div class="Unavbg">
                  <div class="row">
                    <div class="alert-status"> 
                      @if(session()->has('message'))
                      <div class="alert alert-success" id="success">
                          {{ session()->get('message') }}
                      </div>
                      @endif
                      @if(Session()->has('error'))
                      <div class="alert alert-danger">
                        {{ Session()->get('error') }}
                      </div>
                      @endif
                    </div>
                    <div class="col-md-8 col-sm-7">
                      <ul class="Ulinenav clearfix">
                        <li class="active"><a href="">Contacts</a></li>
                        <!--<li><a href="#">Contact Groups</a></li>-->
                      </ul>
                    </div>
                    <div class="col-md-4 col-sm-5">
                        <ul class="EAbtnbg">
                          <li class="expo">
                            <a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a>
                          </li>
                          <li><a href="addcontact">ADD&nbsp;CONTACTS</a></li>
                        </ul>
                    </div>
                  </div>
              </div>   
              {{ csrf_field() }}
              <div class="Paymentsbg ctablebg clearfix">
                <div class="row">
                    <div class="col-md-12">
                        <table id="contacttable" class="display responsive no-wrap" cellspacing="0" width="100%">
                          <thead>
                              <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Created Date</th>
                                <th>Phone&nbsp;No</th>
                                <!-- <th>Status</th> -->
                                <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                            <?php
                            $i=1;
                            foreach ($contacts as $key => $value): ?>
                              <tr id="{{$i}}">
                                  <td>{{$value->first_name}}</td>
                                  <td>{{$value->last_name}}</td>
                                  <td>{{$value->email}}</td>
                                  <td>
                                  <?php 
                                    if (isset($_COOKIE["name"])){
                                        $tzone = $_COOKIE["name"]; 
                                    } else {
                                      $tzone = "UTC";  
                                    }
                                    $timestamp = $value->created_at;
                                    $dt = new DateTime($timestamp, new DateTimeZone('UTC'));
                                    $dt->setTimezone(new DateTimeZone($tzone));
                                    $dateString = $dt->format('d M,Y, h:i A');
                                    echo $dateString; 
                                  ?>  
                                  </td>
                                  <?php
                                  if($value->php_add_number==""){
                                    $explode = explode(',',$value->phn_add_number); 
                                  ?>                                
                                  <td>{{str_replace('-', '', $value->phone_number)}}</td>
                                  <td>
                                    @php
                                        $parameter= Crypt::encrypt($value->id);
                                    @endphp
                                    <a list-id={{$i}} id="call_image{{$i}}" href="#" style="display:none" class="callcut">
                                      <i class="fa fa-phone" style="color: red;" aria-hidden="true"></i>
                                  </a>
                                    <?php if($main_balance>0.5||$balance_minutes>0) { ?>
                                        <span id="call{{$i}}" >
                                          <a data-toggle="tooltip" title="Call" list-id={{$i}} href="#" user-id="{{$value->phone_number}}" class="call"><i class="fa fa-phone" style="font-size:20px" alt="connect"></i></a>
                                        </span>
                                      <?php } else { ?>
                                        <span id="call{{$i}}" >
                                          <a data-toggle="tooltip" title="Low Balance"  href="#"  class="">
                                            <i class="fa fa-phone" style="font-size:20px" alt="connect"></i>
                                          </a>
                                        </span>                                     
                                    <?php } ?>
                                    <a data-toggle="tooltip" title="Edit"  href="editcontact/{{$parameter}}" class="fa fa-pencil-square-o editbtn"></a>
                                    <a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_contact" list-id={{$i}} user-id="{{$parameter}}" ></a>
                                    <input type="hidden" id="test" value="">
                                  </td>
                                <?php } if(count($explode)>1) { ?>
                              </tr>
                              <?php 
                                foreach($explode AS $explode_content){
                                  if($explode_content!=""){
                                ?>
                                <tr>
                                  <td>{{$value->first_name}}</td>
                                  <td>{{$value->last_name}}</td>
                                  <td>{{$value->email}}</td>
                                  <td>
                                    <?php   
                                    $timestamp = $value->created_at;
                                    $dt = new DateTime($timestamp, new DateTimeZone('UTC'));
                                    $dt->setTimezone(new DateTimeZone($tzone));
                                    $dateString = $dt->format('d M,Y, h:i A');
                                    // echo date('d M,Y, H:i A',strtotime($dateString));
                                    echo $dateString; 
                                    ?>                                   
                                  </td>                                     
                                  <td>{{str_replace('-', '', $explode_content)}}</td>
                                  <!--  <td>{{$value->status}}</td> -->
                                  <td>
                                    @php
                                      $parameter= Crypt::encrypt($value->id);
                                    @endphp
                                    <a data-toggle="tooltip" title="Disconnect" list-id={{$i}} id="call_image{{$i}}" href="#" style="display:none" class="callcut">
                                        <i class="fa fa-phone" style="color: red;" aria-hidden="true"></i>
                                      </a>
                                      <span id="call{{$i}}" >
                                        <a data-toggle="tooltip" title="Disconnect" list-id={{$i}} href="#" user-id="{{$explode_content}}" class="call">
                                            <i class="fa fa-phone" style="font-size:20px" alt="connect"></i>
                                          </a>
                                      </span>
                                      <a data-toggle="tooltip" title="Edit" href="editcontact/{{$parameter}}" class="fa fa-pencil-square-o editbtn"></a>
                                      <a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_contact" list-id={{$i}} user-id="{{$parameter}}" ></a>
                                      <input type="hidden" id="test" value="">
                                    </td>
                                </tr>
                                <?php 
                                  $i++; 
                                  }
                                }
                              ?>                            
                              <?php } ?>
                          </tr>
                          <?php 
                            $i++; 
                            endforeach ?>
                        </tbody>
                      </table>
                  </div>
                </div>
            </div>
          </div>
      </div>
    </div>
    <div class="modal hide" id="addBookDialog">
      <div class="modal-header">
          <button class="close" data-dismiss="modal">×</button>
          <h3>Modal header</h3>
      </div>
      <div class="modal-body">
          <p>some content</p>
          <input type="text" name="bookId" id="bookId" value=""/>
      </div>
    </div>
  </div>

  <script src="//cdnjs.cloudflare.com/ajax/libs/jstimezonedetect/1.0.4/jstz.min.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {      
        $('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
        $('.dataTables_filter input').attr("placeholder", "Search");
  
        $(document).on('click','.delete_contact',function(){
          var id = $(this).attr('user-id');
          //var _token = $('#token').val();
          var list_id = $(this).attr('list-id')
          
          if(confirm("Do you really want to delete this contact ?")){
            $.ajax({
              type:"POST",
              url:'deletecontact',
              headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
              data:{id:id},
              success:function(){
               $('#'+list_id).fadeOut(1000);
             }
           })
          }
        });

      $(document).on('click','.call',function(e){
          e.preventDefault(); 
          var id = $(this).attr('user-id');
          var list_id = $(this).attr('list-id');
          var token = $('input[name=_token]').val();
          $.ajax({
              method:"POST",
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url:'make-call',
            data:{id:id},
            dataType:'json',
            success:function(data){
              if(data.status == 'success'){ 
                $("#test").val(data.call_id);
                $('#call'+list_id).hide();
                $('#call_image'+list_id).show();
              }else{
                $('.alert-status').html('<div class="alert alert-danger">'+ data.message +'</div>');
                setTimeout(function(){
                  $('.alert').fadeOut('fast');
                }, 5000);
              }
            } 
          }); 
      });

       $(document).on('click','.callcut',function(e){
          e.preventDefault(); 
          var id = $("#test").val();          
          var list_id = $(this).attr('list-id');
          var token = $('input[name=_token]').val();
          $.ajax({
            method:"POST",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url:'cancel-call',
            data:{id:id},
            dataType:'json',
            success:function(data){ 
              if(data.status == 'success'){       
                $('#call'+list_id).show();
                $('#call_image'+list_id).hide();
              }else{
                $('.alert-status').html('<div class="alert alert-danger">'+ data.message +'</div>');
                setTimeout(function(){
                  $('.alert').fadeOut('fast');
                }, 5000);
              }
            }
          }); 
      });

        $(document).on("click", ".open-AddBookDialog", function () {
          var myBookId = $(this).data('id');
          $(".modal-body #bookId").val( myBookId );         
             // As pointed out in comments, 
             // it is superfluous to have to manually call the modal.
             // $('#addBookDialog').modal('show');
        });
        
      $('[data-toggle="tooltip"]').tooltip();   
      
      setTimeout(function() {
          $('#success').fadeOut('fast');
        }, 5000);
    });

    $(document).ready(function(){
        var tz = jstz.determine(); // Determines the time zone of the browser client
        var tempo = tz.name(); //'Asia/Kolhata' for Indian Time.
        var name = 'name',
        ATS_getExpire = function() { return 'ATS_getExpire'; };
        var curCookie = name + "=" + tempo;
        document.cookie = curCookie;
      });
  </script>
@endsection