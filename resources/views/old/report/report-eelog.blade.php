@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>EE Log</h1>
					</div>  
					@csrf
					<div class="Paymentsbg ctablebg clearfix">
                    <div class="panel panel-default">
					    <div class="panel-heading">
					        <h3 class="panel-title">Filter</h3>
					    </div>
					    <div class="panel-body">
					        <form method="POST" id="eelog-search-form" class="form-inline" role="form">

					            <div class="form-group">
					                <label for="category">Category</label>
					                <input type="text" class="form-control" name="category" id="category" placeholder="search category" autocomplete="off">
					            </div>
					            <div class="form-group">
					                <label for="name">Name</label>
					                <input type="text" class="form-control" name="name" id="name" placeholder="search name" autocomplete="off">
					            </div>

					            <button type="submit" class="btn btn-primary">Search</button>
					        </form>
					    </div>
					</div>
						<div class="row">
							<div class="col-md-12">
                            <table id="eelog-table" class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>Msisdn</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Category</th>
                                        <th>Value</th>
                                        <th>Reference</th>
                                        <th>Created At</th>
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
        var eelogTable = $('#eelog-table').DataTable({
        // dom: "<'row'<'col-xs-12'<'col-xs-6'l><'col-xs-6'p>>r>"+
        //     "<'row'<'col-xs-12't>>"+
        //     "<'row'<'col-xs-12'<'col-xs-6'i><'col-xs-6'p>>>",
        processing: true,
        serverSide: true,
        responsive: true,
        searching: false,
        "bSort" : false,
        dom :'Blfrtip',
        ajax: {
            url: 'list-eelog',
            data: function (d) {
                d.category  = $('input[name=category]').val();
                d.name 		= $('input[name=name]').val();
            }
        },
        columns: [
            {data: 'msisdn', name: 'msisdn'},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'category', name: 'category'},
            {data: 'value', name: 'value'},
            {data: 'reference_id', name: 'reference'},
            {data: 'created_at', name: 'created_at'}
        ],
        buttons: [
        	//{
		//	extend: 'excel',
		//	text: 'Export',
		//	className: 'btn-primary',
		//	exportOptions: {
		//		orthogonal: null
		//	}
		//	},
		],
    });

    $('#eelog-search-form').on('submit', function(e) {
        eelogTable.draw();
        e.preventDefault();
    });

	});
</script>
@endsection