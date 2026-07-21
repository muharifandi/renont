<style>
	.notif-badge{
	visibility :hidden;
	}
</style>
<?php
	$user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
	$this->db->where('agents.account_id',$user->id);
	$this->db->join('agents_balance','agents_balance.account_id = agents.account_id','left');
	$agent = $this->db->get('agents')->row();
?>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
		<!-- Sidebar user panel -->
		<div class="user-panel">
			<div class="pull-left image">
				<img src="<?php echo base_url()."data/agents/profile/thumb_".$agent->img_profile;?>" class="img-circle" alt="User Image">
			</div>
			<div class="pull-left info">
				<p>
					<?php
						
						echo $user->first_name." ".$user->last_name;
						
						
						
					?>
				</p>
				<a href="#"><i class="fa fa-circle text-success"></i> 
					<?php
						echo "Rp. ".number_format($agent->balance, 2 ,",",".");
					?>
				</a>
			</div>
		</div>
		<div class="user-panel">
			<div class="alert alert-success" role="alert">
				<center>Kode Marketing : <b><?php echo $user->id;?></b></center>
			</div>
		</div>
		<!-- search form -->
		
		<!-- /.search form -->
		<!-- sidebar menu: : style can be found in sidebar.less -->
		<ul class="sidebar-menu" data-widget="tree">
			<li class="header">Dashboard</li>
			<li><a href="<?php echo base_url(); ?>agent/dashboard/summary"><i class="fa fa-line-chart"></i> <span>Analitik</span></a></li>
			<li class="header">Mitra</li>
			<li><a href="<?php echo base_url(); ?>agent/partner/list"><i class="fa fa-user-circle"></i> <span>List Mitra</span></a></li>
			<li><a href="<?php echo base_url(); ?>agent/partner/list_commission"><i class="fa fa-money"></i> <span>Komisi dari Mitra</span></a></li>
			<li><a href="<?php echo base_url(); ?>agent/partner/list_transaction"><i class="fa fa-list"></i> <span>List Transaksi</span></a></li>
			<li class="header">Keuangan</li>
			<li><a href="<?php echo base_url(); ?>agent/agent/list_withdraw"><i class="fa fa-long-arrow-left"></i> <span>Pencairan</span></a></li>
			<li class="header">Lainnya</li>
			<li class="treeview">
				<a href="#">
					<i class="fa fa-gears"></i> <span>Konfigurasi</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>
				</a>
				<ul class="treeview-menu">
					<li><a href="<?php echo base_url(); ?>agent/config/profile"><i class="fa fa-gear"></i> Dasar</a></li>
					<li><a href="<?php echo base_url(); ?>agent/config/list_bank"><i class="fa fa-money"></i> Bank</a></li>
				</ul>
			</li>
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
	setNotification($('#withdraw_request_badge'),result.withdraw_request);
	setNotification($('#topup_request_badge'),result.topup_request);
	setNotification($('#partner_claim_reward_badge'),result.partner_claim_reward);
	setNotification($('#feature_request_badge'),result.feature_request);
	setNotification($('#support_request_badge'),result.support_request);
	setNotification($('#chat_badge'),result.chat);
	})
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