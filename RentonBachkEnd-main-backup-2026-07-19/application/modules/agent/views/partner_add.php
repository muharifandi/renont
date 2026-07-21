  <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 480px;
      }
     
    </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Mitra
        <small><?php if($edit){ ?>Ubah<?php }else{?>Tambah<?php }?></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Mitra</a></li>
        <li class="active"><?php if($edit){ ?>Ubah<?php }else{?>Tambah<?php }?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-sm-6 col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title"><?php if($edit){ ?>Ubah<?php }else{?>Tambah<?php }?> Mitra</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			<center><span style=" color:red;"><?php echo $message;?></span></center>
				<form role="form" method="post" action="<?php echo ($edit)?base_url().'agent/partner/edit_partner':base_url().'agent/partner/add_partner';?>" enctype="multipart/form-data">
				<div class="box-body">
					<?php if($edit){ ?>
						<input type="hidden" class="form-control" name="id" value="<?php echo ($edit)?$partner->id:"";?>">
					<?php } ?>
					<div class="form-group">
					  <label>Email/Alamat Surel *</label>
					  <input <?php echo ($edit)?"disabled='true'":"";?> type="text" class="form-control" name="email" placeholder="contoh : dani@rentone.com" value="<?php echo ($partner->email)?$partner->email:"";?>">
					</div>
					<div class="form-group">
					  <label>Nama Depan *</label>
					  <input type="text" class="form-control" name="first_name" placeholder="Nama Depan" value="<?php echo ($partner->first_name)?$partner->first_name:"";?>">
					</div>
					<div class="form-group">
					  <label>Nama Belakang *</label>
					  <input type="text" class="form-control" name="last_name" placeholder="Nama Belakang" value="<?php echo ($partner->last_name)?$partner->last_name:"";?>">
					</div>
					<div class="form-group">
					  <label>Telepon</label>
					  <input type="text" class="form-control" name="phone" placeholder="contoh: 0895XXXXXXXX" value="<?php echo ($partner->phone)?$partner->phone:"";?>">
					</div>
					
					<?php if(!$edit) { ?>
					<div class="form-group">
					  <label>Sandi *</label>
					  <input type="password" class="form-control" name="password" placeholder="Sandi" value="">
					</div>
					<div class="form-group">
					  <label>Konfirmasi Sandi *</label>
					  <input type="password" class="form-control" name="password_confirm" placeholder="Konfirmasi Sandi" value="">
					</div>
					
					<?php } ?>
					
					<div class="form-group">
					  <label for="exampleInputFile">Upload Foto Profil</label>
					  <input id="img_profile_select" type="file" name="img_profile">

					  <p class="help-block"></p>
					  <input type="hidden" class="form-control" name="img_profile_filename" value="<?php echo ($partner->img_profile)?$partner->img_profile:"";?>">
					  <center><img id="image_profile_preview" src="<?php echo ($partner->img_profile)?base_url()."data/customers/profile/".$partner->img_profile:"";?>" width="50%" alt="Tidak ada foto dipilih" class="img-thumbnail"></center>
					</div>
					
					<div class="form-group">
					  <label>No KTP *</label>
					  <input type="text" class="form-control" name="identity_number" placeholder="contoh: 1234567890" value="<?php echo ($partner->identity_number)?$partner->identity_number:"";?>">
					</div>
					
					<div class="form-group">
					  <label for="exampleInputFile">Upload Foto Identitas (KTP/SIM)</label>
					  <input id="img_identity_select" type="file" name="img_identity">

					  <p class="help-block"></p>
					  <input type="hidden" class="form-control" name="img_identity_filename" value="<?php echo ($partner->img_identity)?$partner->img_identity:"";?>">
					  <center><img id="image_identity_preview" src="<?php echo ($partner->img_identity)?base_url()."data/customers/files/identity/".$partner->img_identity:"";?>" width="50%" alt="Tidak ada foto dipilih" class="img-thumbnail"></center>
					</div>
					
					<div class="form-group">
					  <label>Nama Perusahaan *</label>
					  <input type="text" class="form-control" name="company_name" placeholder="" value="<?php echo ($partner->company_name)?$partner->company_name:"";?>">
					</div>
					
					<div class="form-group">
					  <label>Deskripsi</label>
					  <textarea type="text" class="form-control" name="description" placeholder="misal : 'kami adalah perusahaan yang bergerak di bidang industri rental mobil khusus merek toyota'"><?php echo ($partner->description)?$partner->description:"";?></textarea>
					</div>
					
					<div class="form-group">
					  <label>Kepemilikan *</label>
					  <select class="form-control" name="ownership_id">
						<option value="1" <?php if($partner->ownership_id == 1) echo "selected";?>>Perusahaan</option>
						<option value="2" <?php if($partner->ownership_id == 2) echo "selected";?>>Personal</option>
						</select>
					</div>
					
					<div class="form-group">
					  <label>Nomor Pajak</label>
					  <input type="text" class="form-control" name="tax_number" placeholder="ex:123 123" value="<?php echo ($partner->tax_number)?$partner->tax_number:"";?>">
					</div>
					<!-- /.box-body -->
					
					<div class="form-group">
					  <label for="exampleInputFile">Upload Foto Profil Mitra *</label>
					  <input id="img_profile_partner_select" type="file" name="img_profile_partner">

					  <p class="help-block"></p>
					  <input type="hidden" class="form-control" name="img_profile_partner_filename" value="<?php echo ($partner->img_profile_partner)?$partner->img_profile_partner:"";?>">
					  <center><img id="img_profile_partner_preview" src="<?php echo ($partner->img_profile_partner)?base_url()."data/partners/profile/".$partner->img_profile_partner:"";?>" width="50%" alt="Tidak ada foto dipilih" class="img-thumbnail"></center>
					</div>
					
					<div class="form-group">
						<label>Lokasi *</label>
						<select id="regencies_select" class="form-control" name="regencies_id" style="width: 100%;">
							<?php if($partner->regencies_id){ ?>
									 <option value="<?php echo $partner->regencies_id;?>" selected="selected"><?php echo $partner->regencies_name;?></option>
							<?php } ?>
						</select>
					</div>
					
					<div class="form-group">
					  <label>Alamat</label>
					  <textarea type="text" class="form-control" name="address" placeholder="Jl. ABC No 123 Perumahan A, Kelurahan B, Kecamatan C, Kota D Kode Pos 4321"><?php echo ($partner->address)?$partner->address:"";?></textarea>
					</div>
					<div class="form-group">
					  <label>Lokasi Bisnis</label>
						<input readonly="true" type="text" class="form-control" name="latitude" value="<?php echo ($partner->latitude)?$partner->latitude:"";?>"/>
						<input readonly="true" type="text" class="form-control" name="longitude" value="<?php echo ($partner->longitude)?$partner->longitude:"";?>"/>
						<div id="map"></div>
						
					</div>
					
					<div class="form-group">
					  <label for="exampleInputFile">Upload Foto SIM</label>
					  <input id="img_driver_licence_select" type="file" name="img_driver_licence">

					  <p class="help-block"></p>
					  <input type="hidden" class="form-control" name="img_driver_licence_filename" value="<?php echo ($partner->img_driver_licence)?$partner->img_driver_licence:"";?>">
					  <center><img id="img_driver_licence_preview" src="<?php echo ($partner->img_driver_licence)?base_url()."data/partners/files/driver_licence/".$partner->img_driver_licence:"";?>" width="50%" alt="Tidak ada foto dipilih" class="img-thumbnail"></center>
					</div>
					
					<div class="form-group">
					  <label for="exampleInputFile">Upload Foto Ijin Usaha *</label>
					  <input id="img_bussiness_licence_select" type="file" name="img_bussiness_licence">

					  <p class="help-block"></p>
					  <input type="hidden" class="form-control" name="img_bussiness_licence_filename" value="<?php echo ($partner->img_bussiness_licence)?$partner->img_bussiness_licence:"";?>">
					  <center><img id="img_bussiness_licence_preview" src="<?php echo ($partner->img_bussiness_licence)?base_url()."data/partners/files/bussiness_licence/".$partner->img_bussiness_licence:"";?>" width="50%" alt="Tidak ada foto dipilih" class="img-thumbnail"></center>
					</div>
					<div class="form-group">
					  <label for="exampleInputFile">Upload Foto Registrasi Usaha *</label>
					  <input id="img_bussiness_registration_select" type="file" name="img_bussiness_registration">

					  <p class="help-block"></p>
					  <input type="hidden" class="form-control" name="img_bussiness_registration_filename" value="<?php echo ($partner->img_bussiness_registration)?$partner->img_bussiness_registration:"";?>">
					  <center><img id="img_bussiness_registration_preview" src="<?php echo ($partner->img_bussiness_registration)?base_url()."data/partners/files/bussiness_registration/".$partner->img_bussiness_registration:"";?>" width="50%" alt="Tidak ada foto dipilih" class="img-thumbnail"></center>
					</div>
					
					<div class="box-footer">
					<button type="submit" class="btn btn-primary">Simpan</button>
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
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=<?php echo $map_key;?>&callback=">
</script>
<script src="https://unpkg.com/location-picker/dist/location-picker.min.js"></script>
<script>
	
	
</script>
<script>
	$(document).ready(function(){
		
		var lp = new locationPicker('map', {
				setCurrentPosition: true, // You can omit this, defaults to true
				lat: <?php echo ($partner->latitude)?$partner->latitude:"-3.791186";?>,
				lng: <?php echo ($partner->longitude)?$partner->longitude:"119.649873";?>,
			  }, {
				zoom: 12 // You can set any google map options here, zoom defaults to 15
			  });
			  
			  google.maps.event.addListener(lp.map, 'idle', function (event) {
				// Get current location and show it in HTML
				var location = lp.getMarkerPosition();
				$("input[name='latitude']").val(location.lat);
				$("input[name='longitude']").val(location.lng);
				//onIdlePositionView.innerHTML = 'The chosen location is ' + location.lat + ',' + location.lng;
				});
				
		$("#img_profile_select").change(function() {
		  readURL(this,$('#image_profile_preview'));
		});
		
		$("#img_identity_select").change(function() {
		  readURL(this,$('#image_identity_preview'));
		});
		
		$("#img_profile_partner_select").change(function() {
		  readURL(this,$('#img_profile_partner_preview'));
		});
		
		$("#img_driver_licence_select").change(function() {
		  readURL(this,$('#img_driver_licence_preview'));
		});
		
		$("#img_bussiness_licence_select").change(function() {
		  readURL(this,$('#img_bussiness_licence_preview'));
		});
		
		$("#img_bussiness_registration_select").change(function() {
		  readURL(this,$('#img_bussiness_registration_preview'));
		});
		
		function readURL(input,preview) {
		  if (input.files && input.files[0]) {
			var reader = new FileReader();
			
			reader.onload = function(e) {
			  preview.attr('src', e.target.result);
			}
			
			reader.readAsDataURL(input.files[0]);
		  }
		}
		
		
		$('#regencies_select').select2({
		  ajax: {
			url: base_url+"agent/config/get_regencies_select",
			type: 'GET',
			dataType: 'json',
			delay: 250,
			data: function (params) {
			  return {
				search: params.term, // search term
				page: params.page
			  };
			},
			processResults: function (data, params) {
			  // parse the results into the format expected by Select2
			  // since we are using custom formatting functions we do not need to
			  // alter the remote JSON data, except to indicate that infinite
			  // scrolling can be used
			  params.page = params.page || 1;

			  return {
				results: data.items,
				pagination: {
				  more: (params.page * 30) < data.total_count
				}
			  };
			},
			cache: true
		  },
		  placeholder: 'Pilih Area',
		  escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
		  minimumInputLength: 1,
		  templateResult: formatRepo,
		  templateSelection: formatRepoSelection,
		});
		/*
		<?php
			if($partner->regencies_id)
			{
				?>
				$('#regencies_select').select2('data', {id: <?php echo "'".$partner->regencies_id."'";?>, text: <?php echo "'".$partner->regencies_name."'";?>, name: <?php echo "'".$partner->regencies_name."'";?>}).change();
				
				//$("#mySelect2").select2("val", <?php echo "'".$partner->regencies_id."'";?>).change();
				<?php
			}
		?>
		 */
		function formatRepo (repo) {
			if (repo.loading) {
			return repo.id;
			}

			var $container = $(
			"<div>" +
			  "<div><label><b>" + repo.id + "</b></label> - <span style='font-size:14px;'><i>" + repo.name + "</i></span></div>" +
			  "<div><span style='font-size:10px;'>" + repo.province + "</span></div>" +
			"</div>"
			);


			return $container;
		}

		function formatRepoSelection (repo) {
			return repo.name || repo.text;
		}
	});
</script>