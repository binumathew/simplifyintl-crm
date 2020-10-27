<div class="row">
    <div class="col-12">
        <div class="card m-b-20">
            <div class="card-body">
                <!-- <div style="width: 100%; float: left; overflow: scroll;"> -->
                    <table id="dataTable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="d-none"></th>
                                <th>Transaction ID</th> 
                                <th>Description</th>                              
                                <th>Amount</th>
                                <th>Card Detail</th>
                                <th>Gateway</th>
                                <th>Payment On</th>
                                <th>Status</th>
                                @if(Helper::has_permission('payment_history','edit'))
                                <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>   
                            @foreach ($payments as $payment)
                            <tr> 
                                <td class="d-none">{{ $payment->id }}</td>         
                                <td>{{ $payment->transaction_id }}</td> 
                                <td class="text-wrap">{{ $payment->description }}</td>                            
                                <td class="text-right">{{ $currency.Helper::number_format($payment->total_amount) }}</td>
                                <td>{{ $payment->card_type }}</td>
                                <td>{{ $payment->payment_method }}</td>
                                <td class="text-justify">{{ Helper::date_format($payment->created_at) }}</td>
                                <td>
                                	@if($payment->status == 1) 
                                		<span class="badge badge-success">Success</span> 
                                	@elseif($payment->status == 2) 
                                		<span class="badge badge-warning">Success/Error</span>
                                    @elseif($payment->status == 3) 
                                        <span class="badge badge-info">Refund</span>
                                    @elseif($payment->status == 4) 
                                        <span class="badge badge-primary">Deduct</span>
                                    @else
                                        <span class="badge badge-danger">Failed</span>
                                	@endif
                                </td> 
                                @if(Helper::has_permission('payment_history','edit'))
                                    <td>
                                    @if($payment->status == 1)
                                        <a data-toggle="tooltip" href="javascript:void(0);" data-original-title="Refund" class="action_refund text-danger" data-id="{{Crypt::encrypt($payment->id)}}" data-amount="{{$payment->total_amount}}" data-currency="{{$payment->currency}}"><i class="mdi mdi-undo-variant mdi-24px"></i></a>
                                    @endif
                                    </td>
                                @endif                          
                            </tr>                                       
                            @endforeach                                 
                        </tbody>
                    </table>
                <!-- </div> -->
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#dataTable').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search:''}, order:[[0, 'desc']]});
</script>