<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Konfigurasi
			<small>Layanan</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Rental Kendaraan</a></li>
			<li class="active">Layanan</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Layanan</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<div class="form-group">
							<button id="add" class="btn btn-sm btn-primary">Tambah Layanan</button>
						</div>
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th>Icon</th>
										<th>Nama</th>
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
			<h4 id="input_title" class="modal-title">Tambah Layanan</h4>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<form id="input" role="form">
					<div class="box-body">
						<div class="form-group">
							<label>ID</label>
							<span><i>* input ini digunakan oleh dev app mobile untuk mengaktifkan layanan di app mobile. Koordinasikan dengan developer untuk input nilai ini</i></span>
							<input type="text" class="form-control" id="id" name="id" placeholder="id"/>
						</div>	
						<div class="form-group">
							<label>Nama</label>
							<input type="text" class="form-control" id="name" name="name" placeholder="Nama"/>
						</div>	
						<div class="form-group">
							<label>Icon Base64</label>
							<input id="icon_select" type="file" accept="image/x-png">
							
							<p class="help-block">Upload icon maximal 256x256 sama sisi</p>
							<img id="icon_preview" src="" alt="No image Selected" class="img-thumbnail">
							<input type="hidden" class="form-control" id="icon_base_64" name="icon"/>
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
				<h4 class="modal-title">Hapus Layanan</h4>
			</div>
			<div class="modal-body">
				Apa kamu yakin ingin menghapus Layanan ini?
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
				url : base_url+"admin/config/get_list_feature"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'icon' },
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
					
					if(row['icon'] != null)
					{
						return "<img width='24' src='data:image/png;base64,"+row['icon']+"'/>";
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
				targets: 3
			},
			//{ "visible": false,  "targets": [ 3 ] }
			]
		});
		
		$("#icon_select").change(function() {
			readURL(this);
		});
		
		function readURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				
				reader.onload = function(e) {
					$('#icon_preview').attr('src', e.target.result);
					$('#icon_base_64').val(e.target.result.replace("data:image/png;base64,", ""));
				}
				
				reader.readAsDataURL(input.files[0]);
			}
		}
		
		$('#add').on('click', function (event) {
			$('#input_title').html("Tambah Layanan");
			is_edit = false;
			$('#input')[0].reset();
			$('#icon_preview').attr('src',"");
			$('#add-modal').modal('show');
		});
		
		$('#list').on('click','.btn-edit', function() {
			$('#input_title').html("Ubah Layanan");
			current_id = $(this).data("id");
			
			is_edit = true;
			
			$('#input')[0].reset();
			$('#icon_preview').attr('src',"");
			
			$.ajax({
				method: 'post',
				url: base_url+"admin/config/get_feature/"+current_id,
				cache: false,
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						$('#add-modal').modal('show');
						$('input[name ="id"]').val(data.data.id);
						$('input[name ="name"]').val(data.data.name);
						$('#icon_preview').attr('src', "data:image/png;base64,"+data.data.icon);
						$('#icon_base_64').val(data.data.icon);
						
					}else
					{
						alert( data.message );
					}
				}
			});
		});
		
		$('#post').click(function() {
			var status_selected = $('#status_select').val();
			
			$.ajax({
				method: 'post',
				url: base_url+"admin/config/post_feature/"+(is_edit?current_id:""),
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
				url: base_url+"admin/config/delete_feature/"+current_id,
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