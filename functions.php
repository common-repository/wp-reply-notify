<?php
function wprn_init() {
    $parent = dirname(__FILE__).'/wprn-main.php';
    $dir = dirname(__FILE__);
    
    add_menu_page(__('Edit Reply Notify', 'wprn'), __('Reply Notify', 'wprn'), 5, $dir.'/wprn-main.php');
    add_submenu_page($parent, __('WP-Reply Notify Settings', 'wprn'), __('Settings', 'wprn'), 5, $dir.'/wprn-settings.php');
    add_submenu_page($parent, __('Uninstall WP-Reply Notify', 'wprn'), __('Uninstall', 'wprn'), 5, $dir.'/wprn-uninstall.php');
}

function wprn_reply_notify($id, $data) {
    // is reply notify?
    if ( !wprn_get_option('reply_notify') ) return;

    // 답글
    $c_cmt = get_comment($id);
    if ( !$c_cmt->comment_parent ) return;
    // 부모 댓글
    $p_cmt = get_comment($c_cmt->comment_parent);
    // 주소 유효성
    if ( !preg_match('|^http://[^\s./]+\.[^\s]+$|i', $p_cmt->comment_author_url) ) return;
    // 내 블로그 주소와 비교
    if ( $p_cmt->comment_author_url == get_option('siteurl') ) return;
    // 끝에 /가 없으면 붙임
    $p_cmt->comment_author_url = preg_replace('|([^/])$|', '$1/', $p_cmt->comment_author_url);
    // 글 정보
    $post = get_post($c_cmt->comment_post_ID);
    // 글 작성자
    $post_user = get_userdata($post->post_author);
    $post_user->display_name;
    
    $body = array(
        'url' => get_option('siteurl'),
        'mode' => 'fb',
        's_home_title' => get_option('blogname'),
        's_post_title' => $post->post_title,
        's_name' => $post_user->display_name,
        's_no' => $post->ID,
        's_url' => $post->guid,
        'r1_name' => $p_cmt->comment_author,
        'r1_no' => $p_cmt->comment_ID,
        'r1_pno' => $post->ID,
        'r1_rno' => 0,
        'r1_homepage' => $p_cmt->comment_author_url,
        'r1_regdate' => strtotime($p_cmt->comment_date),
        'r1_url' => $post->guid.'#comment-'.$p_cmt->comment_ID,
        'r2_name' => $c_cmt->comment_author,
        'r2_no' => $c_cmt->comment_ID,
        'r2_pno' => $post->ID,
        'r2_rno' => $p_cmt->comment_ID,
        'r2_homepage' => $c_cmt->comment_author_url,
        'r2_regdate' => strtotime($c_cmt->comment_date),
        'r2_url' => $post->guid.'#comment-'.$c_cmt->comment_ID,
        'r1_body' => $p_cmt->comment_content,
        'r2_body' => $c_cmt->comment_content
    );
    
    wp_remote_post($p_cmt->comment_author_url, array('body' => $body));
}

function wprn_reply_receive() {
    global $wpdb, $table_prefix;

    if ( $_POST['mode'] != 'fb' ) return;

    // is reply receive?
    if ( !wprn_get_option('reply_receive') ) exit;

    $fields = array(
        'url', 's_home_title', 's_post_title', 's_name', 's_no', 's_url',
        'r1_name', 'r1_no', 'r1_pno', 'r1_rno', 'r1_homepage', 'r1_regdate', 'r1_url', 'r1_body',
        'r2_name', 'r2_no', 'r2_pno', 'r2_rno', 'r2_homepage', 'r2_regdate', 'r2_url', 'r2_body'
    );
    
    if ( get_magic_quotes_gpc() ) {
        foreach ($fields as $field)
            $_POST[$field] = stripslashes( trim($_POST[$field]) );
    } else {
        foreach ($fields as $field)
            $_POST[$field] = trim($_POST[$field]);
    }


    ## Required field check ##
    $required_fields = array(
        'url', 's_post_title', 's_no', 's_url',
        'r1_name', 'r1_no', 'r1_pno', 'r1_homepage',
        'r2_name', 'r2_no', 'r2_pno', 'r2_rno',
        'r1_body', 'r2_body'
    );
    
    foreach ( $required_fields as $field ) {
        if ( empty($_POST[$field]) ) return;
    }


    ## Duplicate check ##
    $sql = "SELECT * FROM {$table_prefix}wprn_notified ORDER BY id DESC LIMIT 1";
    $row = $wpdb->get_row($sql, ARRAY_A);
    
    if ( $row['url'] == $_POST['url'] &&
         $row['r2_no'] == $_POST['r2_no'] ) return;


    ## Insert table ##
    $_POST['r1_regdate'] = date('Y-m-d H:i:s', $_POST['r1_regdate']);
    $_POST['r2_regdate'] = date('Y-m-d H:i:s', $_POST['r2_regdate']);
    
    $values = array();
    foreach ($fields as $field) {
        $values[] = $wpdb->escape($_POST[$field]);
    }
    
    $values = "'".implode("','", $values)."'";
    $fields = "`".implode("`,`", $fields)."`";
    
    $sql = "INSERT INTO `{$table_prefix}wprn_notified` ({$fields}) VALUES ({$values})";
    $wpdb->query($sql);


    ## Send mail ##
    if ( wprn_get_option('receive_email') ) {
        $blog_name = get_bloginfo('name');
        $user_info = get_userdata(1);
        $subject = '[' . $blog_name . '] ' . __('Reply', 'wprn') . ': ' . $_POST['s_post_title'];
        
        ob_start();
        include dirname(__FILE__) . '/mail-tpl.php';
        $message = ob_get_contents();
        ob_end_clean();
        
        $headers = 'Content-Type: text/html; charset=utf-8';
        wp_mail($user_info->user_email, $subject, $message, $headers);
    }

    exit;
}

function wprn_delete_items($id) {
    global $wpdb, $table_prefix;

    if ( empty($id) ) return false;

    if ( !is_array($id) ) $id = array($id);
    
    foreach ($id as $k=>$v)
        $id[$k] = $wpdb->escape($v);
    
    $id = implode("','", $id);
    $sql = "DELETE FROM {$table_prefix}wprn_notified WHERE id IN('$id')";
    $wpdb->query($sql);
    
    return true;
}

function wprn_delete_period_exceeds() {
    global $wpdb, $table_prefix;

    $keep_period = wprn_get_option('keep_period');
    if ( !$keep_period ) return;
    
    $keep_date = date('Y-m-d H:i:s', time() - $keep_period * 86400);
    
    $slq = "DELETE FROM {$table_prefix}wprn_notified WHERE r2_regdate < '{$keep_date}'";
    $wpdb->query($sql);
}

function wprn_get_option($option_name = '') {
    global $wpdb, $table_prefix;
    static $options;
    
    if ( !isset($options) ) {
        $sql = "SELECT * FROM `{$table_prefix}wprn_options`";
        $rows = $wpdb->get_results($sql, ARRAY_A);
        if ( !is_array($rows) ) $rows = array();

        foreach ( $rows as $row )
            $options[$row['option_name']] = $row['option_value'];
    }

    return empty($option_name) ? $options : $options[$option_name];
}

function wprn_save_options($options) {
    global $wpdb, $table_prefix;
    
    $options['reply_receive'] = empty($options['reply_receive']) ? 0 : 1;
    $options['reply_notify'] = empty($options['reply_notify']) ? 0 : 1;
    $options['receive_email'] = empty($options['receive_email']) ? 0 : 1;
    
    settype($options['items_per_page'], 'int');
    if ( $options['items_per_page'] < 1 ) $options['items_per_page'] = 10;
    
    settype($options['keep_period'], 'int');
    if ( $options['keep_period'] < 0 ) $options['keep_period'] = 0;
    
    foreach ( $options as $name=>$value ) {
        $sql = "UPDATE `{$table_prefix}wprn_options`
                SET `option_value` = '{$value}'
                WHERE `option_name` = '{$name}'";
        $wpdb->query($sql);
    }
}

function wprn_install() {
    global $wpdb, $table_prefix;
    
    $sql = "SHOW TABLES LIKE '{$table_prefix}wprn_notified'";
    
    if ( $wpdb->query($sql) === 0 ) {
        $sql = "CREATE TABLE `{$table_prefix}wprn_notified` (
                    `id` int unsigned NOT NULL auto_increment,
                    `url` varchar(100) NOT NULL,
                    `s_home_title` varchar(50) NOT NULL,
                    `s_post_title` varchar(100) NOT NULL,
                    `s_name` varchar(20) NOT NULL,
                    `s_no` int unsigned NOT NULL,
                    `s_url` varchar(100) NOT NULL,
                    `r1_name` varchar(20) NOT NULL,
                    `r1_no` int unsigned NOT NULL,
                    `r1_pno` int unsigned NOT NULL,
                    `r1_rno` int unsigned NOT NULL,
                    `r1_homepage` varchar(100) NOT NULL,
                    `r1_regdate` datetime NOT NULL default '0000-00-00 00:00:00',
                    `r1_url` varchar(200) NOT NULL,
                    `r1_body` text NOT NULL,
                    `r2_name` varchar(20) NOT NULL,
                    `r2_no` int unsigned NOT NULL,
                    `r2_pno` int unsigned NOT NULL,
                    `r2_rno` int unsigned NOT NULL,
                    `r2_homepage` varchar(100) NOT NULL,
                    `r2_regdate` datetime NOT NULL default '0000-00-00 00:00:00',
                    `r2_url` varchar(200) NOT NULL,
                    `r2_body` text NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `r2_name_idx`(`r2_name`)
                ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
        $wpdb->query($sql);
    }
    
    
    $sql = "SHOW TABLES LIKE '{$table_prefix}wprn_options'";

    if ( $wpdb->query($sql) === 0 ) {
        $sql = "CREATE TABLE `{$table_prefix}wprn_options` (
                    `option_name` varchar(30) NOT NULL default '',
                    `option_value` text NOT NULL,
                    PRIMARY KEY (`option_name`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $wpdb->query($sql);
        
        $options = array(
            'reply_receive'=>'1',
            'reply_notify'=>'1',
            'receive_email'=>'1',
            'items_per_page'=>'10',
            'keep_period'=>'0'
        );
        
        foreach ( $options as $name=>$value ) {
            $sql = "INSERT INTO `{$table_prefix}wprn_options` VALUES('{$name}', '{$value}')";
            $wpdb->query($sql);
        }
    }
    
    // for upgrade from under 1.1 version
    $wpdb->query("ALTER TABLE `{$table_prefix}wprn_notified` ADD INDEX `r2_name_idx`(`r2_name`)");
}

function wprn_uninstall() {
    global $wpdb, $table_prefix;
    
    $sql = "DROP TABLE `{$table_prefix}wprn_notified`";
    $wpdb->query($sql);
    
    $sql = "DROP TABLE `{$table_prefix}wprn_options`";
    $wpdb->query($sql);
}
?>
