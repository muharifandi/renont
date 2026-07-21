  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Marketing
        <small><?php if($edit){ ?>Ubah<?php }else{?>Tambah<?php }?></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Marketing</a></li>
        <li class="active"><?php if($edit){ ?>Ubah<?php }else{?>Tambah<?php }?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-6">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title"><?php if($edit){ ?>Ubah<?php }else{?>Tambah<?php }?> Marketing</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			<center><span style=" color:red;"><?php echo $message;?></span></center>
				<form role="form" method="post" action="<?php echo ($edit)?base_url().'admin/agent/edit_agent':base_url().'admin/agent/add_agent';?>" enctype="multipart/form-data">
				<div class="box-body">
					<?php if($edit){ ?>
						<input type="hidden" class="form-control" name="id" value="<?php echo ($edit)?$agent->id:"";?>">
					<?php } ?>
					<div class="form-group">
					  <label>Email/Alamat Surel *</label>
					  <input <?php echo ($edit)?"disabled='true'":"";?> type="text" class="form-control" name="email" placeholder="contoh : dani@rentone.com" value="<?php echo ($agent->email)?$agent->email:"";?>">
					</div>
					<div class="form-group">
					  <label>Nama Depan *</label>
					  <input type="text" class="form-control" name="first_name" placeholder="Nama Depan" value="<?php echo ($agent->first_name)?$agent->first_name:"";?>">
					</div>
					<div class="form-group">
					  <label>Nama Belakang *</label>
					  <input type="text" class="form-control" name="last_name" placeholder="Nama Belakang" value="<?php echo ($agent->last_name)?$agent->last_name:"";?>">
					</div>
					<div class="form-group">
					  <label>Telepon</label>
					  <input type="text" class="form-control" name="phone" placeholder="contoh: 0895XXXXXXXX" value="<?php echo ($agent->phone)?$agent->phone:"";?>">
					</div>
					
					
					<div class="form-group">
					  <label>Sandi *</label>
					  <input type="password" class="form-control" name="password" placeholder="Sandi" value="">
					</div>
					<div class="form-group">
					  <label>Konfirmasi Sandi *</label>
					  <input type="password" class="form-control" name="password_confirm" placeholder="Konfirmasi Sandi" value="">
					</div>
					
				
					<div class="form-group">
						<label>Lokasi *</label>
						<select id="regencies_select" class="form-control" name="regencies_id" style="width: 100%;">
							<?php if($agent->regencies_id){ ?>
									 <option value="<?php echo $agent->regencies_id;?>" selected="selected"><?php echo $agent->regencies_name;?></option>
							<?php } ?>
						</select>
					</div>
					
					<div class="form-group">
					  <label>Alamat</label>
					  <textarea type="text" class="form-control" name="address" placeholder="Jl. ABC No 123 Perumahan A, Kelurahan B, Kecamatan C, Kota D Kode Pos 4321"><?php echo ($agent->address)?$agent->address:"";?></textarea>
					</div>
					
					<div class="form-group">
					  <label for="exampleInputFile">Upload Foto Profil</label>
					  <input id="img_profile_select" type="file" name="img_profile">

					  <p class="help-block"></p>
					  <input type="hidden" class="form-control" name="img_profile_filename" value="<?php echo ($agent->img_profile)?$agent->img_profile:"";?>">
					  <center><img id="image_profile_preview" src="<?php echo ($agent->img_profile)?base_url()."data/agents/profile/".$agent->img_profile:"";?>" width="50%" alt="Tidak ada foto dipilih" class="img-thumbnail"></center>
					</div>
					
					<div class="form-group">
					  <label>No KTP *</label>
					  <input type="text" class="form-control" name="identity_number" placeholder="contoh: 1234567890" value="<?php echo ($agent->identity_number)?$agent->identity_number:"";?>">
					</div>
					
					<div class="form-group">
					  <label for="exampleInputFile">Upload Foto Identitas (KTP/SIM)</label>
					  <input id="img_identity_select" type="file" name="img_identity">

					  <p class="help-block"></p>
					  <input type="hidden" class="form-control" name="img_identity_filename" value="<?php echo ($agent->img_identity)?$agent->img_identity:"";?>">
					  <center><img id="image_identity_preview" src="<?php echo ($agent->img_identity)?base_url()."data/agents/files/identity/".$agent->img_identity:"";?>" width="50%" alt="Tidak ada foto dipilih" class="img-thumbnail"></center>
					</div>
					
					
					
					<!-- /.box-body -->

					<div class="box-footer">
					<button type="submit" class="btn btn-primary">Submit</button>
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
<script>
	$(document).ready(function(){
		$("#img_profile_select").change(function() {
		  readURL(this,$('#image_profile_preview'));
		});
		
		$("#img_identity_select").change(function() {
		  readURL(this,$('#image_identity_preview'));
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
			url: base_url+"admin/config/get_regencies_select",
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
			if($agent->regencies_id)
			{
				?>
				$('#regencies_select').select2('data', {id: <?php echo "'".$agent->regencies_id."'";?>, text: <?php echo "'".$agent->regencies_name."'";?>, name: <?php echo "'".$agent->regencies_name."'";?>}).change();
				
				//$("#mySelect2").select2("val", <?php echo "'".$agent->regencies_id."'";?>).change();
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