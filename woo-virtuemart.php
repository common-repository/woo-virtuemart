<?php

/*
Plugin Name: Virtuemart to woocoommerce
Plugin URI: https://storina.com/
Description: this plugin imports products, medias, product categories, product attributes from virtuemart to woocommerce
Version: 1.1
Author: onliner
Author URI: https://storina.com
Tested up to: 5.3
License: A "Slug" license name e.g. GPL2
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
set_time_limit(1000);
include "define.php";
include "models/migration.php";
include "models/initial_settings.php";