<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Rental Kendaraan
			<small>Model Kendaraan</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Rental Kendaraan</a></li>
			<li class="active">Model Kendaraan</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Model Kendaraan</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<div class="form-group">
							<button id="add" class="btn btn-sm btn-primary">Tambah Model Kendaraan</button>
						</div>
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th>Merek</th>
										<th>Nama</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
								<tfoot>
									<tr>
										<th>ID</th>
										<th>Merek</th>
										<th>Nama</th>
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
				<h4 id="input_title" class="modal-title">Tambah Model Kendaraan</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<form id="input" role="form">
						<div class="box-body">
							<div class="form-group">
								<label>Nama</label>
								<input type="text" class="form-control" id="name" name="name" placeholder="Nama"/>
							</div>	
							<div class="form-group">
								<label>Merek</label>
								<select id="brand_id" name="brand_id" class="form-control" value="0">
									<?php 
										foreach($brand as $val)
										{ ?>
										<option <?= ($val->id == 1)?"selected":""?> value="<?php echo $val->id;?>"><?php echo $val->functional_type_name." > ".$val->name;?></option>
										<?php
										}
									?>
								</select>
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
				<h4 class="modal-title">Hapus Model Kendaraan</h4>
			</div>
			<div class="modal-body">
				Apa kamu yakin ingin menghapus model kendaraan ini?
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
			'order' : [],
			"ajax": {
				method : 'GET',
				url : base_url+"admin/rentVehicle/get_list_vehicle_model"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'brand_name' },
			{ data: 'name' },
			{ data: null },
			],
			"columnDefs": [
			{ 
				targets: [0,1,2,3], //first column / numbering column
				orderable: false, //set not orderable
			},
			{
				render : function ( data, type, row ) {
					return 	"<a href='#' class='btn btn-success btn-sm btn-edit' data-id="+row['id']+">Ubah</a>"+	
					"<a href='#' class='btn btn-danger btn-sm' data-target='#delete-modal' data-toggle='modal' data-id="+row['id']+">Hapus</a>";
				},
				targets: 3
			},
			]
		});
		
		
		$('#add').on('click', function (event) {
			$('#input_title').html("Tambah Model Kendaraan");
			is_edit = false;
			$('#input')[0].reset();
			$('#add-modal').modal('show');
		});
		
		$('#list').on('click','.btn-edit', function() {
			$('#input_title').html("Ubah Model Kendaraan");
			current_id = $(this).data("id");
			is_edit = true;
			$('#input')[0].reset();
			
			$.ajax({
				method: 'post',
				url: base_url+"admin/rentVehicle/get_vehicle_model/"+current_id,
				cache: false,
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						$('#add-modal').modal('show');
						$('select[name ="brand_id"]').val(data.data.brand_id);
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
				url: base_url+"admin/rentVehicle/post_vehicle_model/"+(is_edit?current_id:""),
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
				url: base_url+"admin/rentVehicle/delete_vehicle_model/"+current_id,
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