<?php

namespace htrxuan\hdlpz;

if (!defined('ABSPATH')) {
    exit;
}

class HDLPZ_Admin
{

    const NONCE_ACTION = 'hdlpz_save_zones';

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
        require_once HDLPZ_PLUGIN_DIR . 'includes/class-hdlpz-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
        add_action('admin_post_hdlpz_save_zones', array($this, 'handle_save'));
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['delivery-zones'] = array(
            'label'  => __('Delivery Zones', 'hdwebmobile-delivery-zones'),
            'order'  => 155,
            'render' => array($this, 'render_page'),
        );
        return $tabs;
    }

    public function render_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to do this.', 'hdwebmobile-delivery-zones'));
        }

        if (!empty($_GET['hdlpz_saved'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a read-only success flag on the redirect back from a nonce-checked save, not a state change.
            echo '<div class="notice notice-success"><p>' . esc_html__('Delivery zones saved.', 'hdwebmobile-delivery-zones') . '</p></div>';
        }

        $zones = HDLPZ_Repository::get_zones();
        ?>
        <p><?php esc_html_e('Add a fee (or a pickup note) for orders shipping to a matching postcode. A postcode pattern ending in * matches any postcode with that prefix (e.g. "9021*"). The fee actually charged is always looked up server-side from the shopper\'s own shipping address -- there is nothing for a shopper to select or submit.', 'hdwebmobile-delivery-zones'); ?></p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="hdlpz_save_zones" />
            <?php wp_nonce_field(self::NONCE_ACTION, 'hdlpz_nonce'); ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Zone name', 'hdwebmobile-delivery-zones'); ?></th>
                        <th><?php esc_html_e('Postcodes (comma-separated)', 'hdwebmobile-delivery-zones'); ?></th>
                        <th><?php esc_html_e('Fee', 'hdwebmobile-delivery-zones'); ?></th>
                        <th><?php esc_html_e('Note shown at checkout', 'hdwebmobile-delivery-zones'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rows = $zones;
                    $rows[] = array('id' => '', 'name' => '', 'postcodes' => array(), 'fee' => '', 'note' => '');
                    foreach ($rows as $i => $zone) :
                        ?>
                        <tr>
                            <td>
                                <input type="hidden" name="zones[<?php echo (int) $i; ?>][id]" value="<?php echo esc_attr($zone['id']); ?>" />
                                <input type="text" name="zones[<?php echo (int) $i; ?>][name]" value="<?php echo esc_attr($zone['name']); ?>" placeholder="<?php esc_attr_e('Downtown', 'hdwebmobile-delivery-zones'); ?>" style="width:100%;" />
                            </td>
                            <td><input type="text" name="zones[<?php echo (int) $i; ?>][postcodes]" value="<?php echo esc_attr(implode(', ', (array) $zone['postcodes'])); ?>" placeholder="90210, 9021*" style="width:100%;" /></td>
                            <td><input type="number" step="any" min="0" name="zones[<?php echo (int) $i; ?>][fee]" value="<?php echo esc_attr($zone['fee']); ?>" style="width:100px;" /></td>
                            <td><input type="text" name="zones[<?php echo (int) $i; ?>][note]" value="<?php echo esc_attr($zone['note']); ?>" placeholder="<?php esc_attr_e('Available for pickup at our Downtown store.', 'hdwebmobile-delivery-zones'); ?>" style="width:100%;" /></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="description"><?php esc_html_e('Leave the last row\'s zone name blank to skip it.', 'hdwebmobile-delivery-zones'); ?></p>
            <?php submit_button(__('Save Delivery Zones', 'hdwebmobile-delivery-zones')); ?>
        </form>
        <?php
    }

    public function handle_save()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to do this.', 'hdwebmobile-delivery-zones'));
        }
        check_admin_referer(self::NONCE_ACTION, 'hdlpz_nonce');

        $rows = isset($_POST['zones']) && is_array($_POST['zones']) ? wp_unslash($_POST['zones']) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- every field is individually sanitized below, and HDLPZ_Repository::save_zones() never trusts caller input regardless of this authorization gate.
        $sanitized = array();
        foreach ($rows as $row) {
            $sanitized[] = array(
                'id'        => isset($row['id']) ? sanitize_key($row['id']) : '',
                'name'      => isset($row['name']) ? sanitize_text_field($row['name']) : '',
                'postcodes' => isset($row['postcodes']) ? sanitize_text_field($row['postcodes']) : '',
                'fee'       => isset($row['fee']) ? sanitize_text_field($row['fee']) : '',
                'note'      => isset($row['note']) ? sanitize_text_field($row['note']) : '',
            );
        }

        HDLPZ_Repository::save_zones($sanitized);

        wp_safe_redirect(admin_url('admin.php?page=hdwebmobile&tab=delivery-zones&hdlpz_saved=1'));
        exit;
    }
}
