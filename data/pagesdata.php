<pre><?php
include 'config.php';
$NanoCMS_custom_config['nanocms_files_dir'] = '';

print_r( $NanoCMS );
define( 'NANO_CUSTOM', 1 );
include "setting.php";
print_r( $NanoCMS );
?>