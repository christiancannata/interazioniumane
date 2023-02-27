<?php

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

global $wpdb;

delete_option( 'wcdcp_enable' );