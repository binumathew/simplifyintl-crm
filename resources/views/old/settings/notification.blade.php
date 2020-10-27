@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Notifications</h1>
					</div>   
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-12">
                            <table id="notification-log-table" class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Action</th>
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
        var notificationTable = $('#notification-log-table').DataTable({
        processing: true,
        serverSide: true,
        // responsive: true,
        searching: false,
        "bSort" : false,
        autoWidth: false,
        ajax: {
            url: 'list-notification-log',
            data: function (d) {
            }
        },
        columns: [
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'phone', name: 'phone'},
            {data: 'message', name: 'message'},
            {data: 'status', name: 'status'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action'}
        ],
        columnDefs: [
           { width: '100px', targets: 2 } 
        ]
    });

    $(document).on("click", '.update_ee_renew', function(){
        var $this = $(this);
        var not_id = $this.data('not_id');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            data: {not_id:not_id},
            url: base_url+'/notification-manage',    
            dataType: 'json',
            success:function(data){
                $this.hide();
            }               
        });
    });

});
</script>
@endsection