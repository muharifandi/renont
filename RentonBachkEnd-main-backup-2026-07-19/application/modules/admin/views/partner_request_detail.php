<style>
	/* Set the size of the div element that contains the map */
	#map {
	height: 400px;  /* The height is 400 pixels */
	width: 100%;  /* The width is the width of the web page */
}
</style>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Partner
        <small>Request Detail</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Partner</a></li>
        <li><a href="#">Request List</a></li>
        <li class="active">Request Detail</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Personal Detail</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
				<div class="col-sm-12" style="padding:0px;">
					<div class="col-md-4 col-lg-2" style="margin-bottom:10px;">
						<a href="#" class="btn btn-success btn-block" data-toggle="modal" data-target="#accept-modal"> Accept Request </a>
					</div>
					<div class="col-md-4 col-lg-2" style="margin-bottom:10px;">
						<a href="#" class="btn btn-danger btn-block" data-toggle="modal" data-target="#reject-modal"> Reject Request </a>
					</div>
				</div>
				<div class="col-sm-12 col-md-2">
				<a target="<?php if($detail->img_profile =="") echo '';else echo "_blank";?>" href="<?php if($detail->img_profile =="") echo '#';else echo base_url().'data/partners/profile/'.$detail->img_profile;?>"><img src="<?php if($detail->img_profile =="") echo base_url().'data/default/no_image.png';else echo base_url().'data/partners/profile/'.$detail->img_profile;?>" alt="..." class="img-thumbnail"/></a>
				</div>
				<div class="col-sm-12 col-md-10">
					<dl class="dl-horizontal">
						<dt>Fullname : </dt>
						<dd><?php echo $detail->fullname;?></dd>
						<dt>Email : </dt>
						<dd><?php echo $detail->email;?></dd>
						<dt>Phone : </dt>
						<dd><?php echo $detail->phone;?></dd>
						<dt>Ownership : </dt>
						<dd><?php echo $detail->ownership;?></dd>
						<dt>Company Name : </dt>
						<dd><?php echo $detail->company_name;?></dd>
						<dt>Description : </dt>
						<dd><?php echo $detail->description;?></dd>
						<dt>Regencies : </dt>
						<dd><?php echo $detail->regencies;?></dd>
						<dt>Address : </dt>
						<dd><?php echo $detail->address;?></dd>
						<dt>Tax Number : </dt>
						<dd><?php echo $detail->tax_number;?></dd>
					</dl>
				</div>
				<div class="col-sm-12">
					<h4>Attachment Files</h4>
					<div class="col-sm-2">
						<center><p class="text-muted">Identity</p></center>
						<a target="<?php if($detail->img_identity =="") echo '';else echo "_blank";?>" href="<?php if($detail->img_identity =="") echo '#';else echo base_url().'data/partners/files/identity/'.$detail->img_identity;?>"><img src="<?php if($detail->img_identity =="") echo base_url().'data/default/no_image.png';else echo base_url().'data/partners/files/identity/'.$detail->img_identity;?>" alt="..." class="img-thumbnail"/></a>
					</div>
					
					<?php if($detail->ownership_id == 2){ ?>
					<div class="col-sm-2">
						<center><p class="text-muted">Driver Licence</p></center>
						<a target="<?php if($detail->img_driver_licence =="") echo '';else echo "_blank";?>" href="<?php if($detail->img_driver_licence =="") echo '#';else echo base_url().'data/partners/files/driver_licence/'.$detail->img_driver_licence;?>"><img src="<?php if($detail->img_driver_licence =="") echo base_url().'data/default/no_image.png';else echo base_url().'data/partners/files/driver_licence/'.$detail->img_driver_licence;?>" alt="..." class="img-thumbnail"/></a>
					</div>
					<?php }
						if($detail->ownership_id == 1){
					?>
					<div class="col-sm-2">
						<center><p class="text-muted">Bussiness Licence</p></center>
						<a target="<?php if($detail->img_bussiness_licence =="") echo '';else echo "_blank";?>" href="<?php if($detail->img_bussiness_licence =="") echo "#";else echo base_url().'data/partners/files/bussiness_licence/'.$detail->img_bussiness_licence;?>"><img src="<?php if($detail->img_bussiness_licence =="") echo base_url().'data/default/no_image.png';else echo base_url().'data/partners/files/bussiness_licence/'.$detail->img_bussiness_licence;?>" alt="..." class="img-thumbnail"/></a>
					</div>
					<div class="col-sm-2">
						<center><p class="text-muted">Bussiness Registration</p></center>
						<a target="<?php if($detail->img_bussiness_registration =="") echo '';else echo "_blank";?>" href="<?php if($detail->img_bussiness_registration =="") echo '#';else echo base_url().'data/partners/files/bussiness_registration/'.$detail->img_bussiness_registration;?>"><img src="<?php if($detail->img_bussiness_registration =="") echo base_url().'data/default/no_image.png';else echo base_url().'data/partners/files/bussiness_registration/'.$detail->img_bussiness_registration;?>" alt="..." class="img-thumbnail"/></a>
					</div>
						<?php } ?>
				</div>
				<div class="col-sm-6">
					<h4>Map Location</h4>
						<div id="map"></div>
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
<div class="modal fade" id="accept-modal">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title">Accept Request Partner</h4>
	  </div>
	  <div class="modal-body">
		<p>Do you want to accept request of <?php echo $detail->fullname;?> ?</p>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
		<a href="<?php echo base_url()."admin/partner/accept_request/".$detail->id;?>" class="btn btn-success">Yes</a>
	  </div>
	</div>
	<!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="reject-modal">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title">Reject Request Partner</h4>
	  </div>
	  <div class="modal-body">
		<p>Do you want to reject request of <?php echo $detail->fullname;?> ?</p>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
		<a href="<?php echo base_url()."admin/partner/reject_request/".$detail->id;?>" class="btn btn-danger">Yes</a>
	  </div>
	</div>
	<!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>


<script>
// Initialize and add the map
function initMap() {
  // The location of Uluru
  var uluru = {lat: <?php echo $detail->latitude;?>, lng: <?php echo $detail->longitude;?>};
  // The map, centered at Uluru
  var map = new google.maps.Map(
      document.getElementById('map'), {zoom: 19, center: uluru});
  // The marker, positioned at Uluru
  var marker = new google.maps.Marker({position: uluru, map: map});
}
    </script>
    
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAB1HocAgo5y9UVQNhcCvf-y1g-SFTjyos&callback=initMap"></script>
