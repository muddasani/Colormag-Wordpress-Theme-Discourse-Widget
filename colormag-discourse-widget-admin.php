<?php 
	if($_POST['colormag_discourse_widget_op_hidden'] == 'Y') {
		//Form data sent
		$discourse_url = $_POST['discourse_url'];
		$random_image = $_POST['random_image'];
		update_option('discourse_url', $discourse_url);
		update_option('random_image', $random_image);
		?>
		<div class="updated"><p><strong><?php echo __('Options saved.', 'colormag-discourse-widget'); ?></strong></p></div>
		<?php
	} else {
		//Normal page display
		$discourse_url = get_option('discourse_url');
		$random_image = get_option('random_image');

	}
?>

<div class="wrap">
<?php echo "<h2>" . __( 'Colormag Discourse Widget Options', 'colormag-discourse-widget' ) . "</h2>"; ?>
<?php echo  __( 'Set the options below', 'colormag-discourse-widget' ); ?>


<div class="tool-box">
<?php echo "<h3>" . __( 'Standard Options', 'colormag-discourse-widget' ) . "</h3>"; ?>
<form name="colormag_discourse_widget_ops_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	<input type="hidden" name="colormag_discourse_widget_op_hidden" value="Y" />
	<table class="form-table">
		<tbody>			
			<!-- discourse_url -->
			<tr valign="top">
				<th scope="row">
					<label for="discourse_url"><?php echo __('Discourse URL', 'colormag-discourse-widget'); ?></label>
				</th>
				<td>
					<input name="discourse_url" type="url" id="discourse_url"  value="<?php form_option('discourse_url', 'colormag-discourse-widget'); ?>" class="regular-text ltr" />
					<p class="description"><?php echo __('Your Discourse forum url like https://studmed.dk - Alert without a ending / ', 'colormag-discourse-widget'); ?></p>
				</td>
			</tr>
			<tr>
			<!-- random_image -->
			<tr>
				<th scope="row">
					<label for="random_image"><?php echo __('Show a Random Image', 'colormag-discourse-widget'); ?></label>
				</th>
				<td scope="row" colspan="2" class="th-full">
					<label for="random_image">
					<input name="random_image" id="random_image" value="1"<?php checked('1', get_option('random_image')); ?> type="checkbox">
					<?php echo  __('If the post doesn\'t have an image then use https://picsum.photos for a random image', 'colormag-discourse-widget'); ?></label>
				</td>
			</tr>			
		</tbody>
	</table>
</div>
	<p class="submit">
		<input type="submit" name="Submit" value="<?php echo __('Save and update options', 'colormag-discourse-widget'); ?>" />
	</p>
</form>
</div>