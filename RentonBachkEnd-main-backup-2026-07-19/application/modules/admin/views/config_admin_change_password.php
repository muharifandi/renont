  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Konfigurasi
        <small>Ganti Sandi Admin</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Konfigurasi</a></li>
        <li class="active">Ganti Sandi Admin</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-6">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Ganti Sandi Admin</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			<div><span style="color:red;"><?php echo $message;?></span></div>
				<form role="form" method="post" action="<?php echo base_url().'admin/config/change_password_admin/'.$user_id['value'];?>" enctype="multipart/form-data">
				<div class="box-body">
					<div class="form-group">
					  <label>Sandi Lama *</label>
					  <input type="password" class="form-control" name="old" placeholder="Sandi Lama" value="<?php echo $old_password['value'];?>">
					</div>
					<div class="form-group">
					  <label>Sandi Baru *</label>
					  <input type="password" class="form-control" name="new" placeholder="Sandi Baru" value="<?php echo $new_password['value'];?>">
					</div>
					<div class="form-group">
					  <label>Konfirmasi Sandi Baru *</label>
					  <input type="password" class="form-control" name="new_confirm" placeholder="Konfirmasi Sandi" value="<?php echo $new_password_confirm['value'];?>">
					</div>
				</div>
					<!-- /.box-body -->

					<div class="box-footer">
					<button type="submit" class="btn btn-primary">Tambah</button>
					</div>
				</form>
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