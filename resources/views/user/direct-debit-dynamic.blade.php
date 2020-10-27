@if(isset($step) && $step == 0)
<input type="hidden" name="user_id" value="{{ Crypt::encrypt($user->id)}}">
<input type="hidden" name="customer_edit" value="{{ $user->userDetail->gocardless_customer }}">
<div class="row card-body">
    <div class="col-md-4">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" class="form-control" name="first_name"  placeholder="First name" required maxlength="25" value="{{ $user->first_name}}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control" placeholder="Last name" required maxlength="25" value="{{ $user->last_name}}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Email</label>
            <input type="text" name="email" class="form-control" placeholder="Email" maxlength="50" value="{{ $user->email }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" class="form-control" placeholder="Address" maxlength="50" value="{{ $user->userDetail->address }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>City</label>
            <input type="text" name="city" class="form-control" placeholder="City" maxlength="25" value="{{ $user->userDetail->city}}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Postal Code</label>
            <input type="text" name="postal_code" class="form-control" placeholder="Postal Code" maxlength="15" value="{{ $user->userDetail->postal_code}}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Country</label>
             <select name="country_id" id="country_id" class="form-control custom-select identifier">
            <option value="">Choose</option>
            @foreach (Helper::getCountry() as $country)
           	@php 
           	    $selected = '';
           	 	if($user->country_id == $country->id){
           	 		$selected = 'selected';
           		}
           	@endphp
            <option value="{{$country->id}}" {{ $selected }}> {{$country->country_name }}</option>
            @endforeach
        </select>
        </div>
    </div>                           
</div>
@endif
@if(isset($step) && $step == 1)
<input type="hidden" name="user_id" value="{{ Crypt::encrypt($user->id)}}">
@if(isset($existbank) && $existbank->isNotEmpty())
<div class="row m-b-20">
    @foreach($existbank as $key=> $bank)
    <div class="col-md-4">
        <div class="card-body">
            <label for="card_1{{$bank->id}}0">
            <input type="radio" name="bank_list" class="radio_bank_list" id="card_1{{$bank->id}}0" value="{{Crypt::encrypt($bank->id)}}" {{($key == 0)?'checked':''}}> {{ ($bank->account_no != '') ? 'Account No  '.str_repeat("*", strlen($bank->account_no) -4) . substr($bank->account_no, -4) : 'Iban  '.str_repeat("*", strlen($bank->iban)-4) . substr($bank->iban, -4)}}
            </label>
        </div>
    </div>
    @endforeach                                        
</div>
@endif
<div class="row card-body">
    <div class="col-md-4">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" class="form-control" name="first_name"  placeholder="First name" required maxlength="25" value="{{ $user->first_name}}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control" placeholder="Last name" required maxlength="25" value="{{ $user->last_name}}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Account No.</label>
            <input type="text" name="account_no" class="form-control" placeholder="Account no" maxlength="20" value="{{ (isset($existbank) && $existbank->isNotEmpty()) ? $existbank[0]->account_no : ''}}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Branch Code</label>
            <input type="text" name="branch_code" class="form-control" placeholder="Branch code" maxlength="12" value="{{ (isset($existbank) && $existbank->isNotEmpty()) ? $existbank[0]->branch_code : ''}}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Iban</label>
            <input type="text" name="iban" class="form-control" placeholder="iban" maxlength="35" value="{{ (isset($existbank) && $existbank->isNotEmpty()) ? $existbank[0]->iban : ''}}">
        </div>
    </div>                            
</div>
@endif
@if(isset($step) && $step == 2)
<input type="hidden" name="user_id" value="{{ Crypt::encrypt($user->id)}}">
<input type="hidden" name="bank_id" value="{{ Crypt::encrypt($user_bank->id)}}">
<div class="row card-body">
    <h6>Create Direct Debit Setup</h6>
    <table class="table table-hover table-bordered">
      <tbody>
        <tr><td>Name</td><td>{{ $user_bank->first_name.' '.$user_bank->last_name}}</td></tr>
        @if($user_bank->account_no)
        <tr><td>Account No</td><td>{{ $user_bank->account_no }}</td></tr>
        @endif
        @if($user_bank->branch_code)
        <tr><td>Branch Code</td><td>{{ $user_bank->branch_code }}</td></tr>
        @endif
        @if($user_bank->iban)
        <tr><td>iban</td><td>{{ $user_bank->iban}}</td></tr>
        @endif
        <tr><td>Country</td><td>{{ $user->country->country_name }}</td></tr>
      </tbody>
  </table>
</div>                           
@endif
@if(isset($mandate))
<div class="row">
    <div class="col-md-12">
        <div class="card m-b-20">
            <div class="card-body">
                @if($success)
                <div class="row m-b-20">
                    <div class="col-md-12"><div class="alert alert-success text-center" role="alert">
                        <h3><strong><i class="mdi mdi-check-circle"></i></strong> Direct Debit set up successfully!</h3>
                        <p>GoCardless Ltd will appear on your bank statement when payments are taken against this Direct Debit.</p>
                    </div></div>
                    <div class="col-md-12 text-center"><a href="{{ url('/users') }}"><button type="button" class="btn btn-primary text-center">Continue</button></a></div>
                </div>
                @else
                <div class="row m-b-20">
                    <div class="col-md-12 text-center"><div class="alert alert-danger text-center" role="alert">
                        <h3><strong><i class="mdi mdi-close-circle"></i></strong> Direct Debit set up Failed!</h3>
                        <p>Please contact our customer support</p>
                    </div></div>
                    <div class="col-md-12"><a href="{{ url('/users') }}"><button type="button" class="btn btn-primary text-center">Continue</button></a></div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div> 
@endif