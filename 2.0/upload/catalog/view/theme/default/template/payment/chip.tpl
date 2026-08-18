<?php if ($chip_allow_instruction) { ?>
<h2><?php echo $text_instruction; ?></h2>
<div class="well well-sm">
  <p><?php echo $chip_instruction; ?></p>
</div>
<?php } ?>
<?php if ($chip_tokens) { ?>
<div class="form-group">
  <label class="control-label" for="input-chip-stored-card"><?php echo $text_stored_cards; ?></label>
  <select name="chip_stored_card" id="input-chip-stored-card" class="form-control">
    <option value=""><?php echo $text_card_new; ?></option>
    <?php foreach ($chip_tokens as $chip_token) { ?>
    <option value="<?php echo $chip_token['href']; ?>"><?php echo $chip_token['name']; ?></option>
    <?php } ?>
  </select>
</div>
<?php } ?>
<div class="buttons">
  <div class="pull-right">
    <a href="<?php echo $button_continue_action; ?>" id="button-chip-continue" class="btn btn-primary"><?php echo $button_continue; ?></a>
  </div>
</div>
<?php if ($chip_tokens) { ?>
<script type="text/javascript"><!--
$('#input-chip-stored-card').on('change', function () {
  if ($(this).val()) {
    $('#button-chip-continue').attr('href', $(this).val());
  } else {
    $('#button-chip-continue').attr('href', '<?php echo $button_continue_action; ?>');
  }
});
//--></script>
<?php } ?>
