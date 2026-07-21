<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Event
			<small>Klaim Hadiah Mitra</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Event</a></li>
			<li class="active">Klaim Hadiah Mitra</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Klaim Hadiah Mitra</h3>
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
										<th>Mitra</th>
										<th>Alamat</th>
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
										<th>Mitra</th>
										<th>Alamat</th>
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


<div class="modal fade" id="process-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">Proses Klaim Hadiah</h4>
		</div>
		<div class="modal-body">
			Apakah kamu yakin ingin memproses klaim hadiah ini?
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
			<button id="process_reward" class="btn btn-primary">Proses</a>
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
			'order' : [],
			"ajax": {
				method : 'GET',
				url : base_url+"admin/partnerReward/get_list_claim"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'img' },
			{ data: 'title' },
			{ data: 'company_name' },
			{ data: 'address' },
			{ data: 'date_modified' },
			{ data: null },
			],
			"columnDefs": [
			{ 
				targets: [0,1,2,3,4,5,6], //first column / numbering column
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
					var result = "";
					
					if(row['processed'] == 0)
					result = "<a href='#' class='btn btn-primary btn-sm' data-target='#process-modal' data-toggle='modal' data-id="+row['id']+">Proses</a>";
					
					return 	result;
				},
				targets: 6
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
		
		$('#process-modal').on('show.bs.modal', function (event) {
			current_id = $(event.relatedTarget).data('id');
		});
		$('#process_reward').click(function() {
			$.ajax({
				method: 'post',
				url: base_url+"admin/partnerReward/process/"+current_id,
				cache: false,
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						alert( data.message );
						table.ajax.reload();
						$('#process-modal').modal('hide');
					}else
					{
						alert( data.message );
					}
				}
			});
		});
		
	});
</script>