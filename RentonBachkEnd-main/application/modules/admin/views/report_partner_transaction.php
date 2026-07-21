<style>
	table {
	padding:2px;
	}
	th{
	font-size:8px;
	}
	td{
	font-size:8px;
	text-align: center;
	}
	tr>th{
	background-color:grey;color:white;
	text-align: center;
	}
	.cell{
	background-color:white;color:black;
	}
</style>
<div style="text-align:center;"><b>Laporan Transaksi Mitra</b></div>
<table border="0">
	<tbody>
		<tr>
			<td style="text-align:left;font-size:10px;"><?php if($group){?><b>ID Mitra : </b><?php echo $account_id;}?></td>
			<td style="text-align:right;font-size:10px;"><b>Tanggal : </b><?php echo strftime('%d %B %Y',strtotime($start_date));?> - <?php echo strftime('%d %B %Y',strtotime($end_date));?></td>
		</tr>
		<tr>
			<td style="text-align:left;font-size:10px;"><?php if($group){?><b>Nama Mitra : </b><?php echo $name;}?></td>
			<td style="text-align:right;font-size:10px;"></td>
		</tr>
	</tbody>
</table>
<table border="1" >
	<thead>
		<tr>
			<th>No</th>
			<?php if(!$group){?><th>ID Mitra</th><?php }?>
			<?php if(!$group){?><th>Mitra</th><?php }?>
			<th>Pelanggan</th>
			<th>Nama Kendaraan</th>
			<th>Harga/Paket</th>
			<th>Jumlah Hari</th>
			<th>Total Biaya</th>
			<th>Biaya Keterlambatan</th>
			<th>Komisi Admin</th>
			<th>Tanggal Selesai</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			$no = 1;
			foreach($data as $val)
			{ ?>
			<tr>
				<td><?php echo $no;?></td>
				<?php if(!$group){?><td><?php echo $val->account_id;?></td><?php }?>
				<?php if(!$group){?><td><?php echo $val->company_name;?></td><?php }?>
				<td><?php echo $val->customer_name;?></td>
				<td><?php echo $val->title;?></td>
				<td><?php echo "Rp. ".number_format($val->price, 0 ,",",".");?></td>
				<td><?php echo $val->days;?></td>
				<td><?php echo "Rp. ".number_format($val->total_payment, 0 ,",",".")?></td>
				<td><?php echo "Rp. ".number_format($val->overtime_fee, 0 ,",",".")?></td>
				<td><?php echo "Rp. ".number_format($val->admin_fee, 0 ,",",".")?></td>
				<td><?php echo strftime('%d %B %Y',strtotime($val->date_modified));?></td>
			</tr>
			<?php 
				$no++;
			}?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="<?php echo (!$group)?"7":"5";?>">Jumlah</th>
			<th class="cell"><b><?php echo "Rp. ".number_format($sum_total_payment, 0 ,",",".")?></b></th>
			<th class="cell"><b><?php echo "Rp. ".number_format($sum_overtime_fee, 0 ,",",".")?></b></th>
			<th class="cell"><b><?php echo "Rp. ".number_format($sum_admin_fee, 0 ,",",".")?></b></th>
			<th></th>
		</tr>
		<tr>
			<th colspan="<?php echo (!$group)?"7":"5";?>">Jumlah yang diterima</th>
			<th colspan="4" class="cell"><b><?php echo "Rp. ".number_format($sum_admin_fee, 0 ,",",".")?></b></th>
			
		</tr>
	</tfoot>
</table>