<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Partner
			<small>Request List</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Partner</a></li>
			<li class="active">Request List</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Request List</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th></th>
										<th>Nama</th>
										<th>Email</th>
										<th>Telepon</th>
										<th>Kepemilikan</th>
										<th>Nama Perusahaan</th>
										<th>Area</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
								<tfoot>
									<tr>
										<th>ID</th>
										<th></th>
										<th>Nama</th>
										<th>Email</th>
										<th>Telepon</th>
										<th>Kepemilikan</th>
										<th>Nama Perusahaan</th>
										<th>Area</th>
										<th></th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
					<!-- /.box-body -->
				</div>
				<!-- /.box -->
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->
	</section>
    <!-- /.content -->
	</div>
	<!-- /.content-wrapper -->
	<script>
		$(document).ready(function(){
			$('#list').DataTable({
				"language": {
					"url": base_url+"data/default/datatables.indonesian.json"
				},
				"paging": true,
				'processing': true,
				'serverSide': true,
				'order' : [],
				"ajax": {
					method : 'GET',
					url : base_url+"admin/partner/get_list_register_request"
				},
				'columns': [
				{ data: 'id' },
				{ data: 'img_profile' },
				{ data: 'fullname' },
				{ data: 'email' },
				{ data: 'phone' },
				{ data: 'ownership' },
				{ data: 'company_name' },
				{ data: 'regencies' },
				{ data: null },
				],
				"columnDefs": [
				{ 
					targets: [0,1,2,3,4,5,6,7,8], //first column / numbering column
					orderable: false, //set not orderable
				},
				{
					render : function ( data, type, row ) {
						if(data)
						return '<img width="72px" src="'+base_url+'data/partners/profile/'+data +'"/>';
						else
						return "<img width='120' src='"+base_url+"data/default/no_image.png'></img>";
					},
					targets: 1
				},
				{
					render : function ( data, type, row ) {
						return "<a href='"+base_url+"admin/partner/request_detail/"+row['id']+"' class='btn btn-primary'>Detail</a>";
					},
					targets: 8
				},
				//{ "visible": false,  "targets": [ 3 ] }
				]
			});
		});
	</script>	