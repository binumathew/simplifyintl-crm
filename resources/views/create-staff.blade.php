@extends('layouts.home')
@section('content')
    <!-- page wrapper start -->
    <div class="wrapper">
        <div class="container-fluid">
            <link href="{{ asset('public/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <link href="{{ asset('public/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">{{ isset($staff)?'Create ':'Edit '}}{{ ($staff_type)?'Staff':'Dealer' }}</li>
                            </ol>
                        </div>
                        <h4 class="page-title">{{ isset($staff)?'Create ':'Edit '}}{{ ($staff_type==1)?'Staff':'Dealer' }}</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card m-b-20">
                        <div class="card-body">                            
                            <form id="save-staff">
                                @csrf
                                <div class="p-3">                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            @if(isset($staff))
                                                <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                            @endif
                                            <div class="form-group">                                                                                 
                                                <label for="first_name" class="col-form-label">First Name</label>
                                                <input id="first_name" name="first_name" type="text" class="form-control" value="{{ isset($staff)?$staff->first_name:'' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">                                                                                 
                                                <label for="last_name" class="col-form-label">Last Name</label>
                                                <input id="last_name" name="last_name" type="text" class="form-control" value="{{ isset($staff)?$staff->last_name:'' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">                                                                                 
                                                <label for="email" class="col-form-label">Email</label>
                                                <input id="email" name="email" type="email" class="form-control" value="{{ isset($staff)?$staff->email:'' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">                                                                                 
                                                <label for="phone" class="col-form-label">Phone</label>
                                                <input id="phone" name="phone" type="text" class="form-control" value="{{ isset($staff)?$staff->phone:'' }}" autocomplete="off" placeholder="eg. 078xxxx" maxlength="15">
                                            </div>
                                        </div>
                                            
                                        @if(isset($staff))
                                        <div class="col-md-6">
                                            <label for="promocode" class="col-form-label">Staff Code</label>
                                            <div class="input-group">                                            
                                                <input type="text" class="form-control text-uppercase disabled" value="{{ isset($staff)?$staff->promocode:'' }}" readonly disabled>
                                            </div>
                                            <span id="promo_status"></span>
                                        </div>
                                        @else
                                        <div class="col-md-6">
                                            <label for="promocode" class="col-form-label">Staff Code</label>
                                            <div class="input-group">                                            
                                                <input id="promocode" name="promocode" type="text" class="form-control text-uppercase" value="{{ isset($staff)?$staff->promocode:'' }}">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info waves-effect waves-light" id="check_promocode">Check</button>
                                                </div>                                                
                                            </div>
                                            <span id="promo_status"></span>
                                        </div>
                                        @endif

                                        <div class="col-md-6">
                                            <div class="form-group">                                                                                 
                                                <label for="role" class="col-form-label">Designation</label>
                                                <select name="role" id="role" class="custom-select">
                                                    <option value="">Choose</option>
                                                    @foreach($roles as $role)
                                                        @if(Auth::user()->role <= $role->id)
                                                            <option value="{{ $role->id }}" {{ (isset($staff) && $staff->role == $role->id)?'selected':'' }}>{{ $role->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">                                                                                 
                                                <label for="parent" class="col-form-label">Parent</label>
                                                <select name="parent" id="parent" class="custom-select">
                                                    <option>Choose</option>
                                                    @if(isset($parents))
                                                        @foreach($parents as $parent)
                                                            <option value="{{$parent->id}}"  {{ ($parent->id == $staff->parent_id)?'selected':'' }}>{{ $parent->full_name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">                                                                                 
                                                <label for="user_status" class="col-form-label">Status</label>
                                                <select name="status" id="user_status" class="custom-select">
                                                    <option value="1"  {{ (isset($staff) && $staff->status == 1)?'selected':'' }}>Active</option>
                                                    <option value="0"  {{ (isset($staff) && $staff->status == 0)?'selected':'' }}>In Active</option>                                                 
                                                </select>
                                            </div>
                                        </div>
                                        @php $options = (isset($staff)) ? json_decode($staff->payment_mode, true):json_decode(Auth::user()->payment_mode, true); @endphp
                                        @foreach($gateways as $gateway)
                                            @if(in_array($gateway->id,  $options) || Auth::user()->role == 1)
                                            <div class="col-md-2">
                                                <div class="form-group">                                                                                 
                                                    <label class="col-form-label">{{ $gateway->gateway }} Payment</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="payment_{{$gateway->id}}" name="payment_mode[]" value="{{$gateway->id}}" {{ in_array($gateway->id,  $options)?'checked':'' }}>
                                                        <label class="custom-control-label" for="payment_{{$gateway->id}}"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach 
                                    </div>                                                                
                                    @if(isset($staff))
                                    <div class="row">                                       
                                        <div class="col-md-6">
                                            <div class="form-group">                                                                                 
                                                <label class="col-form-label">Created On</label>
                                                <input type="text" class="form-control" value="{{ Helper::date_format($staff->created_at) }}" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">                                                                                 
                                                <label class="col-form-label">Email Verified</label>
                                                <input type="text" class="form-control" value="{{ !is_null($staff->email_verified_at)?Helper::date_format($staff->email_verified_at):'-' }}" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="row pull-right">                                        
                                        <a href="{{ ($staff_type==1)? url('staff-list'):url('dealers') }}" class="btn btn-secondary m-r-10">Cancel</a>
                                        <button type="button" class="btn btn-success waves-effect waves-light" id="create_staff">Save</button>                            
                                    </div>                                
                                </div>

                            </form>
                        </div>

                    </div>
                </div>
                <div class="col-md-6"></div>
            </div>
        </div>
        <!-- end container-fluid -->
    </div>
    <!-- page wrapper end -->

    <script type="text/javascript">
        $(document).ready(function () {
            $(document).on("click", '#check_promocode', function(){ 
                var promocode = $('#promocode').val();
                var msg = "Enter a code less than 15 length";
                if(promocode.length <= 15 && promocode != ""){
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        data: {promocode:promocode},
                        url: base_url+'/check-promocode',   
                        dataType: 'json',
                        success:function(data){
                            if(data.success){
                                $("#promocode").removeClass('border border-danger').addClass('border border-success');    
                                $("#promo_status").html(data.message).addClass('text-success');                      
                            }else{
                                $("#promocode").removeClass('border border-success').addClass('border border-danger');
                                $("#promo_status").html(data.message).removeClass('text-success').addClass('text-danger');
                            }
                        }               
                    });
                }else{
                    $("#promocode").removeClass('border border-success').addClass('border border-danger');
                    $("#promo_status").html(msg).addClass('text-danger');
                }
            });

            $(document).on('change', '#role', function(){
                var role = $(this).val();                
                if(role != ""){                    
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        data:{role:role},
                        url: base_url+'/get-parent',
                        dataType: 'json',
                        success: function(response){ 
                            var option = "<option value=''>Choose</option>";
                            $.each(response,function(k,val){
                                option += "<option value='"+val.id+"'>"+val.full_name+"</option>";                               
                            }); 
                            $("#parent").html(option);                       
                        }
                    });
                }
            });

            $(document).on('click', '#create_staff', function(){
                if($("#save-staff").valid()){
                    $(this).prop('disabled', true);
                    var formData = new FormData($('#save-staff')[0]);
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',                                                
                        url: base_url+'/save-staff',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success:function(data){
                            if(data.success){
                                location.href = base_url+data.url;
                            }else{
                                $(this).prop('disabled', false);
                                alert(data.message);
                            }                                    
                        }
                    });
                }
            });

            $("#save-staff").validate({
                errorPlacement: function(error, element) {                       
                    element.addClass('border border-danger');
                    error.insertAfter(element);
                },
                unhighlight: function(element) {
                    $(element).removeClass('error border border-danger');
                    $(element).addClass('border border-success');
                },
                rules: {
                    first_name:'required',
                    last_name:'required',
                    email: {
                        required: true, 
                        email: true
                    },
                    phone: {
                        // number: true,
                        required: true, 
                        regex:/^(((0|\+|))[0-9]{10,15})$/,
                    },
                }               
            });

            $.validator.addMethod(
                'regex',
                function(value, element, regexp) {
                    var re = new RegExp(regexp);
                    return this.optional(element) || re.test(value);
                },
                'Enter a valid phone number'
            );
        });     
    </script>
@endsection