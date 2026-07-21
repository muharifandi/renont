<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="http://www.daterangepicker.com/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="http://www.daterangepicker.com/daterangepicker.css" />
<style type="text/css">
	table td {
	vertical-align : middle !important;
	text-align: left;  
	
	}
	.add-margin{
	margin-top: 5px;
	margin-bottom : 5px;
	}
	
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1>
			Dashboard
			<small>Laporan</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">Dashboard</li>
		</ol>
	</section>
	<section class="content">
		<div class="row">
			<center>
				
			</center>
			
		</div>
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<!-- /.box-header -->
					<div class="box-body">
						<table id="list" class="table table-bordered table-hover">
							<tbody>
								<tr>
									<td><span>Laproan Transaksi Marketing</span></td>
									<td>
										<form id="avbb" target="_blank_" method="post" action="<?php echo base_url().'admin/report/agent_transaction_report';?>">
											<div class="col-sm-4 add-margin">
												<div class="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; ">
													<center>
														<i class="fa fa-calendar"></i>&nbsp;
														<span></span> <i class="fa fa-caret-down"></i>
													</center>
												</div>
											</div>
											<div class="col-sm-6">
												<div class="col-sm-8 add-margin">
													<select id="agent_select" class="form-control" name="ids[]" multiple="" style="width:100%;"></select>
												</div>
												<div class="col-sm-4 add-margin">
													<select class="form-control" name="group">
														<option value="1">Kelompokan</option>
														<option value="0" selected>Jangan Kelompokan</option>
													</select>
												</div>
											</div>
											<div class="col-sm-2  add-margin">
												<input type="hidden" name="start_date"/>
												<input type="hidden" name="end_date"/>
												<input class="btn btn-sm btn-warning col-xs-12" type="submit" name="submit" value="Generate"/>	
											</div>
										</form>
									</td>
								</tr>
								<tr>
									<td><span>Laproan Transaksi Mitra</span></td>
									<td>
										<form target="_blank_" method="post" action="<?php echo base_url().'admin/report/partner_transaction_report';?>">
											<div class="col-sm-4  add-margin">
												<div class="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; ">
													<center>
														<i class="fa fa-calendar"></i>&nbsp;
														<span></span> <i class="fa fa-caret-down"></i>
													</center>
												</div>
											</div>
											<div class="col-sm-6">	
												<div class="col-sm-8 add-margin">
													<select id="partner_select" data-select2-id="partner" class="form-control partner_select" name="ids[]" multiple="" style="width:100%;"></select>
												</div>
												<div class="col-sm-4 add-margin">
													<select class="form-control" name="group">
														<option value="1">Kelompokan</option>
														<option value="0" selected>Jangan Kelompokan</option>
													</select>
												</div>
											</div>
											<div class="col-sm-2  add-margin">
												<input type="hidden" name="start_date"/>
												<input type="hidden" name="end_date"/>
												<input class="btn btn-sm btn-warning col-xs-12" type="submit" name="submit" value="Generate"/>	
											</div>
										</form>
									</td>
								</tr>
								<tr>
									<td><span>Laproan Uang Masuk Perusahaan</span></td>
									<td>
										<form target="_blank_" method="post" action="<?php echo base_url().'admin/report/topup_report';?>">
											<div class="col-sm-4 add-margin">
												<div class="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; ">
													<center>
														<i class="fa fa-calendar"></i>&nbsp;
														<span></span> <i class="fa fa-caret-down"></i>
													</center>
												</div>
											</div>
											<div class="col-sm-6">
												<div class="col-sm-8 add-margin">
													<select id="bank_company_select" class="form-control" name="ids[]" multiple="" style="width:100%;"></select>
												</div>
												<div class="col-sm-4 add-margin">
													<select class="form-control" name="group">
														<option value="1">Kelompokan</option>
														<option value="0" selected>Jangan Kelompokan</option>
													</select>
												</div>
											</div>
											<div class="col-sm-2 add-margin">
												<input type="hidden" name="start_date"/>
												<input type="hidden" name="end_date"/>
												<input class="btn btn-sm btn-warning col-xs-12" type="submit" name="submit" value="Generate"/>	
											</div>
										</form>
									</td>
								</tr>
								<tr>
									<td><span>Laproan Pencairan Dana</span></td>
									<td>
										<form target="_blank_" method="post" action="<?php echo base_url().'admin/report/withdraw_report';?>">
											<div class="col-sm-4 add-margin">
												<div class="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; ">
													<center>
														<i class="fa fa-calendar"></i>&nbsp;
														<span></span> <i class="fa fa-caret-down"></i>
													</center>
												</div>
											</div>
											<div class="col-sm-6">
												<div class="col-sm-8 add-margin">
													<select id="account_select" class="form-control" name="ids[]" multiple="" style="width:100%;"></select>
												</div>
												<div class="col-sm-4 add-margin">
													<select class="form-control" name="group">
														<option value="1">Kelompokan</option>
														<option value="0" selected>Jangan Kelompokan</option>
													</select>
												</div>
											</div>
											<div class="col-sm-2 add-margin">
												<input type="hidden" name="start_date"/>
												<input type="hidden" name="end_date"/>
												<input class="btn btn-sm btn-warning col-xs-12" type="submit" name="submit" value="Generate"/>	
											</div>
										</form>
									</td>
								</tr>
								<tr>
									<td><span>Laporan Transaksi Promosi Mitra</span></td>
									<td>
										<form target="_blank_" method="post" action="<?php echo base_url().'admin/report/partner_promote_transaction_report';?>">
											<div class="col-sm-4  add-margin">
												<div class="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; ">
													<center>
														<i class="fa fa-calendar"></i>&nbsp;
														<span></span> <i class="fa fa-caret-down"></i>
													</center>
												</div>
											</div>
											<div class="col-sm-6">
												<div class="col-sm-8 add-margin">
													<select id="partner_select" data-select2-id="promotion" class="form-control partner_select" name="ids[]" multiple="" style="width:100%;"></select>
												</div>
												<div class="col-sm-4 add-margin">
													<select class="form-control" name="group">
														<option value="1">Kelompokan</option>
														<option value="0" selected>Jangan Kelompokan</option>
													</select>
												</div>
											</div>
											<div class="col-sm-2  add-margin">
												<input type="hidden" name="start_date"/>
												<input type="hidden" name="end_date"/>
												<input class="btn btn-sm btn-warning col-xs-12" type="submit" name="submit" value="Generate"/>	
											</div>
										</form>
									</td>
								</tr>
							</tbody>
						</table>
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
	
	<script type="text/javascript">
		$(document).ready(function(){
			
			var start = moment().subtract(29, 'days');
			var end = moment();
			
			
			function cb(start, end) {
				$('.reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
				$('input[name="start_date"]').each(function( index ) {
					$(this).val(start.format('YYYY-MM-DD'));
				});
				$('input[name="end_date"]').each(function( index ) {
					$(this).val(end.format('YYYY-MM-DD'));
				});
			}
			
			$('.reportrange').daterangepicker({
				showCustomRangeLabel: true,
				startDate: start,
				endDate: end,
				ranges: {
					'Today': [moment(), moment()],
					'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
					'Last 7 Days': [moment().subtract(6, 'days'), moment()],
					'Last 30 Days': [moment().subtract(29, 'days'), moment()],
					'This Month': [moment().startOf('month'), moment().endOf('month')],
					'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			});
			
			$('.reportrange').on('apply.daterangepicker', function(ev, picker) {
				$(this).find("span").html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
				$(this).closest('form').find('input[name="start_date"]').val(picker.startDate.format('YYYY-MM-DD'));
				$(this).closest('form').find('input[name="end_date"]').val(picker.endDate.format('YYYY-MM-DD'));
			});
			
			var agent_select = $('#agent_select').select2({
				ajax: {
					url: base_url+"admin/agent/get_agents_select",
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
				multiple: true,
				placeholder: 'Mencari Marketing. . .',
				escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
				minimumInputLength: 1,
				templateResult: formatRepoAgent,
				templateSelection: formatRepoAgentSelection,
			});
			
			
			
			function formatRepoAgent (repo) {
				if (repo.loading) {
					return repo.fullname;
				}
				
				var img = null;
				if(repo.img_profile != null)
				img = base_url+"data/agents/profile/thumb_"+repo.img_profile;
				else
				img = base_url+'data/default/no_image.png';
				
				var $container = $(
				"<div>" +
				"<div style='display:inline-block;'><img width='48px' src='"+img+"'/></div>" +
				"<div style='display:inline-block;margin-left:5px;vertical-align:top;'><label style='margin-bottom:0px;'><b>" + repo.fullname + "</b></label><br><span>"+repo.regencies+"</span><br><span>"+repo.status_name+"</span></div>" +
				
				"</div>"
				);
				
				
				return $container;
			}
			
			function formatRepoAgentSelection (repo) {
				return repo.fullname || repo.text;
			}
			
			var partner_select = $('.partner_select').select2({
				ajax: {
					url: base_url+"admin/partner/get_partners_select",
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
				multiple: true,
				placeholder: 'Mencari Mitra. . .',
				escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
				minimumInputLength: 1,
				templateResult: formatRepoPartner,
				templateSelection: formatRepoPartnerSelection,
			});
			
			
			
			function formatRepoPartner (repo) {
				if (repo.loading) {
					return repo.company_name;
				}
				
				var img = null;
				if(repo.img_profile != null)
				img = base_url+"data/partners/profile/thumb_"+repo.img_profile;
				else
				img = base_url+'data/default/no_image.png';
				
				var $container = $(
				"<div>" +
				"<div style='display:inline-block;'><img width='48px' src='"+img+"'/></div>" +
				"<div style='display:inline-block;margin-left:5px;vertical-align:top;'><label style='margin-bottom:0px;'><b>" + repo.company_name + "</b></label><br><span>"+repo.regencies+"</span><br><span>"+repo.status_name+"</span></div>" +
				
				"</div>"
				);
				
				
				return $container;
			}
			
			function formatRepoPartnerSelection (repo) {
				return repo.company_name || repo.text;
			}
			
			var bank_company_select = $('#bank_company_select').select2({
				ajax: {
					url: base_url+"admin/config/get_bank_company_select",
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
				multiple: true,
				placeholder: 'Mencari Bank. . .',
				escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
				minimumInputLength: 1,
				templateResult: formatRepoBankCompany,
				templateSelection: formatRepoBankCompanySelection,
			});
			
			
			
			function formatRepoBankCompany (repo) {
				if (repo.loading) {
					return repo.company_name;
				}
				
				var img = null;
				if(repo.icon != null)
				img = "data:image/png;base64,"+repo.icon;
				else
				img = base_url+'data/default/no_image.png';
				
				var $container = $(
				"<div>" +
				"<div style='display:inline-block;'><img width='48px' src='"+img+"'/></div>" +
				"<div style='display:inline-block;margin-left:5px;vertical-align:top;'><label style='margin-bottom:0px;'><b>" + repo.bank_name + "</b></label><br><span>"+repo.bank_number+"</span><br><span>"+repo.name+"</span></div>" +
				
				"</div>"
				);
				
				
				return $container;
			}
			
			function formatRepoBankCompanySelection (repo) {
				return repo.bank_name || repo.text;
			}
			
			var account_select = $('#account_select').select2({
				ajax: {
					url: base_url+"admin/customer/get_accounts_select",
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
				multiple: true,
				placeholder: 'Mencari Akun. . .',
				escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
				minimumInputLength: 1,
				templateResult: formatRepoAccount,
				templateSelection: formatRepoAccountSelection,
			});
			
			
			
			function formatRepoAccount (repo) {
				if (repo.loading) {
					return repo.company_name;
				}
				
				var img = null;
				if(repo.img_profile != null)
				img = base_url+"data/partners/profile/thumb_"+repo.img_profile;
				else
				img = base_url+'data/default/no_image.png';
				
				var partner = null;
				if(repo.partner_account_id != null)
				partner = "Mitra : <b>"+repo.company_name+"</b>";
				else
				partner = "Bukan Mitra";
				var $container = $(
				"<div>" +
				"<div style='display:inline-block;'><img width='48px' src='"+img+"'/></div>" +
				"<div style='display:inline-block;margin-left:5px;vertical-align:top;'><label style='margin-bottom:0px;'><b>" + repo.fullname + "</b></label><br><span>"+partner+"</span><br><span>"+repo.status_name+"</span></div>" +
				
				"</div>"
				);
				
				
				return $container;
			}
			
			function formatRepoAccountSelection (repo) {
				return repo.fullname || repo.text;
			}
			
			cb(start, end);
			
		});
	</script>
	
</div>

