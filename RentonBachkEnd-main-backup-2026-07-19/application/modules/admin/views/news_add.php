  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        News
        <small><?php if($edit){ ?>Edit<?php }else{?>Add<?php }?></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">News</a></li>
        <li class="active"><?php if($edit){ ?>Edit<?php }else{?>Add<?php }?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-6">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">News <?php if($edit){ ?>Edit<?php }else{?>Add<?php }?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
				<form role="form" method="post" action="<?php echo ($edit)?base_url().'admin/news/edit_news':base_url().'admin/news/add_news';?>" enctype="multipart/form-data">
				<div class="box-body">
					<?php if($edit){ ?>
						<input type="hidden" class="form-control" name="id" value="<?php echo ($edit)?$news->id:"";?>">
					<?php } ?>
					<div class="form-group">
					  <label for="exampleInputFile">Upload Image for Banner</label>
					  <input id="img_select" type="file" name="img_banner">

					  <p class="help-block">Upload image with ratio 19:6</p>
					  <img id="image_preview" src="<?php echo ($edit)?(($news->img)?base_url()."data/news/".$news->img:""):"";?>" width="100%" alt="No image Selected" class="img-thumbnail">
					</div>
					<div class="form-group">
					  <label>Title</label>
					  <input type="text" class="form-control" name="title" placeholder="Title" value="<?php echo ($edit)?$news->title:"";?>">
					</div>
					<div class="form-group">
						<label>Content</label>
						<textarea id="content" name="content" rows="10" cols="80">
							<?php echo ($edit)?$news->content:"";?>
						</textarea>
					</div>
					<div class="form-group">
						<label>User Type</label>
							<select id="user_type" name="user_type" class="form-control" value="0">
								<?php 
									foreach($user_type as $val)
									{ ?>
										<option <?php echo ($edit)?(($val->id == $news->user_type)?"selected":""):"";?> value="<?php echo $val->id;?>"><?php echo $val->description;?></option>
									<?php
									}
								?>
							</select>
					</div>
					
					<div class="form-group">
						<label>For Voucher</label>
						<select id="for_voucher" name="is_voucher" class="form-control" value="0">
							<option value="0" <?php echo ($edit)?(($news->is_voucher==0)?"selected":""):"";?>>Tidak</option>
							<option value="1" <?php echo ($edit)?(($news->is_voucher==1)?"selected":""):"";?>>Ya</option>
						</select>
					</div>
					<div class="form-group">
						<label>Voucher</label>
						<select id="voucher_select" class="form-control" name="voucher_id" style="width: 100%;" <?php echo ($edit)?(($news->is_voucher==0)?"disabled='true'":""):"disabled='true'";?>>
							<?php if($edit){ ?>
									 <option value="<?php echo $news->voucher_id;?>" selected="selected"><?php echo $news->voucher_code;?></option>
							<?php } ?>
						</select>
					</div>
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
		$("#img_select").change(function() {
		  readURL(this);
		});
		
		function readURL(input) {
		  if (input.files && input.files[0]) {
			var reader = new FileReader();
			
			reader.onload = function(e) {
			  $('#image_preview').attr('src', e.target.result);
			}
			
			reader.readAsDataURL(input.files[0]);
		  }
		}

		CKEDITOR.replace('content');
		$('#for_voucher').on('change', function() {
			if(this.value == 0){
				$('#voucher_select').val(null).trigger('change');
				$('#voucher_select').attr('disabled', true);
			}else
			{
				$("#voucher_select").removeAttr('disabled');
			}
		  
		});
		
		
		$('#voucher_select').select2({
		  ajax: {
			url: base_url+"admin/voucher/get_voucher_select",
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
		  placeholder: 'Search for a Voucher',
		  escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
		  minimumInputLength: 1,
		  templateResult: formatRepo,
		  templateSelection: formatRepoSelection,
		});
		
	
		 
		function formatRepo (repo) {
			if (repo.loading) {
			return repo.code;
			}

			var $container = $(
			"<div>" +
			  "<div><label><b>" + repo.code + "</b></label> - <span style='font-size:10px;'><i>" + repo.user_type + "</i></span></div>" +
			  "<div><span style='font-size:10px;'>" + repo.description + "</span></div>" +
			"</div>"
			);


			return $container;
		}

		function formatRepoSelection (repo) {
			return repo.code || repo.text;
		}
	});
</script>