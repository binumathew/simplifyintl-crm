<div class="row">
    <div class="col-12">
        <div class="card m-b-20">
            <div class="card-body">
                <table id="dataTable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Period</th>
                            <th>Plan Name</th>
                            <th>Data Used</th>
                            <th>Call Used</th>
                            <th>SMS</th>
                            <th>Out of Bundle Charges</th>
                            <th>Status</th>   
                            <th>Activated On</th>                            
                        </tr>
                    </thead>
                    <tbody> 
                        @php $i = 1; $checked = ''; @endphp
                        @if($plans)
                        @foreach ($plans as $plan)                        
                        <tr class="odd">
                            <td>{{ $i++ }}</td>
                            <td>{{ ($plan->prorata)?Helper::date_format($plan->created_at, 'F, y'):Carbon::parse($plan->created_at)->addday()->format('F, y') }}</td>
                            <td>{{ $plan->plan->plan_name }}</td>
                            <td>{{ round($plan->data_usage / pow(1024, 3),2) }} GB</td>
                            <td>{{ Helper::secondsToTime($plan->call_usage) }}</td>
                            <td>{{ $plan->sms_count }}</td>
                            <td class="text-right">{{ $currency.Helper::number_format($plan->service_total) }}</td>
                            <td>
                                @if($plan->status == 1) 
                                    <span class="badge badge-success">Active</span> 
                                @elseif($plan->status == 0) 
                                    <span class="badge badge-warning">Inactive</span>
                                @elseif($plan->status == 2) 
                                    <span class="badge badge-danger">Failed</span>                                
                                @endif
                            </td>
                            <td class="text-justify">{{ Helper::date_format($plan->created_at) }}</td>                            
                        </tr>                                       
                        @endforeach
                        @endif 
                                                       
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#dataTable').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search:''}});
</script>