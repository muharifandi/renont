<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
		<h1>
			Konfigurasi
			<small>List Admin</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="#">Konfigurasi</a></li>
			<li class="active">List Admin</li>
		</ol>
	</section>
	
    <!-- Main content -->
    <section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">List Admin</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<div class="form-group">
							<div><span style="color:green;"><?php echo $message;?></span></div>
							</div><div class="form-group">
							<a href="<?php echo base_url().'admin/config/add_admin/';?>" class="btn btn-sm btn-primary">Tambah Admin</a>
						</div>
						<div class="table-responsive">
							<table id="list" class="table table-bordered table-hover" width="100%">
								<thead>
									<tr>
										<th>ID</th>
										<th>Email</th>
										<th>Nama</th>
										<th>Akses</th>
										<th>Aktif</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php
										foreach($users as $val)
										{
										?>
										<tr>
											<td><?php echo $val->id;?></td>
											<td><?php echo $val->email;?></td>
											<td><?php echo $val->first_name." ".$val->last_name;?></td>
											<td><?php echo $val->groups;?></td>
											<td><?php echo ($val->active ==1)?"Ya":"Tidak";?></td>
											<td><center>
												<a class="btn btn-primary" href="<?php echo base_url().'admin/config/edit_admin/'.$val->id;?>">Ubah</a>
												<a class="btn btn-warning" href="<?php echo base_url().'admin/config/change_password_admin/'.$val->id;?>">Ganti Password</a>
												<?php if($val->active == 1)
													{
													?>
													<a class="btn btn-danger" href="<?php echo base_url().'admin/config/deactivate_admin/'.$val->id.'/0';?>">Nonaktifkan</a>
													<?php	}else
													{
													?>
													<a class="btn btn-primary" href="<?php echo base_url().'admin/config/activate_admin/'.$val->id.'/1';?>">Aktifkan</a>
												<?php	} ?>
												<a class="btn btn-danger" href="<?php echo base_url().'admin/config/delete_admin/'.$val->id;?>" onclick="return confirm('Apa kamu yakin ingin menghapus akun <?php echo $val->email;?>')">Hapus</a>
											</center></td>
										</tr>
										<?php
										}?>
								</tbody>
								<tfoot>
									<tr>
										<th>ID</th>
										<th>Email</th>
										<th>Nama</th>
										<th>Aktif</th>
										<th>Akses</th>
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