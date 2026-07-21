<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Marketing
			<small>Pencairan</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Marketing</a></li>
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
										<th></th>
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
<div class="modal fade" id="verification-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Verifikasi Pencairan</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="form-group">
						<center>
							<img width="128" id="bank_icon" src="" alt="No image Selected"><br>
							<b><span>Nama Bank : </span></b><span id="bank_name"></span><br>
							<b><span>Nomor Rekening : </span></b><span id="bank_number"></span><br>
							<b><span>Atas Nama : </span></b><span id="bank_name_of"></span><br>
						</center>
					</div>
					<div class="form-group">
						<center>
						<b><span>Nominal : </span></b><span style="color:red;font-size:24px;" id="value"></span><br>
						</center>
					</div>
					
					<div class="form-group">
						<label>Deskripsi</label>
						<textarea class="form-control" id="description" name="description" placeholder="Tulis deskripsi disini"></textarea>
					</div>
					
					<label>Status</label>
					<select id="status_select" class="form-control" value="1">
						<?php 
							foreach($list_withdraw_status as $val)
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
				<button id="verification" class="btn btn-danger">Verifikasi</a>
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
			'order' :[],
			"ajax": {
				method : 'GET',
				url : base_url+"admin/agent/get_list_withdraw"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'fullname' },
			{ data: 'bank_name' },
			{ data: 'value' },
			{ data: 'status_name' },
			{ data: 'date_added' },
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
					return "<center><img width='64' src='data:image/png;base64,"+row['icon']+"'/><br><span>"+row['bank_name']+" - "+row['bank_number']+"</span><br><span>"+row['name']+"</span></center>";
					else
					return "<center><img width='64' src='"+base_url+"data/default/no_image.png'></img></center>";
				},
				targets: 2
			},
			{
				render : function ( data, type, row ) {
					
					var menu ="";
					
					menu+="<a target='_blank_' href='"+base_url+"admin/report/agent_withdraw_invoice/"+row['id']+"' class='btn btn-primary btn-sm'>Cetak Faktur</a>";
					if(row['status'] == '1')
					{
						menu += "<a href='#' class='btn btn-primary btn-sm' data-target='#verification-modal' data-toggle='modal'"+
						" data-id="+row['id']+
						" data-icon="+encodeURIComponent(row['icon'])+
						" data-fullname="+encodeURIComponent(row['fullname'])+
						" data-bank-name="+encodeURIComponent(row['bank_name'])+
						" data-bank-number="+encodeURIComponent(row['bank_number'])+
						" data-bank-name-of="+encodeURIComponent(row['name'])+
						" data-value="+encodeURIComponent(row['value'])+
						" data-status="+encodeURIComponent(row['status'])+
						">Verifikasi</a>";
					}
					
					return menu;
				},
				targets: 6
			},
			]
		});
		
		$('#verification-modal').on('show.bs.modal', function (event) {
			current_id = $(event.relatedTarget).data('id');
			
			var bank_icon = decodeURIComponent($(event.relatedTarget).data('icon'));
			var bank_name = decodeURIComponent($(event.relatedTarget).data('bank-name'));
			var bank_number = decodeURIComponent($(event.relatedTarget).data('bank-number'));
			var bank_name_of = decodeURIComponent($(event.relatedTarget).data('bank-name-of'));
			var value = decodeURIComponent($(event.relatedTarget).data('value'));
			var current_status = decodeURIComponent($(event.relatedTarget).data('status'));
			
			$('#bank_icon').attr('src', "data:image/png;base64,"+bank_icon);
			$('#bank_name').html(bank_name);
			$('#bank_number').html(bank_number);
			$('#bank_name_of').html(bank_name_of);
			$('#value').html(value);
			$('#description').val("");
			$('#status_select').val(current_status);
		});
		
		$('#verification').click(function() {
			var status_selected = $('#status_select').val();
			var description = $('#description').val();
			$.ajax({
				method: 'post',
				url: base_url+"admin/agent/verification_withdraw/"+current_id,
				cache: false,
				data :{
					status_id : status_selected,
					description : description
				},
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						alert( data.message );
						table.ajax.reload();
						$('#verification-modal').modal('hide');
					}else
					{
						alert( data.message );
					}
				}
			});
		});
	});
</script>