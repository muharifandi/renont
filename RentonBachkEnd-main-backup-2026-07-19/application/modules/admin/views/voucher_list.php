<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Event
			<small>Voucher</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Event</a></li>
			<li class="active">Voucher</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Voucher</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<div class="form-group">
							<button id="add-voucher" class="btn btn-sm btn-primary" data-target='#add-voucher-modal' data-toggle='modal'>Add Voucher</button>
						</div>
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th>Code Voucher</th>
										<th>Fitur</th>
										<th>User Type</th>
										<th>Type Voucher</th>
										<th>Description</th>
										<th>Value</th>
										<th>Expried</th>
										<th>Quota</th>
										<th>Status</th>
										<th>Last Update</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
								<tfoot>
									<tr>
										<th>ID</th>
										<th>Code Voucher</th>
										<th>Fitur</th>
										<th>User Type</th>
										<th>Type Voucher</th>
										<th>Description</th>
										<th>Value</th>
										<th>Expried</th>
										<th>Quota</th>
										<th>Status</th>
										<th>Last Update</th>
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
<div class="modal fade" id="add-voucher-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Voucher</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<form id="input_voucher" role="form">
						<div class="box-body">
							<div class="form-group">
								<label>Code Voucher</label>
								<input type="text" class="form-control" id="code" name="code" placeholder="Kode Voucher"/>
								</div>
								<div class="form-group">
								<label>Feature</label>
								<select id="feature" name="feature" class="form-control" value="-1">
									<option <?php echo ($edit)?(($news->feature == null)?"selected":""):"";?> value="-1">Semua Fitur</option> 
									<?php 
										foreach($feature as $val)
										{ ?>
										<option <?php echo ($edit)?(($val->id == $news->feature)?"selected":""):"";?> value="<?php echo $val->id;?>"><?php echo $val->name;?></option>
										<?php
										}
									?>
								</select>
							</div>
							<div class="form-group">
								<label>User Type</label>
								<select id="user_type" name="user_type" class="form-control" value="0">
									<?php 
										foreach($user_type as $val)
										{ ?>
										<option <?= ($val->id == 1)?"selected":""?> value="<?php echo $val->id;?>"><?php echo $val->description;?></option>
										<?php
										}
									?>
								</select>
							</div>
							<div class="form-group">
								<label>Type Voucher</label>
								<select id="voucher_type" name="voucher_type" class="form-control" value="1">
									<?php 
										foreach($list_type_voucher as $val)
										{ ?>
										<option <?= ($val->id == 1)?"selected":""?> value="<?php echo $val->id;?>"><?php echo $val->name;?></option>
										<?php
										}
									?>
								</select>
							</div>
							<div class="form-group">
								<label>Value</label>
								<input type="number" pattern="[0-9]*" class="form-control" name="value" id="value" placeholder="ex: 12500">
							</div>
							<div class="form-group">
								<label>Description</label>
								<textarea name="description" class="form-control" id="description" placeholder="write description here"></textarea>	
							</div>
							<div class="form-group">
								<label>Use Expire</label>
								<select id="use_expire" name="use_expire" class="form-control" value="0">
									<option value="0">No</option>
									<option value="1">Yes</option>
								</select>
							</div>
							<div class="form-group">
								<label>Start Date :</label>
								<div class="input-group date">
									<div class="input-group-addon">
										<i class="fa fa-calendar"></i>
									</div>
									<input type="text" name="start_date" class="form-control pull-right date" id="start_date">
								</div>
								<!-- /.input group -->
							</div>
							<div class="form-group">
								<label>End Date :</label>
								<div class="input-group date">
									<div class="input-group-addon">
										<i class="fa fa-calendar"></i>
									</div>
									<input type="text" name="end_date" class="form-control pull-right date" id="end_date">
								</div>
								<!-- /.input group -->
							</div>
							<div class="form-group">
								<label>Use Quota</label>
								<select id="use_quota" name="use_quota" class="form-control" value="0">
									<option value="0">No</option>
									<option value="1">Yes</option>
								</select>
							</div>
							<div class="form-group">
								<label>Quota</label>
								<input type="number" pattern="[0-9]*" class="form-control" name="quota" id="quota" placeholder="ex: 12500">
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button id="add_voucher" class="btn btn-success">Save</a>
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
				<h4 class="modal-title">Change Status Voucher</h4>
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
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button id="change_status" class="btn btn-danger">Change</a>
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
				<h4 class="modal-title">Delete Voucher</h4>
			</div>
			<div class="modal-body">
				Are you sure want to delete this Voucher?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button id="delete_voucher" class="btn btn-danger">Delete</a>
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
				url : base_url+"admin/voucher/get_list"
			},
			'columns': [
			{ data: 'id' },
			{ data: 'code' },
			{ data: 'feature' },
			{ data: 'user_type' },
			{ data: 'voucher_type' },
			{ data: 'description' },
			{ data: 'value' },
			{ data: 'use_expire' },
			{ data: 'quota' },
			{ data: 'status' },
			{ data: 'date_modified' },
			{ data: null },
			],
			"columnDefs": [
			{ 
				targets: [0,1,2,3,4,5,6,7,8,9,10,11], //first column / numbering column
				orderable: false, //set not orderable
			},
			{
				render : function ( data, type, row ) {
					
					if(row['feature'] == null)
					{
						return "<span>Semua</span>";
					}else
					return 	"<span>"+row['feature']+"</span>";
				},
				targets: 2
			},
			{
				render : function ( data, type, row ) {
					
					if(row['use_expire'] == "1")
					{
						return "<span>"+row['start_date']+" - "+row['end_date']+"</span>";
					}else
					return 	"<span>Tidak</span>";
				},
				targets: 7
			},
			{
				render : function ( data, type, row ) {
					
					if(row['use_quota'] == "1")
					{
						return "<span>"+row['quota']+"</span>";
					}else
					return 	"<span>Tidak</span>";
				},
				targets: 8
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
				url: base_url+"admin/voucher/change_status/"+current_id,
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
		
		$('#add-voucher-modal').on('shown.bs.modal', function (event) {
			$('#input_voucher')[0].reset();
			$('.date').each(function(){
				$(this).datepicker({
					autoclose : true,
					format :'dd-mm-yyyy',
				});
			});
		});
		
		$('#add_voucher').click(function() {
			var status_selected = $('#status_select').val();
			$.ajax({
				method: 'post',
				url: base_url+"admin/voucher/add_voucher/",
				cache: false,
				data : $("#input_voucher").serialize(),
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						//alert( data.message );
						table.ajax.reload();
						$('#add-voucher-modal').modal('hide');
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
		$('#delete_voucher').click(function() {
			$.ajax({
				method: 'post',
				url: base_url+"admin/voucher/delete/"+current_id,
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