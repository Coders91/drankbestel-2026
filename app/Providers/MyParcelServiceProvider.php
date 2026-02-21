<?php

namespace App\Providers;

use App\Services\MyParcel\MyParcelService;
use App\Services\MyParcel\ShipmentBuilder;
use App\Services\MyParcel\WebhookHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use WC_Order;
use WP_REST_Request;
use WP_REST_Response;

class MyParcelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            $this->app->basePath('config/myparcel.php'),
            'myparcel'
        );

        $this->app->singleton(ShipmentBuilder::class);

        $this->app->singleton(MyParcelService::class, function ($app) {
            return new MyParcelService($app->make(ShipmentBuilder::class));
        });

        $this->app->singleton(WebhookHandler::class);
    }

    public function boot(): void
    {
        // Webhook REST route (public)
        add_action('rest_api_init', [$this, 'registerWebhookRoute']);

        if (! is_admin()) {
            return;
        }

        // Barcode column in order list
        add_filter('manage_woocommerce_page_wc-orders_columns', [$this, 'addBarcodeColumn']);
        add_action('manage_woocommerce_page_wc-orders_custom_column', [$this, 'renderBarcodeColumn'], 10, 2);
        // Legacy post-type support
        add_filter('manage_edit-shop_order_columns', [$this, 'addBarcodeColumn']);
        add_action('manage_shop_order_posts_custom_column', [$this, 'renderBarcodeColumnLegacy'], 10, 2);

        // Export action in order row
        add_filter('woocommerce_admin_order_actions', [$this, 'addOrderActions'], 100, 2);

        // AJAX handlers
        add_action('wp_ajax_myparcel_export', [$this, 'handleAjaxExport']);
        add_action('wp_ajax_myparcel_print_label', [$this, 'handlePrintLabel']);

        // Bulk actions
        add_filter('bulk_actions-woocommerce_page_wc-orders', [$this, 'addBulkActions']);
        add_filter('handle_bulk_actions-woocommerce_page_wc-orders', [$this, 'handleBulkExport'], 10, 3);
        // Legacy
        add_filter('bulk_actions-edit-shop_order', [$this, 'addBulkActions']);
        add_filter('handle_bulk_actions-edit-shop_order', [$this, 'handleBulkExport'], 10, 3);

        // Admin notices for export results
        add_action('admin_notices', [$this, 'showExportNotice']);

        // Meta box on order edit
        add_action('add_meta_boxes', [$this, 'addMetaBox']);

        // Inline CSS
        add_action('admin_head', [$this, 'addAdminCSS']);
    }

    // ─── Webhook ─────────────────────────────────────────────────────────

    public function registerWebhookRoute(): void
    {
        // Match the existing webhook URL configured in the MyParcel dashboard
        register_rest_route('woocommerce-myparcel/v1', '/shipment_status_change/(?P<hash>[a-f0-9]+)', [
            'methods' => 'POST',
            'callback' => [$this, 'handleWebhook'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function handleWebhook(WP_REST_Request $request): WP_REST_Response
    {
        try {
            $handler = app(WebhookHandler::class);
            $handler->handle($request->get_json_params());
        } catch (\Exception $e) {
            Log::error('MyParcel webhook error: '.$e->getMessage());
        }

        return new WP_REST_Response(null, 200);
    }

    // ─── Barcode Column ──────────────────────────────────────────────────

    public function addBarcodeColumn(array $columns): array
    {
        $new = [];

        foreach ($columns as $key => $label) {
            $new[$key] = $label;

            if ($key === 'order_status') {
                $new['myparcel_barcode'] = __('Track & Trace', 'sage');
            }
        }

        return $new;
    }

    public function renderBarcodeColumn(string $column, WC_Order $order): void
    {
        if ($column !== 'myparcel_barcode') {
            return;
        }

        $this->outputBarcodeCell($order);
    }

    public function renderBarcodeColumnLegacy(string $column, int $postId): void
    {
        if ($column !== 'myparcel_barcode') {
            return;
        }

        $order = wc_get_order($postId);
        if ($order) {
            $this->outputBarcodeCell($order);
        }
    }

    protected function outputBarcodeCell(WC_Order $order): void
    {
        $service = app(MyParcelService::class);
        $barcode = $order->get_meta('_myparcel_barcode');
        $shipmentId = $order->get_meta('_myparcel_shipment_id');

        // Delivery date from checkout selection
        $deliveryDate = $this->getDeliveryDate($order);
        if ($deliveryDate) {
            echo '<span class="myparcel-delivery-date">'.esc_html($deliveryDate).'</span><br>';
        }

        if ($barcode) {
            $url = $service->getTrackingUrl($order);
            echo '<a href="'.esc_url($url).'" target="_blank" title="'.esc_attr__('Volg zending', 'sage').'">'
                .esc_html($barcode)
                .'</a>';
        } elseif ($shipmentId) {
            echo '<span class="myparcel-no-label">'.esc_html__('Nog geen label', 'sage').'</span>';
        } else {
            echo '<span class="myparcel-empty">&ndash;</span>';
        }
    }

    protected function getDeliveryDate(WC_Order $order): ?string
    {
        $raw = $order->get_meta('_myparcel_delivery_options');

        if (! $raw) {
            return null;
        }

        $data = is_string($raw) ? json_decode($raw, true) : (array) $raw;
        $date = $data['date'] ?? null;

        if (! $date) {
            return null;
        }

        try {
            return (new \DateTime($date))->format('d-m-Y');
        } catch (\Exception) {
            return null;
        }
    }

    // ─── Order Row Action ────────────────────────────────────────────────

    public function addOrderActions(array $actions, WC_Order $order): array
    {
        $service = app(MyParcelService::class);

        if ($service->isExported($order)) {
            $labelUrl = wp_nonce_url(
                admin_url('admin-ajax.php?action=myparcel_print_label&order_id='.$order->get_id()),
                'myparcel_print_label_'.$order->get_id()
            );

            $actions['myparcel_print'] = [
                'url' => $labelUrl,
                'name' => __('Print label', 'sage'),
                'action' => 'myparcel_print',
            ];

            return $actions;
        }

        if (! $service->canExport($order)) {
            return $actions;
        }

        $url = wp_nonce_url(
            admin_url('admin-ajax.php?action=myparcel_export&order_id='.$order->get_id()),
            'myparcel_export_'.$order->get_id()
        );

        $actions['myparcel_export'] = [
            'url' => $url,
            'name' => __('Export naar MyParcel', 'sage'),
            'action' => 'myparcel_export',
        ];

        return $actions;
    }

    // ─── AJAX Export ─────────────────────────────────────────────────────

    public function handleAjaxExport(): void
    {
        $orderId = absint($_GET['order_id'] ?? 0);

        if (! $orderId || ! wp_verify_nonce($_GET['_wpnonce'] ?? '', 'myparcel_export_'.$orderId)) {
            wp_die(__('Ongeldige aanvraag', 'sage'));
        }

        $order = wc_get_order($orderId);

        if (! $order) {
            wp_die(__('Bestelling niet gevonden', 'sage'));
        }

        try {
            $service = app(MyParcelService::class);
            $service->createShipment($order);
        } catch (\Exception $e) {
            Log::error('MyParcel export failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            wp_die(sprintf(__('Export mislukt: %s', 'sage'), $e->getMessage()));
        }

        $redirect = wp_get_referer() ?: admin_url('admin.php?page=wc-orders');
        wp_safe_redirect(add_query_arg('myparcel_exported', 1, $redirect));
        exit;
    }

    // ─── Print Label ─────────────────────────────────────────────────

    public function handlePrintLabel(): void
    {
        $orderId = absint($_GET['order_id'] ?? 0);

        if (! $orderId || ! wp_verify_nonce($_GET['_wpnonce'] ?? '', 'myparcel_print_label_'.$orderId)) {
            wp_die(__('Ongeldige aanvraag', 'sage'));
        }

        $order = wc_get_order($orderId);

        if (! $order) {
            wp_die(__('Bestelling niet gevonden', 'sage'));
        }

        $shipmentId = (int) $order->get_meta('_myparcel_shipment_id');

        if (! $shipmentId) {
            wp_die(__('Geen zending gevonden', 'sage'));
        }

        try {
            $apiKey = config('myparcel.api_key');
            $collection = MyParcelCollection::findMany([$shipmentId], $apiKey);
            $collection->setPdfOfLabels();
            $collection->downloadPdfOfLabels();
            exit;
        } catch (\Exception $e) {
            Log::error('MyParcel label download failed', ['shipment_id' => $shipmentId, 'error' => $e->getMessage()]);
            wp_die(__('Label ophalen mislukt', 'sage'));
        }
    }

    // ─── Bulk Actions ────────────────────────────────────────────────────

    public function addBulkActions(array $actions): array
    {
        $actions['myparcel_bulk_export'] = __('Exporteer naar MyParcel', 'sage');

        return $actions;
    }

    public function handleBulkExport(string $redirectTo, string $action, array $ids): string
    {
        if ($action !== 'myparcel_bulk_export') {
            return $redirectTo;
        }

        $orders = array_filter(array_map('wc_get_order', $ids));
        $service = app(MyParcelService::class);

        try {
            $results = $service->createShipments($orders);
            $count = count($results);
        } catch (\Exception $e) {
            Log::error('MyParcel bulk export failed', ['error' => $e->getMessage()]);
            $count = 0;
        }

        return add_query_arg('myparcel_exported', $count, $redirectTo);
    }

    public function showExportNotice(): void
    {
        if (! isset($_GET['myparcel_exported'])) {
            return;
        }

        $count = absint($_GET['myparcel_exported']);
        $message = sprintf(
            _n(
                '%d bestelling geëxporteerd naar MyParcel.',
                '%d bestellingen geëxporteerd naar MyParcel.',
                $count,
                'sage'
            ),
            $count
        );

        echo '<div class="notice notice-success is-dismissible"><p>'.esc_html($message).'</p></div>';
    }

    // ─── Meta Box ────────────────────────────────────────────────────────

    public function addMetaBox(): void
    {
        add_meta_box(
            'myparcel_shipment',
            __('MyParcel', 'sage'),
            [$this, 'renderMetaBox'],
            'woocommerce_page_wc-orders',
            'side',
            'default'
        );

        // Legacy
        add_meta_box(
            'myparcel_shipment',
            __('MyParcel', 'sage'),
            [$this, 'renderMetaBox'],
            'shop_order',
            'side',
            'default'
        );
    }

    public function renderMetaBox($postOrOrder): void
    {
        $order = $postOrOrder instanceof WC_Order
            ? $postOrOrder
            : wc_get_order($postOrOrder->ID);

        if (! $order) {
            return;
        }

        $service = app(MyParcelService::class);
        $shipmentId = $order->get_meta('_myparcel_shipment_id');
        $barcode = $order->get_meta('_myparcel_barcode');
        $status = $order->get_meta('_myparcel_shipment_status');

        if ($shipmentId) {
            echo '<table class="widefat fixed" style="border:0;box-shadow:none;">';
            echo '<tr><td><strong>'.esc_html__('Zending ID', 'sage').'</strong></td>';
            echo '<td>'.esc_html($shipmentId).'</td></tr>';

            if ($barcode) {
                $trackingUrl = $service->getTrackingUrl($order);
                echo '<tr><td><strong>'.esc_html__('Track & Trace', 'sage').'</strong></td>';
                echo '<td><a href="'.esc_url($trackingUrl).'" target="_blank">'.esc_html($barcode).'</a></td></tr>';
            }

            if ($status) {
                echo '<tr><td><strong>'.esc_html__('Status', 'sage').'</strong></td>';
                echo '<td>'.esc_html($status).'</td></tr>';
            }

            echo '</table>';
        } elseif ($service->canExport($order)) {
            $url = wp_nonce_url(
                admin_url('admin-ajax.php?action=myparcel_export&order_id='.$order->get_id()),
                'myparcel_export_'.$order->get_id()
            );

            echo '<p>'.esc_html__('Deze bestelling is nog niet geëxporteerd.', 'sage').'</p>';
            echo '<a href="'.esc_url($url).'" class="button button-primary">';
            echo '<span class="dashicons dashicons-airplane" style="margin-top:3px;"></span> ';
            echo esc_html__('Exporteer naar MyParcel', 'sage');
            echo '</a>';
        } else {
            echo '<p>'.esc_html__('Bestelling kan niet worden geëxporteerd (status).', 'sage').'</p>';
        }
    }

    // ─── Admin CSS ───────────────────────────────────────────────────────

    public function addAdminCSS(): void
    {
        $screen = get_current_screen();

        if (! $screen || ! in_array($screen->id, ['woocommerce_page_wc-orders', 'edit-shop_order'])) {
            return;
        }

        ?>
        <style>
            .myparcel_export::after,
            .myparcel_print::after {
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

            .myparcel_export::after {
                content: '\f15a'; /* dashicons-airplane */
            }

            .myparcel_print::after {
                content: '\f193'; /* dashicons-media-text (label) */
            }

            .column-myparcel_barcode {
                width: 160px;
            }

            .myparcel-delivery-date {
                font-size: 12px;
                color: #50575e;
            }

            .myparcel-empty {
                color: #999;
            }
        </style>
        <?php
    }
}
