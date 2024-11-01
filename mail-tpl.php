<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

<h2>[<?php echo get_bloginfo('name'); ?>] <?php _e('Reply Notify', 'wprn'); ?></h2>
<ul>
    <li>
        <strong><?php _e('Blog', 'wprn'); ?>:</strong>
        <a href="<?php echo $_POST['url']; ?>" target="_blank"><?php echo htmlspecialchars($_POST['s_home_title']); ?></a>
    </li>
    <li>
        <strong><?php _e('Post', 'wprn'); ?>:</strong>
        <a href="<?php echo $_POST['s_url']; ?>" target="_blank"><?php echo htmlspecialchars($_POST['s_post_title']); ?></a>
    </li>
    <li>
        <strong><?php echo htmlspecialchars($_POST['r1_name']); ?> <?php _e('Says', 'wprn'); ?>:</strong>
        <a href="<?php echo $_POST['r1_url']; ?>" target="_blank"><?php echo $_POST['r1_regdate']; ?></a><br/>
        <?php echo htmlspecialchars($_POST['r1_body']); ?><br/>
    </li>
    <li>
        <strong><?php echo htmlspecialchars($_POST['r2_name']); ?> <?php _e('Reply', 'wprn'); ?>:</strong>
        <a href="<?php echo $_POST['r2_url']; ?>" target="_blank"><?php echo $_POST['r2_regdate']; ?></a><br/>
        <?php echo htmlspecialchars($_POST['r2_body']); ?><br/>
    </li>
</ul>

</body>
</html>
