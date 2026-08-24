<div class="table-responsive">
	<table class="table table-bordered table-hover">
		<thead>
			<tr>
				<td class="text-left"><?php echo $column_customer; ?></td>
				<td class="text-left"><?php echo $column_token_id; ?></td>
				<td class="text-left"><?php echo $column_card_type; ?></td>
				<td class="text-left"><?php echo $column_card_name; ?></td>
				<td class="text-left"><?php echo $column_card_number; ?></td>
				<td class="text-left"><?php echo $column_card_expire; ?></td>
				<td class="text-left"><?php echo $column_date_added; ?></td>
			</tr>
		</thead>
		<tbody>
			<?php if ($tokens) { ?>
				<?php foreach ($tokens as $token) { ?>
					<tr>
						<td class="text-left"><a href="<?php echo $token['customer']; ?>" target="_blank"><?php echo $token['customer_id']; ?></a></td>
						<td class="text-left"><?php echo $token['token_id']; ?></td>
						<td class="text-left"><?php echo $token['type']; ?></td>
						<td class="text-left"><?php echo $token['card_name']; ?></td>
						<td class="text-left"><?php echo $token['card_number']; ?></td>
						<td class="text-left"><?php echo $token['card_expire']; ?></td>
						<td class="text-left"><?php echo $token['date_added']; ?></td>
					</tr>
				<?php } ?>
			<?php } else { ?>
				<tr>
					<td class="text-center" colspan="7"><?php echo $text_no_results; ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<div class="row">
	<div class="col-sm-12 text-left"><?php echo $token_pagination; ?></div>
</div>
