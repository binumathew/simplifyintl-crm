@if((isset($category)))
<form class="add_settings_form mt-0-fix" id="add_settings_form" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Name</label>
                <div class="input-group">
                    <input type="text" class="form-control " placeholder="Name" name="option_name" maxlength="100" value="">
                </div>
                <span></span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Value</label>
                <div class="input-group">
                    <input type="text" class="form-control " placeholder="Value" name="option_value" value="">
                </div>
                <span></span>
            </div>
        </div> 
        <div class="col-md-6">
            <div class="form-group">
                <label>Category</label>
                <div class="input-group">
                    <select name="option_category" id="option_category" class="form-control" aria-invalid="false">
                    <option value="" disabled="" selected="">Select Category</option>
                    @foreach ($category as $list)
                    <option  value="{{$list}}">{{$list}}</option>
                    @endforeach
                </select>
                </div>
                <span></span>
            </div>
        </div>   
    </div>  
    <div class="row">
        <div class="col-md-12 text-right">

            <button class="btn btn-secondary" type="button" id="addsettcancelBtn">Cancel</button>

            <button class="btn btn-success waves-effect waves-light" type="button" id="addsettBtn">Save</button>
        </div>
    </div>
</form>
@endif