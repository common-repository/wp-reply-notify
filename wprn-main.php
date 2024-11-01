<?php
wprn_delete_period_exceeds();

$items_per_page = wprn_get_option('items_per_page');
if ( $items_per_page < 1 ) $items_per_page = 1;

// search name
$where_search_name = ( $_GET['q'] ) ? "WHERE r2_name = '".$wpdb->escape($_GET['q'])."'" : '';

// total rows count
$sql = "SELECT COUNT(id) FROM {$table_prefix}wprn_notified
        {$where_search_name}";
$total = $wpdb->get_var($sql);

// page info
if ( isset( $_GET['apage'] ) )
    $page = abs( (int) $_GET['apage'] );
else
    $page = 1;
    
$start = ( $page - 1 ) * $items_per_page;

$page_links = paginate_links( array(
    'base' => add_query_arg( 'apage', '%#%' ),
    'format' => '',
    'prev_text' => '&laquo;',
    'next_text' => '&raquo;',
    'total' => ceil($total / $items_per_page),
    'current' => $page
));

// get items
$sql = "SELECT * FROM {$table_prefix}wprn_notified
        {$where_search_name}
        ORDER BY id DESC
        LIMIT $start, $items_per_page";
$rows = $wpdb->get_results($sql, ARRAY_A);
if ( !is_array($rows) ) $rows = array();
?>
<style type="text/css">
.wprn_new { background-color: #FFFFE0; }
</style>

<?php echo $wprn_message; ?>

<div class="wrap">
<h2><?php echo wp_specialchars( $title ); ?></h2>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="get">
    <?php
    foreach ( $_GET as $k => $v ):
        if ( $k == 'q' ) continue;
        $v = htmlspecialchars($v);
    ?>
    <input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
    <?php endforeach; ?>
    <p class="search-box">
        <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q']); ?>" />
        <input type="submit" class="button" value="<?php _e('Search Name', 'wprn'); ?>" />
    </p>
</form

<form id="notified-form" action="" method="post">

<div class="tablenav">
    <div class="alignleft actions">
        <input type="submit" value="<?php _e('Delete'); ?>" class="button" name="wprn_delete" />
    </div>
    
    <?php if ( $page_links ) : ?>
    <div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
        number_format_i18n( $start + 1 ),
        number_format_i18n( min( $page * $items_per_page, $total ) ),
        number_format_i18n( $total ),
        $page_links
    ); echo $page_links_text; ?></div>
    <?php endif; ?>
</div>

<table class="widefat" cellspacing="0">
    <thead>
    <tr>
        <th scope="col" class="check-column">
            <input type="checkbox" id="check-all"/>
        </th>
        <th scope="col"><?php _e('Site / Post', 'wprn'); ?></th>
        <th scope="col"><?php _e('Comment', 'wprn'); ?></th>
    </tr>
    </thead>

    <tbody id="the-comment-list" class="list:comment">
    <?php
    $time_new = time() - 86400;
    foreach ( $rows as $row ) :
        if ( $row['s_home_title'] == '' )
            $row['s_home_title'] = __('No Title', 'wprn');
            
        $row['s_home_title'] = htmlspecialchars($row['s_home_title']);
        $row['s_post_title'] = htmlspecialchars($row['s_post_title']);
        $row['r1_name'] = htmlspecialchars($row['r1_name']);
        $row['r1_body'] = htmlspecialchars($row['r1_body']);
        $row['r2_name'] = htmlspecialchars($row['r2_name']);
        $row['r2_body'] = htmlspecialchars($row['r2_body']);
        
        $class_new = (strtotime($row['r2_regdate']) > $time_new) ? ' class="wprn_new"' : '';
    ?>
    <tr<?php echo $class_new; ?>>
        <th class="check-column" scope="row"><input type="checkbox" value="<?php echo $row['id']; ?>" name="id[]"/></th>
        <td style="width: 25em;">
            <a href="<?php echo $row['url']; ?>"><?php echo $row['s_home_title']; ?></a><br />
            <b>┗ </b><a href="<?php echo $row['s_url']; ?>"><?php echo $row['s_post_title']; ?></a>
        </td>
        <td>
            <p style="display: none; margin: 0;">
                <?php echo $row['r1_name']; ?>
                <?php _e('Says', 'wprn'); ?>:
                <a href="<?php echo $row['r1_url']; ?>"><?php echo $row['r1_regdate']; ?></a><br />
                <?php echo $row['r1_body']; ?>
            </p>
            <a href="#" class="unroll" style="display: block; float: left;">[+]</a>
            <p style="margin: 0 0 0 1.5em;">
                <?php if ( $row['r2_homepage'] ): ?>
                <a href="<?php echo $row['r2_homepage']; ?>"><?php echo $row['r2_name']; ?></a>
                <?php else: ?>
                <?php echo $row['r2_name']; ?>
                <?php endif; ?>
                <?php _e('Reply', 'wprn'); ?>:
                <a href="<?php echo $row['r2_url']; ?>"><?php echo $row['r2_regdate']; ?></a><br />
                <?php echo $row['r2_body']; ?>
            </p>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</form>

</div>

<script type="text/javascript">
(function($){
    // comment unroll
    $('a.unroll').click(function() {
        var cmt = $(this).prev();
        
        if ( cmt.css('display') == 'none' ) {
            cmt.css('display', '');
            $(this).text('[-]');
        } else {
            cmt.css('display', 'none');
            $(this).text('[+]');
        }
        
        return false;
    });

    // checkbox all check
    $('#check-all').change(function() {
        $("input[name='id[]']").attr('checked', $(this).attr('checked'));
    });
    
    $('#notified-form').submit(function() {
        return confirm('<?php echo js_escape(__("You are about to delete the selected items.\n  'Cancel' to stop, 'OK' to delete.", 'wprn')); ?>');
    });
})(jQuery);
</script>
