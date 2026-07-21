<style>
	.notif-badge{
		visibility :hidden;
	}
</style>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?php echo base_url(); ?>assets/backend/AdminLTE/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
			<p>
				<?php
					$user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
					echo $user->first_name." ".$user->last_name;
				?>
			</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <!-- search form -->
      
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu" data-widget="tree">
	  
	
		<li class="header">Dasboard</li>
		<li><a href="<?php echo base_url(); ?>admin/dashboard/summary"><i class="fa fa-line-chart"></i> <span>Analitik</span></a></li>
		<li><a href="<?php echo base_url(); ?>admin/report/"><i class="fa fa-book"></i> <span>Laporan</span></a></li>
      
		<li class="header">Administratior</li>
		<li><a href="<?php echo base_url(); ?>admin/config/list_admin"><i class="fa fa-user-circle"></i> <span>List Admin</span></a></li>
		<li><a href="<?php echo base_url(); ?>admin/config/list_bank_company"><i class="fa fa-image"></i> <span>List Rekening Perusahaan</span></a></li>
		<li><a href="<?php echo base_url(); ?>admin/config/list_feature"><i class="fa fa-check-square"></i> <span>List Layanan</span></a></li>
		<li class="treeview">
          <a href="#">
            <i class="fa fa-gears"></i> <span>Konfigurasi</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="<?php echo base_url(); ?>admin/config/basic"><i class="fa fa-gear"></i> Dasar</a></li>
            <li><a href="<?php echo base_url(); ?>admin/config/list_bank"><i class="fa fa-money"></i> Bank</a></li>
          </ul>
        </li>
		<li class="header">Marketing</li>
		<li><a href="<?php echo base_url(); ?>admin/agent/pair_partner"><i class="fa fa-user-circle"></i> <span>Sandingkan Mitra</span></a></li>
		<li><a href="<?php echo base_url(); ?>admin/agent/list"><i class="fa fa-user-circle"></i> <span>List Marketing</span></a></li>
		<li><a href="<?php echo base_url(); ?>admin/agent/list_commision"><i class="fa fa-money"></i> <span>Komisi</span></a></li>
		<li><a href="<?php echo base_url(); ?>admin/agent/list_transaction"><i class="fa fa-money"></i> <span>Transaksi</span></a></li>
		<li>
			<a href="<?php echo base_url(); ?>admin/agent/list_withdraw"><i class="fa fa-long-arrow-left"></i><span>Pencairan Dana</span>
				<span class="pull-right-container">
					<span id="agent_withdraw_request_badge" class="notif-badge label label-danger pull-right">4</span>
				</span>
			</a>
		</li>
        <li class="header">Kontrol Akun</li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-user-circle"></i> <span>Akun Mitra</span>
			<span class="pull-right-container">
				<i class="fa fa-angle-left pull-right"></i>
				<span id="partner_badge" class="notif-badge label label-danger pull-right">4</span>  
            </span>
          </a>
          <ul class="treeview-menu">
            <li>
				<a href="<?php echo base_url(); ?>admin/partner/list_register_request"><i class="fa fa-user-circle-o"></i> <span>Verifikasi Mitra</span>
					<span class="pull-right-container">
						<span id="partner_verification_badge" class="notif-badge label label-danger pull-right">4</span>
					</span>
				</a>
			</li>
            <li><a href="<?php echo base_url(); ?>admin/partner/list"><i class="fa fa-user-o"></i> List Mitra</a></li>
          </ul>
        </li>
		<li class="treeview">
          <a href="#">
            <i class="fa fa-user-circle"></i> <span>Akun Pelanggan</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="<?php echo base_url(); ?>admin/customer/list"><i class="fa fa-user-o"></i> List Pelanggan</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-money"></i> <span>Permintaan</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
			  <span id="request_badge" class="notif-badge label label-danger pull-right">4</span>
            </span>
          </a>
          <ul class="treeview-menu">
            <li>
				<a href="<?php echo base_url(); ?>admin/customer/list_withdraw"><i class="fa fa-long-arrow-left"></i><span>Pencairan Dana</span>
					<span class="pull-right-container">
						<span id="withdraw_request_badge" class="notif-badge label label-danger pull-right">4</span>
					</span>
				</a>
			</li>
            <li>
				<a href="<?php echo base_url(); ?>admin/customer/list_topup"><i class="fa fa-long-arrow-right"></i><span>Pengisian Dana</span>
					<span class="pull-right-container">
						<span id="topup_request_badge" class="notif-badge label label-danger pull-right">4</span>
					</span>
				</a>
			</li>
			<li>
				<a href="<?php echo base_url(); ?>admin/partnerReward/list_claim"><i class="fa fa-long-arrow-right"></i><span>Klaim Hadiah Mitra</span>
					<span class="pull-right-container">
						<span id="partner_claim_reward_badge" class="notif-badge label label-danger pull-right">4</span>
					</span>
				</a>
			</li>
          </ul>
        </li>
		<li>
			<a href="<?php echo base_url(); ?>admin/partner/list_feature_request"><i class="fa fa-check-square"></i> <span>Aktifasi Layanan</span>
				<span class="pull-right-container">
						<span id="feature_request_badge" class="notif-badge label label-danger pull-right">4</span>
					</span>
			</a>
		</li>   <!--
        <li><a href="<?php echo base_url(); ?>admin/partner/banner"><i class="fa fa-image"></i> <span>Banner Mitra (Belum)</span></a></li>
        <li><a href="<?php echo base_url(); ?>admin/partner/map"><i class="fa fa-image"></i> <span>Lokasi Pengguna (Belum)</span></a></li>
		-->
		<li class="header">Kontrol Event</li>
        <li><a href="<?php echo base_url(); ?>admin/voucher/list"><i class="fa fa-gift"></i> <span>Voucher</span></a></li>
        <li><a href="<?php echo base_url(); ?>admin/news/list"><i class="fa fa-image"></i> <span>Berita</span></a></li>
        <li><a href="<?php echo base_url(); ?>admin/news/list_preview"><i class="fa fa-th-large"></i> <span>Pratinjau Berita</span></a></li>
        <li><a href="<?php echo base_url(); ?>admin/partnerReward/list"><i class="fa fa-gift"></i> <span>Hadiah Mitra</span></a></li>
		   <!--
		<li class="header">Bantuan Pelanggan</li>
		<li>
			<a href="<?php echo base_url(); ?>admin/support/list_request"><i class="fa fa-long-arrow-left"></i><span>Permintaan(Belum)</span>
				<span class="pull-right-container">
					<span id="support_request_badge" class="notif-badge label label-danger pull-right">4</span>
				</span>
			</a>
		</li>
		<li>
			<a href="<?php echo base_url(); ?>admin/customer/chat"><i class="fa fa-long-arrow-right"></i><span>Chat(belum)</span>
				<span class="pull-right-container">
					<span id="chat_badge" class="notif-badge label label-danger pull-right">4</span>
				</span>
			</a>
		</li>
        -->
        <li class="header">Rental Kendaraan</li>
		<li class="treeview">
          <a href="#">
            <i class="fa fa-gears"></i> <span>Parameter</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_functional_type"><i class="fa fa-gear"></i> Jenis Fungsi</a></li>
            <li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_vehicle_type"><i class="fa fa-gear"></i> Jenis Kendaraan</a></li>
            <li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_brand"><i class="fa fa-gear"></i> Merek Kendaraan</a></li>
            <li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_vehicle_model"><i class="fa fa-gear"></i> Model Kendaraan</a></li>
            <li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_color"><i class="fa fa-gear"></i> Warna</a></li>
            <li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_transmition_type"><i class="fa fa-gear"></i> Jenis Transmisi</a></li>
            <li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_driven_type"><i class="fa fa-gear"></i> Jenis Penggerak</a></li>
            <li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_fuel"><i class="fa fa-gear"></i> Jenis Bahan Bakar</a></li>
          </ul>
        </li>
		<li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_vehicle"><i class="fa fa-car"></i> <span>Kendaraan</span></a></li>
		<li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_vehicle_transaction"><i class="fa fa-bars"></i> <span>Transaksi</span></a></li>
		<li><a href="<?php echo base_url(); ?>admin/rentVehicle/list_promote_vehicle_transaction"><i class="fa fa-bars"></i> <span>Transaksi Promosi</span></a></li>
        
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>
<!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/7.6.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.6.1/firebase-messaging.js"></script>

<!-- TODO: Add SDKs for Firebase products that you want to use
     https://firebase.google.com/docs/web/setup#available-libraries -->
<!--<script src="https://www.gstatic.com/firebasejs/4.8.1/firebase-analytics.js"></script>-->
 <script src="<?php echo base_url(); ?>firebase-config.js"></script>
<script>
	getAdminNotificationStatus();
	firebase.initializeApp(firebaseConfig);
	//firebase.analytics();
  
	
	// Retrieve Firebase Messaging object.
	const messaging = firebase.messaging();
	messaging.requestPermission()
	.then(function() {
	  console.log('Notification permission granted.');
	  // TODO(developer): Retrieve an Instance ID token for use with FCM.
	  if(isTokenSentToServer()) {
	  	console.log('Token already saved.');
	  } else {
	  	getRegToken();
	  }
	})
	.catch(function(err) {
	  console.log('Unable to get permission to notify.', err);
	});
	function getRegToken(argument) {
		messaging.getToken()
		  .then(function(currentToken) {
		    if (currentToken) {
		      //saveToken(currentToken);
		      var token = currentToken;
	          var device_id = '<?php echo md5($_SERVER['HTTP_USER_AGENT']); ?>';
		      console.log(token, device_id);
		      saveToken(token, device_id);
		    } else {
		      console.log('No Instance ID token available. Request permission to generate one.');
		      //setTokenSentToServer(false);
		    }
		  })
		  .catch(function(err) {
		    console.log('An error occurred while retrieving token. ', err);
		  //  setTokenSentToServer(false);
		  });
	}

	function setTokenSentToServer(token, device_id) {
	    window.localStorage.setItem('sentToServer', sent ? 1 : 0);
	}

	function isTokenSentToServer() {
	    return window.localStorage.getItem('sentToServer') == 1;
	}

	function saveToken(currentToken, deviceid) {
		$.ajax({
			url: <?php echo '"'.base_url().'admin/config/save_admin_token/'.$this->session->userdata('user_id').'/"';?> + currentToken,
			method: 'get',
		}).done(function(result){
			console.log(result);
		})
	}
	
	function getAdminNotificationStatus()
	{
		$.ajax({
			url: <?php echo '"'.base_url().'admin/config/get_admin_notification_count/'.$this->session->userdata('user_id').'/"';?> ,
			method: 'get',
			dataType: 'json',
		}).done(function(result){
			console.log(result);	
			setNotification($('#notification_badge'),result.notification);
			setNotification($('#partner_badge'),result.partner);
			setNotification($('#partner_verification_badge'),result.partner_verification);
			setNotification($('#request_badge'),result.request);
			setNotification($('#agent_withdraw_request_badge'),result.agent_withdraw_request);
			setNotification($('#withdraw_request_badge'),result.withdraw_request);
			setNotification($('#topup_request_badge'),result.topup_request);
			setNotification($('#partner_claim_reward_badge'),result.partner_claim_reward);
			setNotification($('#feature_request_badge'),result.feature_request);
			setNotification($('#support_request_badge'),result.support_request);
			setNotification($('#chat_badge'),result.chat);
		});
	}
	
	function setNotification(badge, count)
	{
		if(count > 0)
			{
				if(count > 99)
					badge.html('99+');
				else
					badge.html(count);
				badge.css('visibility','visible');
			}else
				badge.css('visibility','hidden');
	}
	
	messaging.onMessage((payload) => {
	  console.log('Message received. ', payload);
	  console.log(payload);
	  var notificationTitle = payload.notification.title;
	  const notificationOptions = {
			body: payload.notification.body,
			icon: payload.data.icon,
			image:  payload.data.image,
			click_action: payload.data.link_action, // To handle notification click when notification is moved to notification tray
			data: {
				click_action: payload.data.link_action
			}
		};
	  getAdminNotificationStatus();
	  var notification = new Notification(notificationTitle,notificationOptions);
	});
</script>