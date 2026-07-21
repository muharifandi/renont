  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Mitra
        <small>List Komisi</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Mitra</a></li>
        <li class="active">List Komisi</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">List Komisi</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
                <thead>
                <tr>
                  <th>ID</th>
                  <th></th>
                  <th>Komisi</th>
                  <th>Persentase</th>
                  <th>Deskripsi</th>
                  <th>Mitra</th>
                  <th>Transaksi</th>
                  <th>Total Pembayaran</th>
                  <th>Biaya Tambahan</th>
                  <th>Biaya Administrasi</th>
                  <th>Status</th>
                  <th>Tanggal</th>
                </tr>
                </thead>
                <tbody>
                
                </tbody>
                <tfoot>
                <tr>
                  <th>ID</th>
                  <th></th>
                  <th>Komisi</th>
                  <th>Persentase</th>
                  <th>Deskripsi</th>
                  <th>Mitra</th>
                  <th>Transaksi</th>
                  <th>Total Pembayaran</th>
                  <th>Biaya Tambahan</th>
                  <th>Biaya Administrasi</th>
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
				url : base_url+"agent/partner/get_list_commission"
			},
			'columns': [
				{ data: 'id' },
				{ data: 'thumb_image' },
				{ data: 'value' },
				{ data: 'percentage' },
				{ data: 'description' },
				{ data: 'company_name' },
				{ data: 'title' },
				{ data: 'total_payment' },
				{ data: 'total_fee' },
				{ data: 'admin_fee' },
				{ data: 'status_name' },
				{ data: 'date_added' },
			],
			"columnDefs": [
				{
					render : function ( data, type, row ) {
						if(data)
							return '<img width="72px" src="'+data +'"/>';
						else
							return "<img width='120' src='"+base_url+"data/default/no_image.png'></img>";
					},
					targets: 1
				},
			]
		});
	});
</script>