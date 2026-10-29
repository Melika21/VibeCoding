<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! defined( 'RCS_UNINSTALLING' ) ) {
	define( 'RCS_UNINSTALLING', true );
}

require_once __DIR__ . '/plugin.php';

RCS_Core_Uninstaller::uninstall();
