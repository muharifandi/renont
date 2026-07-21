  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Konfigurasi
        <small>Ubah Admin</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Konfigurasi</a></li>
        <li class="active">Ubah Admin</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-6">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Ubah Admin</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			<div><span style="color:red;"><?php echo $message;?></span></div>
				<form role="form" method="post" action="<?php echo base_url().'admin/config/edit_admin/'.$user->id;?>" enctype="multipart/form-data">
				<div class="box-body">
					
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
					
					  <label>Akses</label>
					  <?php if ($this->ion_auth->is_admin()): ?>

						  <?php foreach ($groups as $group):?>
							<?php 
								$gID=$group['id'];
								if($gID != 4 && $gID != 5 && $gID != 7) { 
							?>
								  <div class="checkbox">
									  <label class="checkbox">
									  <?php
										  $checked = null;
										  $item = null;
										  foreach($currentGroups as $grp) {
											  if ($gID == $grp->id) {
												  $checked= ' checked="checked"';
											  break;
											  }
										  }
									  ?>
									  
									 
									  <input type="checkbox" name="groups[]" value="<?php echo $group['id'];?>"<?php echo $checked;?>>
									  <?php echo htmlspecialchars($group['description'],ENT_QUOTES,'UTF-8');?>
									  </label>
									</div>
							<?php } ?>
						  <?php endforeach?>

					  <?php endif ?>
						<?php echo form_hidden('id', $user->id);?>
						<?php echo form_hidden($csrf); ?>
					
				</div>
					<!-- /.box-body -->

					<div class="box-footer">
					<button type="submit" class="btn btn-primary">Ubah</button>
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