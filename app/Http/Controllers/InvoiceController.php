<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Preview invoice PDF (inline in browser) - Admin only.
     */
    public function preview(int $order_id, Request $request): Response
    {
        $this->authorizeAdmin($order_id, $request);

        $order = wc_get_order($order_id);

        if (! $order) {
            abort(404, __('Bestelling niet gevonden', 'sage'));
        }

        if (! $this->invoiceService->canGenerateInvoice($order)) {
            abort(403, __('Kan geen factuur genereren voor deze bestelling', 'sage'));
        }

        return $this->invoiceService->preview($order);
    }

    /**
     * Download invoice PDF - Admin only.
     */
    public function download(int $order_id, Request $request): Response
    {
        $this->authorizeAdmin($order_id, $request);

        $order = wc_get_order($order_id);

        if (! $order) {
            abort(404, __('Bestelling niet gevonden', 'sage'));
        }

        if (! $this->invoiceService->canGenerateInvoice($order)) {
            abort(403, __('Kan geen factuur genereren voor deze bestelling', 'sage'));
        }

        return $this->invoiceService->download($order);
    }

    /**
     * Authorize admin access with nonce verification.
     */
    protected function authorizeAdmin(int $order_id, Request $request): void
    {
        // Check user has admin/shop manager capabilities
        if (! current_user_can('manage_woocommerce')) {
            abort(403, __('Toegang geweigerd', 'sage'));
        }

        // Verify nonce
        $nonce = $request->query('key');
        if (! wp_verify_nonce($nonce, 'invoice_' . $order_id)) {
            abort(403, __('Beveiligingstoken ongeldig of verlopen', 'sage'));
        }
    }
}
