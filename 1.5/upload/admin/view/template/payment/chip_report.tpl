<table class="list">
	<thead>
		<tr>
			<td class="left"><?php echo $column_order; ?></td>
			<td class="left"><?php echo $column_chip_id; ?></td>
			<td class="left"><?php echo $column_status; ?></td>
			<td class="left"><?php echo $column_amount; ?></td>
			<td class="left"><?php echo $column_environment; ?></td>
			<td class="left"><?php echo $column_date_added; ?></td>
		</tr>
	</thead>
	<tbody>
		<?php if ($reports) { ?>
		<?php foreach ($reports as $report) { ?>
		<tr>
			<td class="left"><a href="<?php echo $report['order']; ?>" target="_blank"><?php echo $report['order_id']; ?></a></td>
			<td class="left"><?php echo $report['chip_id']; ?></td>
			<td class="left"><?php echo $report['status']; ?></td>
			<td class="left"><?php echo $report['amount']; ?></td>
			<td class="left"><?php echo $report['environment_type']; ?></td>
			<td class="left"><?php echo $report['date_added']; ?></td>
		</tr>
		<?php } ?>
		<?php } else { ?>
		<tr>
			<td class="center" colspan="6"><?php echo $text_no_results; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
<div class="pagination"><?php echo $report_pagination; ?></div>
