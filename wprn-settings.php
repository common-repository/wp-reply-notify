<?php
$checked = ' checked="checked"';
$options = wprn_get_option();

$check = array();
$check['reply_receive'.$options['reply_receive']] = $checked;
$check['reply_notify'.$options['reply_notify']] = $checked;
$check['receive_email'.$options['receive_email']] = $checked;
?>
<?php echo $wprn_message; ?>
<div class="wrap">
<h2><?php echo wp_specialchars( $title ); ?></h2>

<form action="" method="post">
<table class="form-table">
    <tr valign="top">
        <th scope="row"><?php _e('Reply receive', 'wprn'); ?></th>
        <td>
            <label><input type="radio" name="reply_receive" value="0"<?php echo $check['reply_receive0']; ?>/> off</label><br />
            <label><input type="radio" name="reply_receive" value="1"<?php echo $check['reply_receive1']; ?>/> on</label>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><?php _e('Reply notify', 'wprn'); ?></th>
        <td>
            <label><input type="radio" name="reply_notify" value="0"<?php echo $check['reply_notify0']; ?>/> off</label><br />
            <label><input type="radio" name="reply_notify" value="1"<?php echo $check['reply_notify1']; ?>/> on</label>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><?php _e('Receive Email', 'wprn'); ?></th>
        <td>
            <label><input type="radio" name="receive_email" value="0"<?php echo $check['receive_email0']; ?>/> off</label><br />
            <label><input type="radio" name="receive_email" value="1"<?php echo $check['receive_email1']; ?>/> on</label>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="items-per-page"><?php _e('Items Per Page', 'wprn'); ?></label></th>
        <td>
            <input type="text" class="small-text" id="items-per-page" name="items_per_page" value="<?php echo $options['items_per_page']; ?>"/>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="keep-period"><?php _e('Keep period', 'wprn'); ?></label></th>
        <td>
            <select id="keep-period" name="keep_period">
                <option value="0"><?php _e('Unlimited', 'wprn'); ?></option>
                <?php
                $days = array(7, 10, 30, 60, 90, 180, 300, 365);
                
                if ( $options['keep_period'] ) {
                    if ( !in_array($options['keep_period'], $days) )
                        $days[] = $options['keep_period'];
                }
                
                foreach ( $days as $day ) :
                    $slt = ( $day == $options['keep_period'] ) ? ' selected="selected"' : '';
                ?>
                    <option value="<?php echo $day; ?>"<?php echo $slt; ?>><?php echo sprintf(__("%d days", 'wprn'), $day); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
</table>
<p class="submit">
    <input type="submit" value="<?php _e('Save Changes', 'wprn'); ?>" class="button-primary" name="wprn_settings"/>
</p>
</form>

</div>
