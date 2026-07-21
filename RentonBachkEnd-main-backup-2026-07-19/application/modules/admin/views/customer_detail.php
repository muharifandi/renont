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
        Pelanggan
        <small>Request Detail</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Pelanggan</a></li>
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
						<a href="#" class="btn btn-success btn-block" data-toggle="modal" data-target="#accept-modal"> Terima Request </a>
					</div>
					<div class="col-md-4 col-lg-2" style="margin-bottom:10px;">
						<a href="#" class="btn btn-danger btn-block" data-toggle="modal" data-target="#reject-modal"> Tolak Request </a>
					</div>
				</div>
				<div class="col-sm-12 col-md-2">
				<a target="<?php if($detail->img_profile =="") echo '';else echo "_blank";?>" href="<?php if($detail->img_profile =="") echo '#';else echo base_url().'data/customers/profile/'.$detail->img_profile;?>"><img src="<?php if($detail->img_profile =="") echo base_url().'data/default/no_image.png';else echo base_url().'data/customers/profile/'.$detail->img_profile;?>" alt="..." class="img-thumbnail"/></a>
				</div>
				<div class="col-sm-12 col-md-10">
					<dl class="dl-horizontal">
						<dt>Nama Lengkap : </dt>
						<dd><?php echo $detail->fullname ;?></dd>
						<dt>Email : </dt>
						<dd><?php echo $detail->email;?></dd>
						<dt>Telepon : </dt>
						<dd><?php echo $detail->phone;?></dd>
						<dt>Nomor Identitas : </dt>
						<dd><?php echo $detail->identity_number;?></dd>
					</dl>
				</div>
				<div class="col-sm-12">
					<h4>Lampiran</h4>
					<div class="col-sm-2">
						<center><p class="text-muted">Identitas</p></center>
						<a target="<?php if($detail->img_identity =="") echo '';else echo "_blank";?>" href="<?php if($detail->img_identity =="") echo '#';else echo base_url().'data/customers/files/identity/'.$detail->img_identity;?>"><img src="<?php if($detail->img_identity =="") echo base_url().'data/default/no_image.png';else echo base_url().'data/customers/files/identity/'.$detail->img_identity;?>" alt="..." class="img-thumbnail"/></a>
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
<div class="modal fade" id="accept-modal">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title">Terima Request Pelanggan</h4>
	  </div>
	  <div class="modal-body">
		<p>Apakah kamu ingin menerima pelanggan : <?php echo $detail->first_name;?> ?</p>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
		<a href="<?php echo base_url()."admin/customer/accept_request/".$detail->id;?>" class="btn btn-success">Ya</a>
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
		<h4 class="modal-title">Tolak Request Pelanggan</h4>
	  </div>
	  <div class="modal-body">
		<p>Apakah kamu ingin menolak pelanggan : <?php echo $detail->first_name;?> ?</p>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
		<a href="<?php echo base_url()."admin/customer/reject_request/".$detail->id;?>" class="btn btn-danger">Ya</a>
	  </div>
	</div>
	<!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>

