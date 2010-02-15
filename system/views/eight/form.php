<?php echo form::open($action, $attributes) ?>

<?php include Eight::find_file('views', 'eight/form_errors') ?>

<fieldset>
<?php foreach($inputs as $title => $input): ?>
<label><span><?php echo $title ?></span><?php echo form::input($input) ?></label>
<?php endforeach ?>
</fieldset>

<fieldset class="submit"><?php echo html::anchor($cancel, 'Cancel'), ' ', form::button(nil, 'Save') ?></fieldset>

<?php echo form::close() ?>
