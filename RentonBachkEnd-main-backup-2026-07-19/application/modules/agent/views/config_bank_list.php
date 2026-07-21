<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Konfigurasi
			<small>Bank</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Konfigurasi</a></li>
			<li class="active">Bank</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Bank</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<div class="form-group">
							<button id="add" class="btn btn-sm btn-primary">Tambah Bank</button>
						</div>
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th>Icon</th>
										<th>Nama</th>
										<th>Kode</th>
										<th>Nomor Rekening</th>
										<th>Atas Nama</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
								<tfoot>
									<tr>
										<th>ID</th>
										<th>Icon</th>
										<th>Nama</th>
										<th>Kode</th>
										<th>Nomor Rekening</th>
										<th>Atas Nama</th>
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
				<h4 id="input_title" class="modal-title">Tambah Bank</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<form id="input" role="form">
					<div class="box-body">
						<div class="form-group">
							<label>Bank</label>
							<select id="bank_id" name="bank_id" class="form-control" value="0">
								<?php 
									foreach($banks as $val)
									{ ?>
									<option <?= ($val->id == 1)?"selected":""?> value="<?php echo $val->id;?>"><?php echo $val->name;?></option>
									<?php
									}
								?>
							</select>
						</div>
						<div class="form-group">
							<label>Nomor Rekening</label>
							<input type="text" class="form-control" id="bank_number" name="bank_number" placeholder="Nomor Rekening"/>
						</div>
						<div class="form-group">
							<label>Atas Nama</label>
							<input type="text" class="form-control" id="name" name="name" placeholder="Atas Nama"/>
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
				<h4 class="modal-title">Hapus Bank</h4>
			</div>
			<div class="modal-body">
				Apa kamu yakin ingin menghapus Akun Bank ini?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
				<button id="delete-item" class="btn btn-danger">Hapus</a>
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
			"ajax": {
				method : 'GET',
				url : base_url+"agent/config/get_list_bank"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'icon' },
			{ data: 'bank_name' },
			{ data: 'code' },
			{ data: 'bank_number' },
			{ data: 'name' },
			{ data: null },
			],
			"columnDefs": [
			{
				render : function ( data, type, row ) {
					
					if(row['icon'] != null)
					{
						return "<img width='64' src='data:image/png;base64,"+row['icon']+"'/>";
					}else
					return 	"<span>Tidak</span>";
				},
				targets: 1
			},
			{
				render : function ( data, type, row ) {
					return 	"<a href='#' class='btn btn-success btn-sm btn-edit' data-id="+row['id']+">Ubah</a>"+	
					"<a href='#' class='btn btn-danger btn-sm' data-target='#delete-modal' data-toggle='modal' data-id="+row['id']+">Hapus</a>";
				},
				targets: 6
			},
			//{ "visible": false,  "targets": [ 3 ] }
			]
		});
		
		
		$('#add').on('click', function (event) {
			$('#input_title').html("Tambah Bank");
			is_edit = false;
			$('#input')[0].reset();
			$('#add-modal').modal('show');
		});
		
		$('#list').on('click','.btn-edit', function() {
			$('#input_title').html("Ubah Bank");
			current_id = $(this).data("id");
			
			is_edit = true;
			
			$('#input')[0].reset();
			
			$.ajax({
				method: 'post',
				url: base_url+"agent/config/get_bank/"+current_id,
				cache: false,
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						$('#add-modal').modal('show');
						$('select[name ="bank_id"]').val(data.data.bank_id);
						$('input[name ="bank_number"]').val(data.data.bank_number);
						$('input[name ="name"]').val(data.data.name);
						
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
				url: base_url+"agent/config/post_bank/"+(is_edit?current_id:""),
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
		
		$('#delete-item').click(function() {
			$.ajax({
				method: 'post',
				url: base_url+"agent/config/delete_bank/"+current_id,
				cache: false,
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						//alert( data.message );
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