  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Marketing
        <small>Sandingkan Mitra</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Marketing</a></li>
        <li class="active">Sandingkan Mitra</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-6">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Sandingkan Mitra</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			<center><span style=" color:red;"><?php echo $error_message;?><span style=" color:green;"><?php echo $success_message;?></span></center>
				<form role="form" method="post" action="<?php echo base_url().'admin/agent/post_pair_partner';?>" enctype="multipart/form-data">
				<div class="box-body">
					<div class="form-group">
					  <label>Email Marketing *</label>
					  <input type="text" class="form-control" name="agent_email" placeholder="Email Marketing" value="<?php echo $input->agent_email;?>"/>
					</div>
					<div class="form-group">
					  <label>Email Mitra *</label>
					  <input type="text" class="form-control" name="partner_email" placeholder="Email Mitra" value="<?php echo $input->partner_email;?>"/>
					</div>
					<div class="box-footer">
					<button type="submit" class="btn btn-primary">Sandingkan</button>
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