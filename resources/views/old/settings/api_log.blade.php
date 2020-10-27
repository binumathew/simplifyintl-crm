@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>API Log</h1>
					</div>   
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-12">
                            <table id="api-log-table" class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Path</th>
                                        <th>Method</th>
                                        <th>Execution</th>
                                        <th>Created At</th>
                                        <th>Request</th>
                                        <th>Response</th>                                        
                                    </tr>
                                </thead>
                            </table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div> 
	</div>
	<script type="text/javascript">
	$(document).ready(function(){
        var notificationTable = $('#api-log-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: true,
        "bSort" : false,
        autoWidth: false,
        ajax: {
            url: 'list-api-log',
            data: function (d) {
            }
        },
        columns: [
            {data: 'name', name: 'usr.name'},
            {data: 'path', name: 'al.path'},
            {data: 'method', name: 'al.method'},
            {data: 'exec_time', name: 'al.exec_time'},
            {data: 'created_at', name: 'al.created_at'},
            {data: 'request', name: 'al.request'},
            {data: 'response', name: 'al.response'},            
        ],
        columnDefs: [
           { width: '100px', targets: 2 } 
        ]
    });

});
</script>
@endsection