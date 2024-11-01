<?php echo $wprn_message; ?>
<div class="wrap">
<h2><?php echo wp_specialchars( $title ); ?></h2>
<?php if ( !$wprn_uninstall ) : ?>
<form action="" method="post" style="text-align: center;">
    <p><?php _e('If you choose to uninstall WP-Reply Notify the table above will be deleted from your database. Are you sure?', 'wprn'); ?></p>
    <p><label><input type="checkbox" name="agree" value="1"/> <?php _e("I'm Sure!", 'wprn'); ?></label></p>
    <input type="submit" value="Uninstall WP-Reply Notify" name="wprn_uninstall" class="button" />
</form>
<?php endif; ?>
<?php
if ( $wprn_uninstall ) {
        $deactivate_plugin_url = wp_nonce_url('plugins.php?action=deactivate&amp;plugin=wp-reply-notify/wp-reply-notify.php', 'deactivate-plugin_wp-reply-notify/wp-reply-notify.php');
        echo '<p align="center">'.sprintf(__('<a href="%s">Click Here</a> and the plugin will be deactivated else it don\'t work again', 'wprn'), $deactivate_plugin_url).'</p>';
}
?>
</div>
