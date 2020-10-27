<ul class="progress-indicator nocenter stacked">
    @if (count($enquiry_history) > 0)
    @foreach ($enquiry_history as $enquiry)
    <li class="completed">
        <span class="bubble"></span>
        <span class="stacked-text">
            
            <span class="subdued">{{ date('d-M-Y H:i',strtotime($enquiry->created_at))}}</span>
            <span class="sub-info">
                <ul>
                    <li>Handled By : {{$enquiry->handled_by}}</li>
                    <li>{{$enquiry->note}}</li>
                </ul>
            </span>
        </span>
    </li>
    @endforeach
    @else
    <li class="completed">
        <span class="bubble"></span>
        <span class="stacked-text">
            No Enquiry
        </span>
    </li>
    @endif
    
</ul>
<hr>
<h3 class="content-center">Enquiry Details</h3>

<input type="hidden" name="request_id" id="enq_request_id" value="{{Crypt::encrypt($req_id)}}"> 
<div class="form-out-group">
    <label class="form-label">Enquiry Note</label>
    <input type="text" name="enquiry_note" id="enquiry_note" class="form-input">
</div>
<button type="button" id="save_enq_history" class="btn btn-success">Save</button>
