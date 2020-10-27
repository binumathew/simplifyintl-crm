<div class="row">
    <div class="col-12">
        <div class="card m-b-20">
            <div class="card-body">

                <button class="btn btn-success pull-right" id="">Update</button>
                <table id="dataTable" class="table table-striped dt-responsive nowrap table-vertical" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="d-none"></th>
                            <th>Connected On</th>
                            <th>CLI</th>
                            <th>CLD</th>    
                            <th>Duration</th>
                            <th>Cost</th>               
                            <th>Platform</th>
                        </tr>
                    </thead>
                    <tbody>   
                        @foreach ($calls as $call)
                        <tr> 
                            <td class="d-none">{{$call->id}}</td>         
                            <td class="text-justify">{{ Helper::date_format($call->connect_date) }}</td> 
                            <td>{{ $call->cli}}</td> 
                            <td>{{ $call->cld}}</td>
                            <td>{{ gmdate("H:i:s", $call->duration)}}</td>
                            <td class="text-right">{{ $currency.Helper::number_format($call->cost) }}</td>
                            <td>{{ ($call->history_from == 1)?'App':'Sim' }}</td>
                        </tr>                                       
                        @endforeach                                   
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#dataTable').DataTable({ responsive: true, bSort : true, pageLength: 25, language: { search:''}, order:[[0, 'desc']]});
</script>