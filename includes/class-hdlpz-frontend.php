<?php

namespace htrxuan\hdlpz;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Applies a zone's fee (and shows its note) based on the shipping postcode WooCommerce's own
 * customer object is currently tracking. There is no zone-selection field on the checkout
 * form at all -- see HDLPZ_Repository's docblock for why that's the point.
 */
final class HDLPZ_Frontend
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('woocommerce_cart_calculate_fees', array($this, 'apply_zone_fee'));
        add_action('woocommerce_review_order_before_payment', array($this, 'render_zone_note'));

        // The block checkout never runs the classic hook above: it gets the note from the
        // Store API cart response instead, so it also follows address changes without a reload.
        add_action('woocommerce_blocks_loaded', array($this, 'register_store_api_data'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_block_checkout_script'));

        // Stamped for the admin's own reference on the finished order (e.g. sorting orders by
        // pickup location) -- registered on both checkout flows, since woocommerce_checkout_
        // create_order alone never fires for the Store API's block checkout.
        add_filter('woocommerce_checkout_create_order', array($this, 'stamp_order'));
        add_action('woocommerce_store_api_checkout_update_order_from_request', array($this, 'stamp_order'));
    }

    private function resolve_current_zone()
    {
        if (!WC()->customer) {
            return null;
        }
        $postcode = WC()->customer->get_shipping_postcode();
        if (!$postcode) {
            $postcode = WC()->customer->get_billing_postcode();
        }
        if (!$postcode) {
            return null;
        }
        return HDLPZ_Repository::find_zone_for_postcode($postcode);
    }

    public function apply_zone_fee($cart)
    {
        $zone = $this->resolve_current_zone();
        if (!$zone) {
            return;
        }
        // Re-read the fee fresh from the zone's own saved config by id, rather than trusting
        // the value already sitting in $zone from resolve_current_zone() a moment ago -- a
        // defense-in-depth habit, not a functional difference, matching this suite's
        // convention of never assuming a value already in hand is still current.
        $current = HDLPZ_Repository::get_zone($zone['id']);
        if (!$current || $current['fee'] <= 0) {
            return;
        }
        $cart->add_fee($current['name'], $current['fee'], false);
    }

    public function render_zone_note()
    {
        $zone = $this->resolve_current_zone();
        if (!$zone || '' === $zone['note']) {
            return;
        }
        echo '<p class="hdlpz-zone-note">' . esc_html($zone['note']) . '</p>';
    }

    public function register_store_api_data()
    {
        if (!function_exists('woocommerce_store_api_register_endpoint_data')) {
            return;
        }

        woocommerce_store_api_register_endpoint_data(array(
            'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema::IDENTIFIER,
            'namespace'       => 'hdlpz',
            'data_callback'   => array($this, 'get_store_api_data'),
            'schema_callback' => array($this, 'get_store_api_schema'),
            'schema_type'     => ARRAY_A,
        ));
    }

    public function get_store_api_data()
    {
        $zone = $this->resolve_current_zone();
        return array('note' => $zone ? $zone['note'] : '');
    }

    public function get_store_api_schema()
    {
        return array(
            'note' => array(
                'description' => __('Note for the delivery zone matching the shipping postcode', 'hdwebmobile-delivery-zones'),
                'type'        => 'string',
                'context'     => array('view', 'edit'),
                'readonly'    => true,
            ),
        );
    }

    public function enqueue_block_checkout_script()
    {
        if (!is_checkout() || !wp_script_is('wc-blocks-checkout', 'registered')) {
            return;
        }

        wp_enqueue_script(
            'hdlpz-block-checkout',
            HDLPZ_PLUGIN_URL . 'assets/js/hdlpz-block-checkout.js',
            array('wc-blocks-checkout', 'wp-element', 'wp-plugins'),
            HDLPZ_VERSION,
            true
        );
    }

    /**
     * Records which zone applied on the finished order, purely for the admin's own reference
     * (e.g. picking orders by pickup location) -- this is a label, never re-trusted as a fee
     * or eligibility source on its own.
     */
    public function stamp_order($order)
    {
        $zone = $this->resolve_current_zone();
        if ($zone) {
            $order->update_meta_data('_hdlpz_zone_name', $zone['name']);
        }
        return $order;
    }
}
