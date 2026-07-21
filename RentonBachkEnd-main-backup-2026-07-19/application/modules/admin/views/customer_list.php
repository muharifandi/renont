  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Pelanggan
        <small>List</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Pelanggan</a></li>
        <li class="active">List</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
                <thead>
                <tr>
                  <th>ID</th>
                  <th></th>
                  <th>Nama</th>
                  <th>Email</th>
                  <th>Telepon</th>
                  <th>Saldo</th>
                  <th>Poin</th>
                  <th>Dibuat</th>
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
                  <th>Nama</th>
                  <th>Email</th>
                  <th>Telepon</th>
                  <th>Saldo</th>
                  <th>Poin</th>
                  <th>Dibuat</th>
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
<div class="modal fade" id="status-modal">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title">Ganti Status Pelanggan</h4>
	  </div>
	  <div class="modal-body">
		<div class="form-group">
			<label>Status</label>
			<select id="status_select" class="form-control" value="1">
				<?php 
					foreach($list_active_status as $val)
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
				url : base_url+"admin/customer/get_list"
			},
			'columns': [
				{ data: 'id' },
				{ data: 'img_profile' },
				{ data: 'fullname' },
				{ data: 'email' },
				{ data: 'phone' },
				{ data: 'balance' },
				{ data: 'point' },
				{ data: 'member_since' },
				{ data: 'status_name' },
				{ data: null },
			],
			"columnDefs": [
				{ 
					targets: [0,1,2,3,4,5,6,7,8,9], //first column / numbering column
					orderable: false, //set not orderable
				},
				{
					render : function ( data, type, row ) {
						if(data)
							return '<img width="72px" src="'+base_url+'data/customers/profile/'+data +'"/>';
						else
							return "<img width='120' src='"+base_url+"data/default/no_image.png'></img>";
					},
					targets: 1
				},
				{
					render : function ( data, type, row ) {
						return 	"<a href='"+base_url+"admin/customer/detail/"+row['id']+"' class='btn btn-primary btn-sm'>Detail</a><a href='#' class='btn btn-warning btn-sm' data-target='#status-modal' data-toggle='modal' data-id="+row['id']+" data-status="+row['status']+">Ganti Status</a>";
					},
					targets: 9
				},
			]
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
				url: base_url+"admin/customer/change_status/"+current_id,
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
	});
</script>