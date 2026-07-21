<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Event
			<small>Pratinjau Berita</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Event</a></li>
			<li class="active">Pratinjau Berita</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">List Pratinjau Berita</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<?php echo $message;?>
						<div class="form-group">
							<button id="add" class="btn btn-sm btn-primary">Tambah Pratinjau Berita</button>
						</div>
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th></th>
										<th>Urutan</th>
										<th>Judul</th>
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
										<th>Urutan</th>
										<th>Judul</th>
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

<div class="modal fade" id="add-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
				<h4 id="input_title" class="modal-title">Tambah Pratinjau Berita</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<form id="input" role="form">
						<div class="box-body">
							<div class="form-group">
								<label>Urutan *</label>
								<input type="number" class="form-control" id="order" name="order" placeholder="Urutan"/>
							</div>
							<div class="form-group">
								<label>Berita</label>
								<select id="news_select" class="form-control" name="news_id" style="width: 100%;">
									
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
<div class="modal fade" id="status-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Ganti Status Pratinjau</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label>Status</label>
					<select id="status_select" class="form-control" value="1">
						<?php 
							foreach($list_status as $val)
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
				<h4 class="modal-title">Hapus Pratinjau</h4>
			</div>
			<div class="modal-body">
				Apakah yakin ingin menghapus Pratinjau ini?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
				<button id="delete_preview" class="btn btn-danger">Hapus</a>
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
				url : base_url+"admin/news/get_list_preview"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'img' },
			{ data: 'order' },
			{ data: 'title' },
			{ data: 'status_name' },
			{ data: null },
			],
			"columnDefs": [
			{ 
				targets: [0,1,2,3,4,5], //first column / numbering column
				orderable: false, //set not orderable
			},
			{
				render : function ( data, type, row ) {
					
					if(row['img'])
					{
						return "<img width='120' src='"+base_url+"data/news/"+data+"'></img>";
					}else
					return "<img width='120' src='"+base_url+"data/default/no_image.png'></img>";
				},
				targets: 1
			},
			{
				render : function ( data, type, row ) {
					return 	"<a href='#' class='btn btn-success btn-sm btn-edit' data-id="+row['id']+">Ubah</a>"+"<a href='#' class='btn btn-primary btn-sm' data-target='#status-modal' data-toggle='modal' data-id="+row['id']+" data-status="+row['status']+">Change Status</a>"+
					"<a href='#' class='btn btn-danger btn-sm' data-target='#delete-modal' data-toggle='modal' data-id="+row['id']+">Hapus</a>";
				},
				targets: 5
			},
			//{ "visible": false,  "targets": [ 3 ] }
			]
		});
		
		var news_select = $('#news_select').select2({
			ajax: {
				url: base_url+"admin/news/get_news_select",
				type: 'GET',
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						search: params.term, // search term
						page: params.page
					};
				},
				processResults: function (data, params) {
					// parse the results into the format expected by Select2
					// since we are using custom formatting functions we do not need to
					// alter the remote JSON data, except to indicate that infinite
					// scrolling can be used
					params.page = params.page || 1;
					
					return {
						results: data.items,
						pagination: {
							more: (params.page * 30) < data.total_count
						}
					};
				},
				cache: true
			},
			placeholder: 'Mencari Berita. . .',
			escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
			minimumInputLength: 1,
			templateResult: formatRepo,
			templateSelection: formatRepoSelection,
		});
		
		
		
		function formatRepo (repo) {
			if (repo.loading) {
				return repo.title;
			}
			
			var $container = $(
			"<div>" +
			"<div style='display:inline-block;'><img width='72px' src='"+base_url+"data/news/"+repo.img+"'/></div>" +
			"<div style='display:inline-block;margin-left:5px;vertical-align:top;'><label style='margin-bottom:0px;'><b>" + repo.title + "</b></label><br><span>"+repo.user_type+"</span> - <span>"+repo.status+"</span></div>" +
			
			"</div>"
			);
			
			
			return $container;
		}
		
		function formatRepoSelection (repo) {
			return repo.title || repo.text;
		}
		
		$('#add').on('click', function (event) {
			$('#input_title').html("Tambah Pratinjau Berita");
			is_edit = false;
			$('#input')[0].reset();
			$('#add-modal').modal('show');
		});
		
		$('#list').on('click','.btn-edit', function() {
			$('#input_title').html("Ubah Pratinjau Berita");
			current_id = $(this).data("id");
			
			is_edit = true;
			
			$('#input')[0].reset();
			$('#icon_preview').attr('src',"");
			
			$.ajax({
				method: 'post',
				url: base_url+"admin/news/get_preview/"+current_id,
				cache: false,
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						$('#add-modal').modal('show');
						$('input[name ="order"]').val(data.data.order);
						news_select.empty();
						news_select.append('<option value="'+data.data.news_id+'" selected="selected">'+data.data.title+'</option>');
						
						$test.trigger('change');
						
						
						
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
				url: base_url+"admin/news/post_preview/"+(is_edit?current_id:""),
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
				url: base_url+"admin/news/change_status_preview/"+current_id,
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
		
		$('#delete_preview').click(function() {
			$.ajax({
				method: 'post',
				url: base_url+"admin/news/delete_preview/"+current_id,
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