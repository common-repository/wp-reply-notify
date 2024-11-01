<?php
/*
Plugin Name: WP-Reply Notify
Plugin URI: http://ani2life.com
Description: When you answer comments, you will konw about that. And that is compatiable with blog related Tattertools like Tistroy and Textcube. And Tistory and Textcube are very popular blog platform in Korea. 티스토리나 텍스트큐브 같은 테터툴즈 계열 블로그와 호환되는 댓글 알리미 입니다.
Author: A2
Version: 1.1
Author URI: http://ani2life.com
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/*
WP-Reply Notify
Copyright (C) 2009 박민권, ani2life@gmail.com

이 프로그램은 자유 소프트웨어입니다. 소프트웨어의 피양도자는 자유 소프트웨어 재단이 공표한 GNU 일반 공중 사용 허가서 2판 또는 그 이후 판을 임의로 선택해서, 그 규정에 따라 프로그램을 개작하거나 재배포할 수 있습니다.

이 프로그램은 유용하게 사용될 수 있으리라는 희망에서 배포되고 있지만, 특정한 목적에 맞는 적합성 여부나 판매용으로 사용할 수 있으리라는 묵시적인 보증을 포함한 어떠한 형태의 보증도 제공하지 않습니다. 보다 자세한 사항에 대해서는 GNU 일반 공중 사용 허가서를 참고하시기 바랍니다.

GNU 일반 공중 사용 허가서는 이 프로그램과 함께 제공됩니다. 만약, 이 문서가 누락되어 있다면 자유 소프트웨어 재단으로 문의하시기 바랍니다. (자유 소프트웨어 재단: Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA) 
*/
global $wpdb, $table_prefix;

## Load Languages File ##
load_plugin_textdomain('wprn','wp-content/plugins/wp-reply-notify/languages');


## Load functions ##
require_once(dirname(__FILE__).'/functions.php');


## Hooks ##
add_action('comment_post', 'wprn_reply_notify', 1, 2);
add_action('init', 'wprn_reply_receive', 1);
add_action('admin_menu', 'wprn_init');

if (isset($_GET['activate']) && $_GET['activate'] == 'true') {
    add_action('init', 'wprn_install');
}


## Events ##
// delete notified
if ( isset($_POST['wprn_delete']) ) {
    if ( wprn_delete_items($_POST['id']) ) {
        $wprn_message = '<div class="updated fade" id="message"><p>'.__('Deleted Items.', 'wprn').'</p></div>';
    }
}

// save settings
if ( isset($_POST['wprn_settings']) ) {
    $options = array(
        'reply_receive'=>$_POST['reply_receive'],
        'reply_notify'=>$_POST['reply_notify'],
        'receive_email'=>$_POST['receive_email'],
        'items_per_page'=>$_POST['items_per_page'],
        'keep_period'=>$_POST['keep_period']
    );
    
    wprn_save_options($options);
    
    $wprn_message = '<div class="updated fade" id="message"><p>'.__('Settings saved.', 'wprn').'</p></div>';
}

// uninstall
if ( isset($_POST['wprn_uninstall']) ) {
    if ( $_POST['agree'] ) {
        wprn_uninstall();
        $wprn_uninstall = true;
        $wprn_message = '<div class="updated fade" id="message"><p>'.__('Uninstall Completed.', 'wprn').'</p></div>';
    }
}
?>
