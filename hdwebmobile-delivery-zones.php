<?php

/**
 * Plugin Name: HDWebmobile Local Pickup & Delivery Zones
 * Plugin URI: https://hdwebmobile.com/plugins/hdwebmobile-delivery-zones/
 * Description: Postcode-based delivery zone fees, resolved server-side from the shopper's own address -- never accepted from the request.
 * Version: 1.0.0
 * Author: htrxuan - Han Tran
 * Author URI: https://hdwebmobile.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hdwebmobile-delivery-zones
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.9
 */

namespace htrxuan\hdlpz;

if (!defined('ABSPATH')) {
    exit;
}

define('HDLPZ_VERSION', '1.0.0');
define('HDLPZ_PLUGIN_FILE', __FILE__);
define('HDLPZ_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HDLPZ_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once HDLPZ_PLUGIN_DIR . 'includes/class-hdlpz-activator.php';

register_activation_hook(__FILE__, array(HDLPZ_Activator::class, 'activate'));

add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', HDLPZ_PLUGIN_FILE, true);
    }
});

add_action('plugins_loaded', function () {
    require_once HDLPZ_PLUGIN_DIR . 'includes/class-hdlpz-core.php';
    HDLPZ_Core::get_instance();
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $donate_link = '<a href="https://paypal.me/htrxuan/20" target="_blank" rel="noopener noreferrer">' . esc_html__('Donate', 'hdwebmobile-delivery-zones') . '</a>';
    array_unshift($links, $donate_link);
    return $links;
});
