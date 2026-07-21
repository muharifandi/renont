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
<div style="text-align:center;"><b>Laporan Pencairan</b></div>
<table border="0">
	<tbody>
		<tr>
			<td style="text-align:left;font-size:10px;"><?php if($group){?><b>Nama : </b><?php echo $fullname;}?></td>
			<td style="text-align:right;font-size:10px;"><b>Tanggal : </b><?php echo strftime('%d %B %Y',strtotime($start_date));?> - <?php echo strftime('%d %B %Y',strtotime($end_date));?></td>
		</tr>
		<tr>
			<td style="text-align:left;font-size:10px;"><?php if($group){?><b>Mitra : </b><?php echo $company_name;}?></td>
			<td style="text-align:right;font-size:10px;"></td>
		</tr>
	</tbody>
</table>
<table border="1" >
	<thead>
		<tr>
			<th>No</th>
			<?php if(!$group){?><th>Nama Akun</th><?php }?>
			<?php if(!$group){?><th>Mitra</th><?php }?>
			<th>Bank</th>
			<th>Atas Nama Bank</th>
			<th>Nominal</th>
			<th>Tanggal Pengisian</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			$no = 1;
			foreach($data as $val)
			{ ?>
			
			<tr>
				<td><?php echo $no;?></td>
				<?php if(!$group){?><td><?php echo $val->fullname;?></td><?php }?>
				<?php if(!$group){?><td><?php echo $val->company_name;?></td><?php }?>
				<td><?php echo $val->bank_name;?></td>
				<td><?php echo $val->account_bank_name;?></td>
				<td><?php echo "Rp. ".number_format($val->value, 0 ,",",".")?></td>
				<td><?php echo strftime('%d %B %Y',strtotime($val->date_added));?></td>
				<td><?php echo $val->status_name;?></td>
			</tr>
			<?php 
				$no++;
			}?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="<?php echo (!$group)?"5":"3";?>">Jumlah</th>
			<th colspan="3" class="cell"><b><?php echo "Rp. ".number_format($sum_total_value, 0 ,",",".")?></b></th>
			<th></th>
		</tr>
	</tfoot>
</table>