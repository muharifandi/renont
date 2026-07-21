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
<div style="text-align:center;"><b>Laporan Berdasarkan Kode Marekting</b></div>
<table border="0">
	<tbody>
		<tr>
			<td style="text-align:left;font-size:10px;"><?php if($group){?><b>Kode Marketing : </b><?php echo $account_id;}?></td>
			<td style="text-align:right;font-size:10px;"><b>Tanggal : </b><?php echo strftime('%d %B %Y',strtotime($start_date));?> - <?php echo strftime('%d %B %Y',strtotime($end_date));?></td>
		</tr>
		<tr>
			<td style="text-align:left;font-size:10px;"><?php if($group){?><b>Nama Marketing : </b><?php echo $name;}?></td>
			<td style="text-align:right;font-size:10px;"></td>
		</tr>
	</tbody>
</table>
<table border="1" >
	<thead>
		<tr>
			<th>No</th>
			<?php if(!$group){?><th>Kode Marketing</th><?php }?>
			<?php if(!$group){?><th>Marketing</th><?php }?>
			<th>Mitra</th>
			<th>Persen (%)</th>
			<th>Jumlah Dibayar</th>
			<th>Biaya Tambahan</th>
			<th>Biaya Admin</th>
			<th>Komisi Marketing</th>
			<th>Tanggal</th>
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
				<?php if(!$group){?><td><?php echo $val->name;?></td><?php }?>
				<td><?php echo $val->company_name;?></td>
				<td><?php echo number_format($val->percentage, 0 ,",",".");?></td>
				<td><?php echo "Rp. ".number_format($val->total_payment, 0 ,",",".")?></td>
				<td><?php echo "Rp. ".number_format($val->total_fee, 0 ,",",".")?></td>
				<td><?php echo "Rp. ".number_format($val->admin_fee, 0 ,",",".")?></td>
				<td><?php echo "Rp. ".number_format($val->value, 0 ,",",".")?></td>
				<td><?php echo strftime('%d %B %Y',strtotime($val->date_added));?></td>
			</tr>
			<?php 
				$no++;
			}?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="<?php echo (!$group)?"5":"3";?>">Jumlah</th>
			<th class="cell"><b><?php echo "Rp. ".number_format($sum_total_payment, 0 ,",",".")?></b></th>
			<th class="cell"><b><?php echo "Rp. ".number_format($sum_total_fee, 0 ,",",".")?></b></th>
			<th class="cell"><b><?php echo "Rp. ".number_format($sum_admin_fee, 0 ,",",".")?></b></th>
			<th class="cell"><b><?php echo "Rp. ".number_format($sum_value, 0 ,",",".")?></b></th>
			<th></th>
			
		</tr>
		<tr>
			<th colspan="<?php echo (!$group)?"8":"6";?>">Jumlah yang dibayar</th>
			<th colspan="2" class="cell"><b><?php echo "Rp. ".number_format($sum_value, 2 ,",",".")?></b></th>
			
		</tr>
	</tfoot>
</table>