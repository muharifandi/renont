  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Konfigurasi
        <small>Dasar</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Konfigurasi</a></li>
        <li class="active">Dasar</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-8">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Konfigurasi Dasar</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
				<div class="panel panel-info">
					<div class="panel-heading">Maintenance / Pemeliharaan Sistem</div>
					<div class="panel-body">	
						<form class="form-horizontal" id="filter" name="filter" method="post" action="<?php echo base_url()."admin/config/set_config/"?>">
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Aktifkan Pemeliharaan :</label>
								<div class="col-sm-4">
									<select id="maintenance" name="maintenance" class="form-control" value="-1">
										<option value="1" <?= ($config_data['maintenance'] == 1)?"selected":""?>> Ya </option>
										<option value="0" <?= ($config_data['maintenance'] == 0)?"selected":""?>> Tidak </option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Pesan Pemeliharaan :</label>
								<div class="col-sm-4">
									<textarea class="form-control" name="maintenance_message"><?php echo $config_data['maintenance_message'];?></textarea>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-4 col-sm-2">
									<input class="btn btn-sm btn-primary" type="submit" value="Simpan" onclick="">
								</div>
							</div>
						</form>
					</div>
				</div>
				
				<div class="panel panel-info">
					<div class="panel-heading">Versi Aplikasi Android</div>
					<div class="panel-body">	
						<form class="form-horizontal" id="filter" name="filter" method="post" action="<?php echo base_url()."admin/config/set_config/"?>">
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Kode Versi :</label>
								<div class="col-sm-4">
									<input class="form-control" name="android_app_version_code" value="<?php echo $config_data['android_app_version_code'];?>">
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Nama Versi :</label>
								<div class="col-sm-4">
									<input class="form-control" name="android_app_version_name" value="<?php echo $config_data['android_app_version_name'];?>">
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Link Aplikasi :</label>
								<div class="col-sm-8">
									<input class="form-control" name="android_app_update_link" value="<?php echo $config_data['android_app_update_link'];?>">
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-4 col-sm-2">
									<input class="btn btn-sm btn-primary" type="submit" value="Simpan" onclick="">
								</div>
							</div>
						</form>
					</div>
				</div>
				
				<div class="panel panel-info">
					<div class="panel-heading">Header Laporan</div>
					<div class="panel-body">	
						<form class="form-horizontal" id="filter" name="filter" method="post" action="<?php echo base_url()."admin/config/set_config/"?>">
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Judul Kepala:</label>
								<div class="col-sm-8">
									<input class="form-control" name="report_title" value="<?php echo $config_data['report_title'];?>">
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Deskripsi Kepala :</label>
								<div class="col-sm-8">
									<textarea class="form-control" name="report_description"><?php echo $config_data['report_description'];?></textarea>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-4 col-sm-2">
									<input class="btn btn-sm btn-primary" type="submit" value="Simpan" onclick="">
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="panel panel-info">
					<div class="panel-heading">Promosi Rental Kendaraan</div>
					<div class="panel-body">	
						<form class="form-horizontal" id="filter" name="filter" method="post" action="<?php echo base_url()."admin/config/set_config/"?>">
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Harga per Hari:</label>
								<div class="col-sm-4">
									<input class="form-control" name="promote_price_per_day_rent_vehicle" value="<?php echo $config_data['promote_price_per_day_rent_vehicle'];?>">
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Jumlah ditampilkan :</label>
								<div class="col-sm-4">
									<input type="number" class="form-control" name="promote_max_rent_vehicle" value="<?php echo $config_data['promote_max_rent_vehicle'];?>">
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Pesan :</label>
								<div class="col-sm-8">
									<textarea class="form-control" name="promote_info_rent_vehicle"><?php echo $config_data['promote_info_rent_vehicle'];?></textarea>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-4 col-sm-2">
									<input class="btn btn-sm btn-primary" type="submit" value="Simpan" onclick="">
								</div>
							</div>
						</form>
					</div>
				</div>
				
				<div class="panel panel-info">
					<div class="panel-heading">Biaya Admin</div>
					<div class="panel-body">	
						<form class="form-horizontal" id="filter" name="filter" method="post" action="<?php echo base_url()."admin/config/set_config/"?>">
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Gunakan Persentase untuk Biaya Admin :</label>
								<div class="col-sm-4">
									<select id="admin_fee_use_percentage" name="admin_fee_use_percentage" class="form-control" value="-1">
										<option value="1" <?= ($config_data['admin_fee_use_percentage'] == 1)?"selected":""?>> Ya </option>
										<option value="0" <?= ($config_data['admin_fee_use_percentage'] == 0)?"selected":""?>> Tidak </option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Nilai :</label>
								<div class="col-sm-4">
									<input class="form-control" name="admin_fee" value="<?php echo $config_data['admin_fee'];?>">
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-4 col-sm-2">
									<input class="btn btn-sm btn-primary" type="submit" value="Simpan" onclick="">
								</div>
							</div>
						</form>
					</div>
				</div>
				
				<div class="panel panel-info">
					<div class="panel-heading">Reward</div>
					<div class="panel-body">	
						<form class="form-horizontal" id="filter" name="filter" method="post" action="<?php echo base_url()."admin/config/set_config/"?>">
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Referal Poin Reward Mitra :</label>
								<div class="col-sm-2">
									<input class="form-control" name="referal_point_reward_partner" value="<?php echo $config_data['referal_point_reward_partner'];?>">
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Transaksi Poin Reward Mitra :</label>
								<div class="col-sm-2">
									<input class="form-control" name="transaction_point_reward_partner" value="<?php echo $config_data['transaction_point_reward_partner'];?>">
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Referal Poin Reward Pelanggan :</label>
								<div class="col-sm-2">
									<input class="form-control" name="referal_point_reward_customer" value="<?php echo $config_data['referal_point_reward_customer'];?>">
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Transaksi Poin Reward Pelanggan :</label>
								<div class="col-sm-2">
									<input class="form-control" name="transaction_point_reward_customer" value="<?php echo $config_data['transaction_point_reward_customer'];?>">
								</div>
							</div>
							
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Minimum Penukaran Poin :</label>
								<div class="col-sm-2">
									<input class="form-control" name="exchange_point_minimum" value="<?php echo $config_data['exchange_point_minimum'];?>">
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Saldo Penukaran per Poin :</label>
								<div class="col-sm-4">
									<input class="form-control" name="rate_point_to_balance" value="<?php echo $config_data['rate_point_to_balance'];?>">
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-4 col-sm-2">
									<input class="btn btn-sm btn-primary" type="submit" value="Simpan" onclick="">
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="panel panel-info">
					<div class="panel-heading">Pengisian dan Pencairan Saldo</div>
					<div class="panel-body">	
						<form class="form-horizontal" id="filter" name="filter" method="post" action="<?php echo base_url()."admin/config/set_config/"?>">
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Minimum Topup :</label>
								<div class="col-sm-4">
									<input class="form-control" name="topup_minimum" value="<?php echo $config_data['topup_minimum'];?>">
								</div>
							</div>
							
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Minimum Pencairan :</label>
								<div class="col-sm-4">
									<input class="form-control" name="withdraw_minimum" value="<?php echo $config_data['withdraw_minimum'];?>">
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-4 col-sm-2">
									<input class="btn btn-sm btn-primary" type="submit" value="Simpan" onclick="">
								</div>
							</div>
						</form>
					</div>
				</div>
				
				<div class="panel panel-info">
					<div class="panel-heading">Rental Kendaraan</div>
					<div class="panel-body">	
						<form class="form-horizontal" id="filter" name="filter" method="post" action="<?php echo base_url()."admin/config/set_config/"?>">
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Jarak Pelanggan dengan produk (Km) di Rekomendasi:</label>
								<div class="col-sm-4">
									<input class="form-control" name="distance_recomendation_rentvehicle" value="<?php echo $config_data['distance_recomendation_rentvehicle'];?>">
								</div>
							</div>
							<div class="form-group">
								<label for="inputEmail3" class="col-sm-4 control-label">Jarak Pelanggan dengan produk (Km) di Pencarian:</label>
								<div class="col-sm-4">
									<input class="form-control" name="distance_max_rentvehicle" value="<?php echo $config_data['distance_max_rentvehicle'];?>">
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-4 col-sm-2">
									<input class="btn btn-sm btn-primary" type="submit" value="Simpan" onclick="">
								</div>
							</div>
						</form>
					</div>
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