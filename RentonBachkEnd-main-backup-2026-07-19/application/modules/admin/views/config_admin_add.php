  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Konfigurasi
        <small>Tambah Admin</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Konfigurasi</a></li>
        <li class="active">Tambah Admin</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-6">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Tambah Admin</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			<div><span style="color:red;"><?php echo $message;?></span></div>
				<form role="form" method="post" action="<?php echo base_url().'admin/config/admin_add';?>" enctype="multipart/form-data">
				<div class="box-body">
					
					<div class="form-group">
					  <label>Email/Alamat Surel *</label>
					  <input type="text" class="form-control" name="email" placeholder="contoh : dani@rentone.com" value="<?php echo $email['value'];?>">
					</div>
					<div class="form-group">
					  <label>Nama Depan *</label>
					  <input type="text" class="form-control" name="first_name" placeholder="Nama Depan" value="<?php echo $first_name['value'];?>">
					</div>
					<div class="form-group">
					  <label>Nama Belakang *</label>
					  <input type="text" class="form-control" name="last_name" placeholder="Nama Belakang" value="<?php echo $last_name['value'];?>">
					</div>
					<div class="form-group">
					  <label>Perusahaan</label>
					  <input type="text" class="form-control" name="company" placeholder="Perusahaan" value="<?php echo $company['value'];?>">
					</div>
					<div class="form-group">
					  <label>Telepon</label>
					  <input type="text" class="form-control" name="phone" placeholder="contoh: 0895XXXXXXXX" value="<?php echo $phone['value'];?>">
					</div>
					<div class="form-group">
					  <label>Sandi *</label>
					  <input type="password" class="form-control" name="password" placeholder="Sandi" value="<?php echo $password['value'];?>">
					</div>
					<div class="form-group">
					  <label>Konfirmasi Sandi *</label>
					  <input type="password" class="form-control" name="password_confirm" placeholder="Konfirmasi Sandi" value="<?php echo $password_confirm['value'];?>">
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