<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Marketing
			<small>List Marketing</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Marketing</a></li>
			<li class="active">List Marketing</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">List Marketing</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<?php echo $message;?>
						<div class="form-group">
							<a href="<?php echo base_url().'admin/agent/add';?>"><button id="add-agent" class="btn btn-sm btn-primary">Tambah Marketing</button></a>
						</div>
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th></th>
										<th>Nama</th>
										<th>Email</th>
										<th>Telepon</th>
										<th>Saldo</th>
										<th>Jumlah Mitra</th>
										<th>Area</th>
										<th>Status</th>
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
										<th>Saldo</th>
										<th>Jumlah Mitra</th>
										<th>Area</th>
										<th>Status</th>
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
<div class="modal fade" id="status-modal">
	<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">Ganti Status Marketing</h4>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<label>Status</label>
				<select id="status_select" class="form-control" value="1">
					<?php 
						foreach($list_active_status as $val)
						{ ?>
						<option <?= ($val->id == 1)?"selected":""?> value="<?php echo $val->id;?>"><?php echo $val->name;?></option>
						<?php
						}
					?>
				</select>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
			<button id="change_status" class="btn btn-danger">Ganti</a>
		</div>
	</div>
	<!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>

<div class="modal fade" id="delete-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Hapus Marketing</h4>
			</div>
			<div class="modal-body">
				Apakah yakin ingin menghapus Marketing ini?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
				<button id="delete_partner" class="btn btn-danger">Hapus</a>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>

<script>
	var table;
	var current_id;
	$(document).ready(function(){
		table = $('#list').DataTable({
			"language": {
                "url": base_url+"data/default/datatables.indonesian.json"
			},
			"paging": true,
			'processing': true,
			'serverSide': true,
			"order": [],
			"ajax": {
				method : 'GET',
				url : base_url+"admin/agent/get_list"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'img_profile' },
			{ data: 'fullname' },
			{ data: 'email' },
			{ data: 'phone' },
			{ data: 'balance' },
			{ data: 'partner_total' },
			{ data: 'regencies' },
			{ data: 'status_name' },
			{ data: null },
			],
			"columnDefs": [
			{ 
				targets: [0,1,2,3,4,5,6,7,8,9], //first column / numbering column
				orderable: false, //set not orderable
			},
			{
				render : function ( data, type, row ) {
					if(data)
					return '<img width="120px" src="'+base_url+'data/agents/profile/'+data +'"/>';
					else
					return "<img width='120' src='"+base_url+"data/default/no_image.png'></img>";
				},
				targets: 1
			},
			{
				render : function ( data, type, row ) {
					return 	"<a href='"+base_url+"admin/agent/edit/"+row['id']+"' class='btn btn-success btn-sm'>Ubah</a>"+"<a href='#' class='btn btn-primary btn-sm' data-target='#status-modal' data-toggle='modal' data-id="+row['id']+" data-status="+row['active']+">Change Status</a>"+
					"<a href='#' class='btn btn-danger btn-sm' data-target='#delete-modal' data-toggle='modal' data-id="+row['id']+">Hapus</a>";
				},
				targets: 9
			},
			//{ "visible": false,  "targets": [ 3 ] }
			]
		});
		
		// change status 
		$('#status-modal').on('show.bs.modal', function (event) {
			current_id = $(event.relatedTarget).data('id');
			var current_status = $(event.relatedTarget).data('status');
			$('#status_select').val(current_status);
		});
		
		$('#change_status').click(function() {
			var status_selected = $('#status_select').val();
			$.ajax({
				method: 'post',
				url: base_url+"admin/agent/change_status/"+current_id,
				cache: false,
				data :{
					status_id : status_selected
				},
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						alert( data.message );
						table.ajax.reload();
						$('#status-modal').modal('hide');
					}else
					{
						alert( data.message );
					}
				}
			});
		});
		
		$('#delete-modal').on('show.bs.modal', function (event) {
			current_id = $(event.relatedTarget).data('id');
		});
		
		$('#delete_partner').click(function() {
			$.ajax({
				method: 'post',
				url: base_url+"admin/agent/delete/"+current_id,
				cache: false,
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						alert( data.message );
						table.ajax.reload();
						$('#delete-modal').modal('hide');
					}else
					{
						alert( data.message );
					}
				}
			});
		});
		
		
	});
</script>