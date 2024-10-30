<?php
// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

delete_option("maadne_martinsadnetwork_theme");
delete_option("maadne_martinsadnetwork_position");
delete_option("maadne_martinsadnetwork_offset");