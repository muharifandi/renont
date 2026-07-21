<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Keuangan
			<small>Pencairan</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Keuangan</a></li>
			<li class="active">Pencairan</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Pencairan</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<div class="form-group">
							<a id="add" href="#"><button id="request-withdraw" class="btn btn-sm btn-primary">Buat Permintaan</button></a>
						</div>
						
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th>Nama</th>
										<th>Rekening Tujuan</th>
										<th>Nominal</th>
										<th>Status</th>
										<th>Tanggal</th>
										
									</tr>
								</thead>
								<tbody>
									
								</tbody>
								<tfoot>
									<tr>
										<th>ID</th>
										<th>Nama</th>
										<th>Rekening Tujuan</th>
										<th>Nominal</th>
										<th>Status</th>
										<th>Tanggal</th>
										
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
				<h4 id="input_title" class="modal-title">Permintaan Pencairan</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<form id="input" role="form">
						<div class="box-body">
							<div class="form-group">
								<label>Bank *</label>
								<select id="bank_id" name="account_bank_id" class="form-control" value="0">
									<?php 
										foreach($agent_banks as $val)
										{ ?>
										<option <?= ($val->id == 1)?"selected":""?> value="<?php echo $val->id;?>"><?php echo $val->bank_name." - ".$val->bank_number." - Atas Nama : ".$val->name;?></option>
										<?php
										}
									?>
								</select>
							</div>
							<div class="form-group">
								<label>Nominal * <span style="color:red;font-size:10px;"><?php echo "Minimum pencairan : Rp. ".number_format($config['withdraw_minimum'], 2 ,",",".");?></span></label>
								<input type="number" class="form-control" id="value" name="value" placeholder="Nominal" min="<?php echo $config['withdraw_minimum'];?>" value="<?php echo $config['withdraw_minimum'];?>"/>
							</div>
							<div class="form-group">
								<label>Deskripsi</label>
								<textarea class="form-control" id="description" name="description" placeholder="Deskripsi"></textarea>
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
			"ajax": {
				method : 'GET',
				url : base_url+"agent/agent/get_list_withdraw"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'fullname' },
			{ data: 'bank_name' },
			{ data: 'value' },
			{ data: 'status_name' },
			{ data: 'date_added' },
			],
			"columnDefs": [
			{
				render : function ( data, type, row ) {
					if(data)
					return "<center><img width='64' src='data:image/png;base64,"+row['icon']+"'/><br><span>"+row['bank_name']+" - "+row['bank_number']+"</span><br><span>"+row['name']+"</span></center>";
					else
					return "<center><img width='64' src='"+base_url+"data/default/no_image.png'></img></center>";
				},
				targets: 2
			},
			]
		});
		
		$('#add').on('click', function (event) {
			$('#input_title').html("Permintaan Pencairan");
			is_edit = false;
			$('#input')[0].reset();
			$('#add-modal').modal('show');
		});
		
		$('#post').click(function() {
			$.ajax({
				method: 'post',
				url: base_url+"agent/agent/request_withdraw/",
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
	});
	
	
</script>