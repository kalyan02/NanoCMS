<html>
<head>
<title><?php _lt('NanoCMS Admin Login'); ?></title>
<link rel='stylesheet' href='admin-design/stuff/admin.css' />
<style type='text/css'>
#login_page { width:250px; margin:auto; margin-top:170px; }
table { background:#fff; border:2px solid #C3D9FF; }
th { padding:5px; }
td { padding:5px; }
</style>
<?php runTweak('login-head'); ?>
</head>
<body>
    <div id='login_page'>
    <p align='center'><?php echo $loginbox_msg; ?></p>
    <table align='center' border='1' cellpadding='8'>
    <form action='?' method='post' accept-charset='utf-8'>
    <tr class='th'><th colspan=2 align='center'><?php _lt('NanoCMS Admin Login'); ?></th></tr>
    <tr><td><?php _lt('Username'); ?></td><td><input type='text' name='user'></td></tr>
    <tr><td><?php _lt('Password'); ?></td><td><input type='password' name='pass'></td></tr>
    <tr><td colspan='2' align='right'><input type='submit' value='<?php _lt('Login'); ?>'></td></tr>
    </form>
    </table>
    <?php runTweak('login-footer'); ?>
    <small>&copy; <a href='http://nanocms.in/'>NanoCMS</a>, <a href='http://KalyanChakravarthy.net/'>Kalyan</a></small>
    </div>
</body>
</html>