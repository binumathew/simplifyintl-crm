
<div class="row">
    <div class="col-md-12">
        <div class="card m-b-20">
            <div class="card-body invoice">
            <h4 class="mt-0 header-title">Invoice</h4>
            <hr>
            <!-- <form id="invoice_form">
            <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="invoice_month" id="invoice_month" class="form-control custom-select required">
                                <option value="">Choose</option>
                                    @php
                                        $now       = Carbon::now();
                                        $month     = $now->startOfMonth();
                                        $currmonth = $now->startOfMonth()->format('m');
                                    @endphp
                                @for($i=1; $i<=12; $i++)
                                    @php 
                                        $month_name = $now->format('F');
                                        $monthid    = $now->format('m');
                                        $month      = $month->addMonth();
                                    @endphp
                                <option {{ ($currmonth == $monthid) ? 'selected':'' }} value="{{base64_encode($monthid)}}">{{$month_name}}</option>  
                                @endfor
                            </select>
                        </div>
                    </div> 
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="invoice_year" id="invoice_year" class="form-control custom-select required">
                                <option value="">Choose</option>
                                @php 
                                    $firstYear = (int)Carbon::now()->format('Y'); 
                                    $lastYear = $firstYear - 5;
                                @endphp
                                @for($i=$firstYear;$i >= $lastYear;$i--)
                                <option {{ ($i == $firstYear) ? 'selected':'' }} value="{{base64_encode($i)}}">{{$i}}</option>  
                                @endfor
                            </select>
                        </div>
                    </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="createinvoice">Download</button>
                </div>  
            </div>
            </form> -->
            <div class="row">
            <div class="col-md-12">
            <table id="dataTable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Phone</th>
                        <th>Sub Total</th>
                        <th>Tax</th>
                        <th>Total</th>
                        <th>Credit Applied</th>
                        <th>Amount Due</th>
                        <th>Invoice Date</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th>PDF</th>
                    </tr>
                </thead>
                <tbody>   
                    @if($invoice->isNotEmpty())
                    @foreach ($invoice as $inv)
                    <tr> 
                       <td>{{ $user->phone }}</td>
                        <td>{{ $currency.$inv->sub_total }}</td>         
                        <td>{{ $currency.$inv->tax }}</td> 
                        <td>{{ $currency.$inv->total }}</td>
                        <td>{{ $currency.$inv->credits_applied }}</td>
                        <td>{{ $currency.$inv->amount_due }}</td>
                        <td>{{ Carbon::parse($inv->date)->format('d-m-Y') }}</td>
                        <td>{{ Carbon::parse($inv->paid_at)->format('d-m-Y') }}</td>
                        <td>
                            @if($inv->status == 0)
                            <span class="badge badge-danger">Not Processed</span> 
                            @elseif($inv->status == 1) 
                                <span class="badge badge-success">Paid</span> 
                            @elseif($inv->status == 2) 
                                <span class="badge badge-warning">Partially Paid</span>
                            @elseif($inv->status == 3) 
                                <span class="badge badge-info">Unpaid</span>
                            @elseif($inv->status == 4) 
                                <span class="badge badge-primary">Cancel</span>
                            @elseif($inv->status == 5) 
                                <span class="badge badge-info">Pending</span>
                            @elseif($inv->status == 6) 
                                <span class="badge badge-warning">Refund</span>
                            @elseif($inv->status == 7)
                                <span class="badge badge-danger">Dispute</span>
                            @elseif($inv->status == 8)
                                <span class="badge badge-danger">Failed</span>
                                @if($inv->payment_requests_count > 0)
                                <br><span>Link sent count - {{$inv->payment_requests_count}}</span><br><span>link sent on - {{Carbon::parse($inv->last_payment_request_at)->format('d-m-Y')}}</span>
                                @else
                                <br><span>link sent count - {{$inv->payment_requests_count }}</span>
                                @endif
                            @endif
                        </td> 
                        <td>
                            @if($inv->status == 8)
                            <!-- <a href="javascript:void(0);"><button class="btn btn-primary btn-sm pay_link"  title="Send Payment Link" data-id="{{Crypt::encrypt($inv->id)}}">Pay Link</button></a> -->
                            @endif
                        </td>   
                        <td>
                        @php
                        $inv_link = Crypt::encrypt($inv->id);
                        @endphp
                        <a href="{{url('/generate-invoices/'.$inv_link)}}"><button class="btn btn-info btn-sm" >Download</button></a>
                        </td>                       
                    </tr>                                       
                    @endforeach  
                    @endif                               
                </tbody>
            </table>
            </div>
            </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
        $('#dataTable').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search:''}});
</script>