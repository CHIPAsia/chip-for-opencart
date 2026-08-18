<div class="table-responsive">
	<table class="table table-bordered table-hover">
		<thead>
			<tr>
				<td class="text-left"><?php echo $column_order; ?></td>
				<td class="text-left"><?php echo $column_chip_id; ?></td>
				<td class="text-left"><?php echo $column_status; ?></td>
				<td class="text-left"><?php echo $column_amount; ?></td>
				<td class="text-left"><?php echo $column_environment; ?></td>
				<td class="text-left"><?php echo $column_date_added; ?></td>
			</tr>
		</thead>
		<tbody>
			<?php if ($reports) { ?>
				<?php foreach ($reports as $report) { ?>
					<tr>
						<td class="text-left"><a href="<?php echo $report['order']; ?>" target="_blank"><?php echo $report['order_id']; ?></a></td>
						<td class="text-left"><?php echo $report['chip_id']; ?></td>
						<td class="text-left"><?php echo $report['status']; ?></td>
						<td class="text-left"><?php echo $report['amount']; ?></td>
						<td class="text-left"><?php echo $report['environment_type']; ?></td>
						<td class="text-left"><?php echo $report['date_added']; ?></td>
					</tr>
				<?php } ?>
			<?php } else { ?>
				<tr>
					<td class="text-center" colspan="6"><?php echo $text_no_results; ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<div class="row">
	<div class="col-sm-12 text-left"><?php echo $report_pagination; ?></div>
</div>
