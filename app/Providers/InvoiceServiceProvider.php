<?php

namespace App\Providers;

use App\Services\InvoiceService;
use Illuminate\Support\ServiceProvider;
use WC_Order;

class InvoiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(InvoiceService::class, function ($app) {
            return new InvoiceService();
        });

        $this->app->alias(InvoiceService::class, 'invoice');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add admin buttons to WooCommerce order edit page
        add_action('woocommerce_admin_order_data_after_order_details', [$this, 'addAdminOrderButtons']);

        // Add meta box for invoice settings
        add_action('add_meta_boxes', [$this, 'addInvoiceMetaBox']);
        add_action('woocommerce_process_shop_order_meta', [$this, 'saveInvoiceMetaBox']);

        // Add invoice actions to order actions column
        add_filter('woocommerce_admin_order_actions', [$this, 'addOrderActions'], 100, 2);
        add_action('admin_head', [$this, 'addOrderActionsCSS']);
    }

    /**
     * Add invoice buttons to admin order page.
     */
    public function addAdminOrderButtons(WC_Order $order): void
    {
        $invoiceService = app(InvoiceService::class);

        if (! $invoiceService->canGenerateInvoice($order)) {
            return;
        }

        $previewUrl = add_query_arg([
            'key' => wp_create_nonce('invoice_' . $order->get_id()),
        ], home_url('/factuur/' . $order->get_id() . '/preview/'));

        $downloadUrl = add_query_arg([
            'key' => wp_create_nonce('invoice_' . $order->get_id()),
        ], home_url('/factuur/' . $order->get_id() . '/download/'));

        echo '<div class="order-invoice-actions" style="clear: both; margin-top: 20px; padding: 12px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<p style="margin: 0 0 8px 0; font-weight: 600;">' . esc_html__('Factuur', 'sage') . '</p>';
        echo '<div style="display: flex; gap: 8px;">';
        echo '<a href="' . esc_url($previewUrl) . '" target="_blank" class="button button-secondary">' .
             '<span class="dashicons dashicons-visibility" style="margin-top: 3px;"></span> ' .
             esc_html__('Bekijk', 'sage') . '</a>';
        echo '<a href="' . esc_url($downloadUrl) . '" class="button button-secondary">' .
             '<span class="dashicons dashicons-download" style="margin-top: 3px;"></span> ' .
             esc_html__('Download', 'sage') . '</a>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Add invoice meta box to order edit page.
     */
    public function addInvoiceMetaBox(): void
    {
        // HPOS-compatible (WooCommerce 8.2+)
        add_meta_box(
            'invoice_settings',
            __('Factuur instellingen', 'sage'),
            [$this, 'renderInvoiceMetaBox'],
            'woocommerce_page_wc-orders',
            'side',
            'default'
        );

        // Legacy support for non-HPOS
        add_meta_box(
            'invoice_settings',
            __('Factuur instellingen', 'sage'),
            [$this, 'renderInvoiceMetaBox'],
            'shop_order',
            'side',
            'default'
        );
    }

    /**
     * Render invoice meta box.
     */
    public function renderInvoiceMetaBox($post_or_order): void
    {
        $order = $post_or_order instanceof WC_Order
            ? $post_or_order
            : wc_get_order($post_or_order->ID);

        if (! $order) {
            return;
        }

        $invoiceService = app(InvoiceService::class);
        $invoiceNumber = $order->get_meta('_invoice_number');
        $invoiceDate = $order->get_meta('_invoice_date');
        $defaultNumber = 'DB-' . $order->get_order_number();

        wp_nonce_field('invoice_meta_box', 'invoice_meta_box_nonce');
        ?>
        <p>
            <label for="invoice_number"><strong><?php esc_html_e('Factuurnummer:', 'sage'); ?></strong></label>
            <input type="text" id="invoice_number" name="invoice_number"
                   value="<?php echo esc_attr($invoiceNumber); ?>"
                   placeholder="<?php echo esc_attr($defaultNumber); ?>"
                   class="widefat"
                   style="margin-top: 4px;">
            <span class="description" style="font-size: 11px; color: #666;">
                <?php esc_html_e('Laat leeg voor standaard nummer', 'sage'); ?>
            </span>
        </p>
        <p>
            <label for="invoice_date"><strong><?php esc_html_e('Factuurdatum:', 'sage'); ?></strong></label>
            <input type="text" id="invoice_date" name="invoice_date"
                   value="<?php echo esc_attr($invoiceDate); ?>"
                   placeholder="<?php echo esc_attr($invoiceService->getInvoiceDate($order)); ?>"
                   class="widefat"
                   style="margin-top: 4px;">
            <span class="description" style="font-size: 11px; color: #666;">
                <?php esc_html_e('Formaat: dd-mm-jjjj', 'sage'); ?>
            </span>
        </p>
        <?php
    }

    /**
     * Save invoice meta box data.
     */
    public function saveInvoiceMetaBox($order_id): void
    {
        if (! isset($_POST['invoice_meta_box_nonce']) ||
            ! wp_verify_nonce($_POST['invoice_meta_box_nonce'], 'invoice_meta_box')) {
            return;
        }

        $order = wc_get_order($order_id);
        if (! $order) {
            return;
        }

        if (isset($_POST['invoice_number'])) {
            $invoiceNumber = sanitize_text_field($_POST['invoice_number']);
            if ($invoiceNumber) {
                $order->update_meta_data('_invoice_number', $invoiceNumber);
            } else {
                $order->delete_meta_data('_invoice_number');
            }
        }

        if (isset($_POST['invoice_date'])) {
            $invoiceDate = sanitize_text_field($_POST['invoice_date']);
            if ($invoiceDate) {
                $order->update_meta_data('_invoice_date', $invoiceDate);
            } else {
                $order->delete_meta_data('_invoice_date');
            }
        }

        $order->save();
    }

    /**
     * Add invoice actions to order actions column.
     */
    public function addOrderActions(array $actions, WC_Order $order): array
    {
        $invoiceService = app(InvoiceService::class);

        if (! $invoiceService->canGenerateInvoice($order)) {
            return $actions;
        }

        $previewUrl = add_query_arg([
            'key' => wp_create_nonce('invoice_' . $order->get_id()),
        ], home_url('/factuur/' . $order->get_id() . '/preview/'));

        $downloadUrl = add_query_arg([
            'key' => wp_create_nonce('invoice_' . $order->get_id()),
        ], home_url('/factuur/' . $order->get_id() . '/download/'));

        $actions['invoice_preview'] = [
            'url' => $previewUrl,
            'name' => __('Bekijk factuur', 'sage'),
            'action' => 'invoice_preview',
        ];

        $actions['invoice_download'] = [
            'url' => $downloadUrl,
            'name' => __('Download factuur', 'sage'),
            'action' => 'invoice_download',
        ];

        return $actions;
    }

    /**
     * Add CSS for invoice action icons.
     */
    public function addOrderActionsCSS(): void
    {
        ?>
        <style>
            .invoice_preview::after,
            .invoice_download::after {
                font-family: 'Dashicons';
                speak: never;
                font-weight: 400;
                font-variant: normal;
                text-transform: none;
                line-height: 1;
                -webkit-font-smoothing: antialiased;
                margin: 0;
                text-indent: 0;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                text-align: center;
            }

            .invoice_preview::after {
                content: '\f177'; /* dashicons-visibility */
            }

            .invoice_download::after {
                content: '\f316'; /* dashicons-download */
            }
        </style>
        <?php
    }
}
