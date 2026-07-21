  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Rental Kendaraan
        <small>List Transaksi Promosi</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Rental Kendaraan</a></li>
        <li class="active">List Transaksi Promosi</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">List Transaksi Promosi</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
				<div class="table-responsive">
				  <table id="list" class="table table-bordered table-hover" width="100%">
					<thead>
					<tr>
					  <th>ID</th>
					  <th></th>
					  <th>Mitra</th>
					  <th>Pelanggan</th>
					  <th>Judul Kendaraan</th>
					  <th>Harga Per Hari</th>
					  <th>Tanggal Promosi</th>
					  <th>Total Biaya</th>
					  <th>Dana Dikembalikan</th>
					  <th>Ditampilkan</th>
					  <th>Status</th>
					  <th>Tanggal Transaksi</th>
					  <th></th>
					</tr>
					</thead>
					<tbody>
					
					</tbody>
					<tfoot>
					<tr>
					  <th>ID</th>
					  <th></th>
					  <th>Mitra</th>
					  <th>Pelanggan</th>
					  <th>Judul Kendaraan</th>
					  <th>Harga Per Hari</th>
					  <th>Tanggal Promosi</th>
					  <th>Total Biaya</th>
					  <th>Dana Dikembalikan</th>
					  <th>Ditampilkan</th>
					  <th>Status</th>
					  <th>Tanggal Transaksi</th>
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
<div class="modal fade" id="cancel-modal">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title">Batalkan Transaksi</h4>
	  </div>
	  <div class="modal-body">
			Dana akan dikembalikan ke saldo sesuai perhitungan sisa masa tayang.<br>
			<b>Apakan anda ingin membatalkan iklan ini?</b>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
		<button id="cancel_transaction" class="btn btn-danger">Proses</a>
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
			"order": [],
			
			"ajax": {
				method : 'GET',
				url : base_url+"admin/rentVehicle/get_list_promote_vehicle_transaction"
			},
			'columns': [
				{ data: 'id' },
				{ data: 'img' },
				{ data: 'company_name' },
				{ data: 'customer_name' },
				{ data: 'vehicle_title' },
				{ data: 'price_per_day' },
				{ data: null },
				{ data: 'total_payment' },
				{ data: 'canceled_total_return' },
				{ data: 'viewer' },
				{ data: 'status_name' },
				{ data: 'date_added' },
				{ data: null },
			],
			"columnDefs": [
				{ 
					targets: [0,1,2,3,4,5,6,7,8,9,10,11,12], //first column / numbering column
					orderable: false, //set not orderable
				},
				{
					render : function ( data, type, row ) {
						if(data)
							return '<center><img width="72px" src="'+base_url+'data/vehicles/'+data +'"/></center>';
						else
							return "<center><img width='120' src='"+base_url+"data/default/no_image.png'></img></center>";
					},
					targets: 1
				},
				{
					render : function ( data, type, row ) {
						var formatter = new Intl.NumberFormat('id', {
						  style: 'currency',
						  currency: 'IDR',
						});

						return formatter.format(data);
					},
					targets: 5
				},
				{
					render : function ( data, type, row ) {
						var result = "<center>"+row['start_date']+" - "+row['end_date']+"</center>";
						return result;
					},
					targets: 6
				},
				{
					render : function ( data, type, row ) {
						var formatter = new Intl.NumberFormat('id', {
						  style: 'currency',
						  currency: 'IDR',
						});

						return formatter.format(data);
					},
					targets: 7
				},
				{
					render : function ( data, type, row ) {
						var formatter = new Intl.NumberFormat('id', {
						  style: 'currency',
						  currency: 'IDR',
						});

						return formatter.format(data);
					},
					targets: 8
				},
				{
					render : function ( data, type, row ) {
						
						var buttonset = "";
						if(row['status'] == 0 || row['status'] == 1)
						{
							/**
							buttonset += "<a href='#' class='btn btn-danger btn-sm' data-target='#cancel-modal' data-toggle='modal' data-id="+row['id']+">Batalkan</a>";
							**/
						}

						return 	buttonset;
					},
					targets: 12
				},
			]
		});
		
		$('#cancel-modal').on('show.bs.modal', function (event) {
			current_id = $(event.relatedTarget).data('id');
		});
		
		$('#cancel_transaction').click(function() {
			var status_selected = $('#status_select').val();
			$.ajax({
				method: 'post',
				url: base_url+"admin/rentVehicle/cancel_promote_vehicle_transaction/",
				cache: false,
				data :{
					id : current_id
				},
				dataType: 'json',
				success: function(data){
					if(data.status)
					{
						alert( data.message );
						table.ajax.reload();
						$('#cancel-modal').modal('hide');
					}else
					{
						alert( data.message );
					}
				}
			});
		});
		
	});
</script>