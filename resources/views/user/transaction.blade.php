<div class="row">
    <div class="col-12">
        <div class="card m-b-20">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                    @if($invoice->isNotEmpty()) 
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#invoice_details" role="tab">
                            <span class="d-none d-md-block">Invoice Details</span>
                            <span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                        </a>
                    </li> 
                    @endif                 
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#txn_details" role="tab">
                            <span class="d-none d-md-block">Transaction Details</span>
                            <span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane  p-3" id="invoice_details" role="tabpanel">
                    <table id="dataTable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Tax</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>   
                           @if($invoice->isNotEmpty())
                            @foreach ($invoice as $inv)
                            <tr> 
                                <td>{{ $currency.$inv->sub_total }}</td>         
                                <td>{{ $currency.$inv->tax }}</td> 
                                <td>{{ $currency.$inv->total }}</td>
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
                                	@endif
                                </td> 
                                <td>{{ Carbon::parse($inv->paid_at)->format('d-m-Y') }}</td>                       
                            </tr>                                       
                            @endforeach  
                            @endif                               
                        </tbody>
                    </table>
                    @if($invoice->isNotEmpty())
                    <br>
                    <table class="dataTable table table-hover table-bordered">
                        <tbody>
                        <tr>
                            <td>Total Amount</tc>
                            <td>{{$currency.$invoice->sum('total')}}</td>
                        </tr>
                        <tr>
                            <td>Total Paid</tc>
                            <td>{{$currency.$invoice->where('status',1)->sum('total')}}</td>
                        </tr>
                        <tr>
                            <td>Current Owing</tc>
                            <td>{{$currency.$invoice->where('status','<>',1)->sum('total')}}</td>
                        </tr>
                        </tbody>
                    </table>
                    @endif
                    </div>
                    <div class="tab-pane active p-3" id="txn_details" role="tabpanel">
                    <table id="dataTable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="d-none"></th>
                                <th>Transaction ID</th> 
                                <th>Amount</th>
                                <th>Gateway</th>
                                <th>Payment On</th>
                                <th>Status</th>
                                @if(Helper::has_permission('payment_history','edit'))
                                <!-- <th>Action</th> -->
                                @endif
                            </tr>
                        </thead>
                        <tbody>   
                            @foreach ($payments as $payment)
                            @php
                             $pay_method = $payment->payment_method; 
                             if($pay_method == 'Cash'){
                                 $pay_method = ' Cash Payment. Amount will be deducted from next billing.';
                             }elseif($pay_method == 'DD'){
                                $pay_method = 'Stripe Website Payment';
                             }
                             $desc = json_decode($payment->description);
                            @endphp
                            <tr> 
                                <td class="d-none">{{ $payment->id }}</td>         
                                <td>{{ $payment->transaction_id }}</td> 
                                <td class="text-right">{{ $currency.Helper::number_format($payment->total_amount) }}</td>
                                <td>{{ $pay_method }}</td>
                                <td class="text-justify">{{ Helper::date_format($payment->created_at) }}</td>
                                <td>
                                    @if($payment->status == 0) 
                                        <span class="badge badge-info">Draft</span> 
                                	@elseif($payment->status == 1) 
                                		<span class="badge badge-success">Success</span> 
                                	@elseif($payment->status == 2) 
                                		<span class="badge badge-warning">Success/Error</span>
                                    @elseif($payment->status == 3) 
                                        <span class="badge badge-info">Refund</span>
                                    @elseif($payment->status == 4) 
                                        <span class="badge badge-primary">Deduct</span>
                                    @elseif($payment->status == 5) 
                                        <span class="badge badge-info">Pending</span>
                                    @elseif($payment->status == 6) 
                                        <span class="badge badge-warning">Failed/Retried</span>
                                    @else
                                        <span class="badge badge-danger">Failed</span>
                                	@endif
                                </td> 
                                @if(Helper::has_permission('payment_history','edit'))
                                    <!-- <td>
                                    @if($payment->status == 1)
                                        <a data-toggle="tooltip" href="javascript:void(0);" data-original-title="Refund" class="action_refund text-danger" data-id="{{Crypt::encrypt($payment->id)}}" data-amount="{{$payment->total_amount}}" data-currency="{{$payment->currency}}"><i class="mdi mdi-undo-variant mdi-24px"></i></a>
                                    @endif
                                    </td> -->
                                @endif                          
                            </tr>                                       
                            @endforeach                                 
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