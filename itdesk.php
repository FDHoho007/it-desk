<?php
/**
 * Plugin Name: IT Desk
 * Description: Geräte- und Problemverwaltung für Schulen, Unternehmen, etc.
 * Author: Fabian Dietrich
 * Author URI: https://fdhoho007.de/
 * Version: 2.0.0
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
require "include/it.php";
$wp_dir = dirname(__FILE__) . "/wordpress/";
foreach (scandir($wp_dir) as $file)
    if (!is_dir($wp_dir . $file))
        require $wp_dir . $file;
new Wordpress();