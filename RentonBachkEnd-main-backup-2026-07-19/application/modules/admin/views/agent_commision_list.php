<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Marketing
			<small>Komisi</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Marketing</a></li>
			<li class="active">Komisi</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">List Komisi</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<?php echo $message;?>
						<div class="form-group">
							<button id="add" class="btn btn-sm btn-primary">Tambah Komisi</button>
						</div>
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th>Judul</th>
										<th>Deskripsi</th>
										<th>Minimal Target</th>
										<th>Maksimal Target</th>
										<th>Persentase</th>
										<th>Tanggal Dibuat</th>
										<th>Tanggal Diubah</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
								<tfoot>
									<tr>
										<th>ID</th>
										<th>Judul</th>
										<th>Deskripsi</th>
										<th>Minimal Target</th>
										<th>Maksimal Target</th>
										<th>Persentase</th>
										<th>Tanggal Dibuat</th>
										<th>Tanggal Diubah</th>
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

<div class="modal fade" id="add-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
				<h4 id="input_title" class="modal-title">Tambah Komisi</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<form id="input" role="form">
						<div class="box-body">
							<div class="form-group">
								<label>Judul</label>
								<input type="text" class="form-control" id="title" name="title" placeholder="Judul"/>
							</div>
							<div class="form-group">
								<label>Deskripsi</label>
								<textarea class="form-control" id="description" name="description" placeholder="Deskripsi"></textarea>
							</div>
							<div class="form-group">
								<label>Minimal Target</label>
								<input type="number" pattern="[0-9]*" class="form-control" name="min_target" id="min_target" placeholder="ex: 10">
							</div>
							<div class="form-group">
								<label>Maksimal Target</label>
								<input type="number" pattern="[0-9]*" class="form-control" name="max_target" id="max_target" placeholder="ex: 20 (lebih besar dari minimal target)">
							</div>
							<div class="form-group">
								<label>Persentase Komisi</label>
								<input type="number" pattern="^\d+(?:\.\d{1,2})?$" step="0.1" class="form-control" name="percentage" id="percentage" placeholder="ex: 7.5">
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
				<button id="post" class="btn btn-success">Simpan</a>
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
				<h4 class="modal-title">Hapus Komisi</h4>
			</div>
			<div class="modal-body">
				Apakah yakin ingin menghapus Komisi ini?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
				<button id="delete_commision" class="btn btn-danger">Hapus</a>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>

<script>
	var table;
	var current_id;
	var is_edit = false;
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
				url : base_url+"admin/agent/get_list_commision"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'title' },
			{ data: 'description' },
			{ data: 'min_target' },
			{ data: 'max_target' },
			{ data: 'percentage' },
			{ data: 'date_added' },
			{ data: 'date_modified' },
			{ data: null },
			],
			"columnDefs": [
			{ 
				targets: [0,1,2,3,4,5,6,7,8], //first column / numbering column
				orderable: false, //set not orderable
			},
			{
				render : function ( data, type, row ) {
					return 	"<a href='#' class='btn btn-success btn-sm btn-edit' data-id="+row['id']+">Ubah</a>"+
					"<a href='#' class='btn btn-danger btn-sm' data-target='#delete-modal' data-toggle='modal' data-id="+row['id']+">Hapus</a>";
				},
				targets: 8
			},
			//{ "visible": false,  "targets": [ 3 ] }
			]
		});
		
		$('#add').on('click', function (event) {
			$('#input_title').html("Tambah Komisi");
			is_edit = false;
			$('#input')[0].reset();
			$('#add-modal').modal('show');
		});
		
		$('#list').on('click','.btn-edit', function() {
			$('#input_title').html("Ubah Komisi");
			current_id = $(this).data("id");
			
			is_edit = true;
			
			$('#input')[0].reset();
			$('#icon_preview').attr('src',"");
			
			$.ajax({
				method: 'post',
				url: base_url+"admin/agent/get_commision/"+current_id,
				cache: false,
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						$('#add-modal').modal('show');
						$('input[name ="title"]').val(data.data.title);
						$('textarea[name ="description"]').val(data.data.description);
						$('input[name ="min_target"]').val(data.data.min_target);
						$('input[name ="max_target"]').val(data.data.max_target);
						$('input[name ="percentage"]').val(data.data.percentage);
						
						
					}else
					{
						alert( data.message );
					}
				}
			});
		});
		
		$('#post').click(function() {
			
			$.ajax({
				method: 'post',
				url: base_url+"admin/agent/post_commision/"+(is_edit?current_id:""),
				cache: false,
				data : $("#input").serialize(),
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						//alert( data.message );
						table.ajax.reload();
						$('#add-modal').modal('hide');
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
		
		$('#delete_commision').click(function() {
			$.ajax({
				method: 'post',
				url: base_url+"admin/agent/delete_commision/"+current_id,
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