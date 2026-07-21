<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Event
			<small>Hadiah Mitra</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Event</a></li>
			<li class="active">Hadiah Mitra</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Hadiah Mitra</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<div class="form-group">
							<button id="add-voucher" class="btn btn-sm btn-primary" data-target='#add-reward-modal' data-toggle='modal'>Tambah Hadiah</button>
						</div>
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th></th>
										<th>Judul</th>
										<th>Deskripsi</th>
										<th>Fitur</th>
										<th>Lingkup</th>
										<th>Jenis Hadiah</th>
										<th>Target</th>
										<th>Hadiah Poin</th>
										<th>Status</th>
										<th>Tanggal</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
								<tfoot>
									<tr>
										<th>ID</th>
										<th></th>
										<th>Judul</th>
										<th>Deskripsi</th>
										<th>Fitur</th>
										<th>Lingkup</th>
										<th>Jenis Hadiah</th>
										<th>Target</th>
										<th>Hadiah Poin</th>
										<th>Status</th>
										<th>Tanggal</th>
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
<div class="modal fade" id="add-reward-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Tambah Hadiah</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<form method="post" id="input_reward" role="form" enctype="multipart/form-data">
							<div class="box-body">
								<div class="form-group">
									<label>Judul</label>
									<input type="text" class="form-control" id="title" name="title" placeholder="Judul Hadiah"/>
								</div>
								<div class="form-group">
									<label>Deskripsi</label>
									<textarea name="description" class="form-control" id="description" placeholder="Tulis deskripsi disini"></textarea>	
								</div>
								<div class="form-group">
									<label>Fitur</label>
									<select id="feature" name="feature_id" class="form-control" value="-1">
										<?php 
											foreach($feature as $val)
											{ ?>
											<option value="<?php echo $val->id;?>"><?php echo $val->name;?></option>
											<?php
											}
										?>
									</select>
								</div>
								<div class="form-group">
									<label>Lingkup</label>
									<select id="reward_scope" name="reward_scope" class="form-control" value="0">
										<?php 
											foreach($list_scope_reward as $val)
											{ ?>
											<option value="<?php echo $val->id;?>"><?php echo $val->name;?></option>
											<?php
											}
										?>
									</select>
								</div>
								<div class="form-group">
									<label>Target</label>
									<input type="number" pattern="[0-9]*" class="form-control" name="target" id="target" placeholder="contoh : 12">
								</div>
								<div class="form-group">
									<label>Jenis Hadiah</label>
									<select id="reward_type" name="reward_type" class="form-control" value="0">
										<?php 
											foreach($list_type_reward as $val)
											{ ?>
											<option value="<?php echo $val->id;?>"><?php echo $val->name;?></option>
											<?php
											}
										?>
									</select>
								</div>
								<div class="form-group">
									<label>Hadiah Poin</label>
									<input type="number" pattern="[0-9]*" class="form-control" name="point_reward" id="point_reward" placeholder="contoh : 12">
								</div>
								<div class="form-group">
									<label for="exampleInputFile">Gambar</label>
									<input id="img_select" type="file" name="img">
									
									<p class="help-block">File gambar harus simetris dengan maksimal 256x256 pixel</p>
									<img id="image_preview" src="<?php echo base_url()."data/default/no_image.png";?>" width="256" height="256" alt="No image Selected" class="img-thumbnail">
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
					<button id="add_reward" class="btn btn-success">Simpan</a>
				</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<div class="modal fade" id="status-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Ganti Status Hadiah</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label>Status</label>
					<select id="status_select" class="form-control" value="1">
						<?php 
							foreach($list_status as $val)
							{ ?>
							<option value="<?php echo $val->id;?>"><?php echo $val->name;?></option>
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
				<h4 class="modal-title">Hapus Hadiah</h4>
			</div>
			<div class="modal-body">
				Apakah kamu yakin ingin menghapus hadiah ini?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
				<button id="delete_reward" class="btn btn-danger">Hapus</a>
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
		$("#img_select").change(function() {
			readURL(this);
		});
		
		function readURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				
				reader.onload = function(e) {
					$('#image_preview').attr('src', e.target.result);
				}
				
				reader.readAsDataURL(input.files[0]);
			}
		}
		
		table = $('#list').DataTable({
			"language": {
                "url": base_url+"data/default/datatables.indonesian.json"
			},
			"paging": true,
			'processing': true,
			'serverSide': true,
			'order' :[],
			"ajax": {
				method : 'GET',
				url : base_url+"admin/partnerReward/get_list"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'img' },
			{ data: 'title' },
			{ data: 'description' },
			{ data: 'feature_name' },
			{ data: 'reward_scope_name' },
			{ data: 'reward_type_name' },
			{ data: 'target' },
			{ data: 'point_reward' },
			{ data: 'status_name' },
			{ data: 'date_added' },
			{ data: null },
			],
			"columnDefs": [
			{ 
				targets: [0,1,2,3,4,5,6,7,8,9,10,11], //first column / numbering column
				orderable: false, //set not orderable
			},
			{
				render : function ( data, type, row ) {
					if(data)
					return '<center><img width="72px" src="'+base_url+'data/rewards/'+data +'"/></center>';
					else
					return "<center><img width='120' src='"+base_url+"data/default/no_image.png'></img></center>";
				},
				targets: 1
			},
			{
				render : function ( data, type, row ) {
					return 	"<a href='#' class='btn btn-primary btn-sm' data-target='#status-modal' data-toggle='modal' data-id="+row['id']+" data-status_id="+row['status_id']+">Change Status</a>"+
					"<a href='#' class='btn btn-danger btn-sm' data-target='#delete-modal' data-toggle='modal' data-id="+row['id']+">Delete</a>";
				},
				targets: 11
			},
			//{ "visible": false,  "targets": [ 3 ] }
			]
		});
		
		// change status 
		$('#status-modal').on('show.bs.modal', function (event) {
			current_id = $(event.relatedTarget).data('id');
			$('#status_select').val($(event.relatedTarget).data('status_id'));
		});
		
		$('#change_status').click(function() {
			var status_selected = $('#status_select').val();
			$.ajax({
				method: 'post',
				url: base_url+"admin/partnerReward/change_status/"+current_id,
				cache: false,
				data :{
					status_id : status_selected
				},
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						//alert( data.message );
						table.ajax.reload();
						$('#status-modal').modal('hide');
					}else
					{
						alert( data.message );
					}
				}
			});
		});
		
		$('#add-reward-modal').on('shown.bs.modal', function (event) {
			$('#input_reward')[0].reset();
			$('#image_preview').attr('src', base_url+"data/default/no_image.png");
			$('.date').each(function(){
				$(this).datepicker({
					autoclose : true,
					format :'dd-mm-yyyy',
				});
			});
		});
		
		$('#add_reward').click(function() {
			var form = $('#input_reward')[0];
			
			// Create an FormData object 
			var data = new FormData(form);
			
			$.ajax({
				method: 'post',
				url: base_url+"admin/partnerReward/add_reward/",
				cache: false,
				enctype: 'multipart/form-data',
				processData: false,  // Important!
				contentType: false,
				data : data,
				dataType: 'json',
				timeout: 600000,
				success: function(data){
					if(data.status)
					{
						//alert( data.message );
						table.ajax.reload();
						$('#add-reward-modal').modal('hide');
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
		$('#delete_reward').click(function() {
			$.ajax({
				method: 'post',
				url: base_url+"admin/partnerReward/delete/"+current_id,
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