<div style="text-align:center"><h3>Faktur Pencairan Dana Marketing</h3></div>
<div style="align:center">
	<table border="0">
		<tbody>
			<tr>
				<td width="65"></td>
				<td width="250" style="text-align:center"><b>Kode Marketing</b></td>
				<td width="10"><b>:</b></td>
				<td width="250"><?php echo $agent_withdraw->account_id;?></td>
				<td width="65"></td>
			</tr>
			<tr>
				<td width="65"></td>
				<td width="250" style="text-align:center"><b>Nama Marketing</b></td>
				<td width="10"><b>:</b></td>
				<td width="250"><?php echo $agent_withdraw->first_name." ".$agent_withdraw->last_name;?></td>
				<td width="65"></td>
			</tr>
			<tr>
				<td width="65"></td>
				<td width="250" style="text-align:center"><b>No Rekening</b></td>
				<td width="10"><b>:</b></td>
				<td width="250"><?php echo $agent_withdraw->bank_number;?></td>
				<td width="65"></td>
			</tr>
			<tr>
				<td width="65"></td>
				<td width="250" style="text-align:center"><b>Nama Bank</b></td>
				<td width="10"><b>:</b></td>
				<td width="250"><?php echo $agent_withdraw->bank_name;?></td>
				<td width="65"></td>
			</tr>
			<tr>
				<td width="65"></td>
				<td width="250" style="text-align:center"><b>Nominal</b></td>
				<td width="10"><b>:</b></td>
				<td width="250"><?php echo "Rp. ".number_format($agent_withdraw->value, 2 ,",",".");?></td>
				<td width="65"></td>
			</tr>
			<tr>
				<td width="65"></td>
				<td width="250" style="text-align:center"><b>Status</b></td>
				<td width="10"><b>:</b></td>
				<td width="250"><?php echo $agent_withdraw->status_name;?></td>
				<td width="65"></td>
			</tr>
			<tr>
				<td width="65"></td>
				<td width="250" style="text-align:center"><b>Tanggal Pencairan</b></td>
				<td width="10"><b>:</b></td>
				<td width="250"><?php echo strftime('%A, %d %B %Y %H:%M',strtotime($agent_withdraw->date_added));?></td>
				<td width="65"></td>
			</tr>
		</tbody>
	</table>
</div>