<?php
/**
 * @package VN Location
 */
/*
Plugin Name: VN Location
Plugin URI: http://google.com
Description: Store location to post.
Version: 1.0.0
Author: Vu Dang
Author URI: http://google.com
License: GPLv2 or later
Text Domain: Vu Dang
*/

define('PATH_PLUGIN', plugin_dir_url(__FILE__));
require_once 'inc/meta-box.php';
register_activation_hook(__FILE__, 'v_install_plugin');
register_uninstall_hook(__FILE__, 'v_uninstall_plugin');

function run(){
    $metabox = new Meta_Box();
    $metabox->excute();
}
run();

function v_uninstall_plugin(){
    global $wpdb;
    $tbl_Wards      = $wpdb->prefix . 'wards';
    $tbl_Districts  = $wpdb->prefix . 'districts';
    $tbl_Cities     = $wpdb->prefix . 'cities';
    $wpdb->query("DROP TABLE IF EXISTS $tbl_Wards");
    $wpdb->query("DROP TABLE IF EXISTS $tbl_Districts");
    $wpdb->query("DROP TABLE IF EXISTS $tbl_Cities");
}

function v_install_plugin(){
    global $wpdb;
    $tbl_Ward       = $wpdb->prefix . 'wards';
    $tbl_Cities     = $wpdb->prefix . 'cities';
    $tbl_Districts  = $wpdb->prefix . 'districts';
    
    require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // create table wards
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl_Ward'") != $tbl_Ward){
        $sql = "CREATE TABLE IF NOT EXISTS $tbl_Ward (
                `wardid` varchar(5) NOT NULL,
                `name` varchar(100) NOT NULL,
                `type` varchar(30) NOT NULL,
                `location` varchar(30) NOT NULL,
                `districtid` varchar(5) NOT NULL,
                PRIMARY KEY (`wardid`),
                KEY `districtid` (`districtid`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    
    // Create table cities
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl_Cities'") != $tbl_Cities){
        $sql = "CREATE TABLE IF NOT EXISTS $tbl_Cities (
                `provinceid` varchar(5) NOT NULL,
                `name` varchar(100) NOT NULL,
                `type` varchar(30) NOT NULL,
                PRIMARY KEY (`provinceid`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    
    // Create table cities
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl_Districts'") != $tbl_Districts){
        $sql = "CREATE TABLE IF NOT EXISTS $tbl_Districts (
                `districtid` varchar(5) NOT NULL,
                `name` varchar(100) NOT NULL,
                `type` varchar(30) NOT NULL,
                `location` varchar(30) NOT NULL,
                `provinceid` varchar(5) NOT NULL,
                PRIMARY KEY (`districtid`),
                KEY `provinceid` (`provinceid`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    
    $query_wards        = file_get_contents(PATH_PLUGIN.'wards.txt');
    $query_cities       = file_get_contents(PATH_PLUGIN.'cities.txt');
    $query_districts    = file_get_contents(PATH_PLUGIN.'districts.txt');
    
    $wpdb->query("
        INSERT INTO $tbl_Ward (`wardid`, `name`, `type`, `location`, `districtid`) VALUES $query_wards"
    );
    $wpdb->query("
        INSERT INTO $tbl_Cities (`provinceid`, `name`, `type`) VALUES $query_cities"
    );
    $wpdb->query("
        INSERT INTO $tbl_Districts (`districtid`, `name`, `type`, `location`, `provinceid`) VALUES $query_districts"
    );
}
