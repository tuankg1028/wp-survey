<?php
/*
Plugin Name: Formidable Polylang
Description: Add multilingual support for Formidable
Plugin URI: http://formidablepro.com/
Author: Strategy11
Author URI: http://formidablepro.com
Version: 1.07
*/

if ( ! defined('ABSPATH') ) {
	die();
}

function frm_polylang_autoloader($class_name) {
	if ( ! preg_match( '/^FrmPll.+$/', $class_name ) ) {
        return;
    }

    $filepath = dirname(__FILE__);

    if ( preg_match('/^.+Helper$/', $class_name) ) {
        $filepath .= '/helpers';
    } else if ( preg_match('/^.+Controller$/', $class_name) ) {
        $filepath .= '/controllers';
    } else {
        $filepath .= '/models';
    }

    $filepath .= '/'. $class_name .'.php';

    if ( file_exists( $filepath ) ) {
        include( $filepath );
    }
}

// Add the autoloader
spl_autoload_register('frm_polylang_autoloader');

FrmPllAppController::load_hooks();
FrmPllSettingsController::load_hooks();
