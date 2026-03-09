<?php

namespace App\Services;

use App\View\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Illuminate\Http\Response;
use WC_Order;

class InvoiceService
{
    /**
     * Generate invoice number from order.
     */
    public function getInvoiceNumber(WC_Order $order): string
    {
        // Check for custom invoice number meta
        $customNumber = $order->get_meta('_invoice_number');
        if ($customNumber) {
            return $customNumber;
        }

        // Default: DB-{order_number}
        return 'DB-' . $order->get_order_number();
    }

    /**
     * Generate invoice date.
     */
    public function getInvoiceDate(WC_Order $order): string
    {
        // Check for custom invoice date meta
        $customDate = $order->get_meta('_invoice_date');
        if ($customDate) {
            return $customDate;
        }

        // Default: order date paid or order date created
        $date = $order->get_date_paid() ?? $order->get_date_created();

        return $date ? $date->date('d-m-Y') : date('d-m-Y');
    }

    /**
     * Build invoice data from WooCommerce order.
     */
    public function buildInvoiceData(WC_Order $order): Invoice
    {
        return Invoice::fromOrder(
            $order,
            $this->getInvoiceNumber($order),
            $this->getInvoiceDate($order)
        );
    }

    /**
     * Generate PDF from order.
     */
    public function generatePdf(WC_Order $order): DomPDF
    {
        $data = $this->buildInvoiceData($order);

        return Pdf::loadView('pdf.invoice', ['invoice' => $data])
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true);
    }

    /**
     * Get PDF as inline stream (for browser preview).
     */
    public function preview(WC_Order $order): Response
    {
        $pdf = $this->generatePdf($order);
        $filename = $this->getFilename($order);

        return $pdf->stream($filename);
    }

    /**
     * Get PDF as download response.
     */
    public function download(WC_Order $order): Response
    {
        $pdf = $this->generatePdf($order);
        $filename = $this->getFilename($order);

        return $pdf->download($filename);
    }

    /**
     * Get raw PDF content (for email attachment).
     */
    public function getContent(WC_Order $order): string
    {
        return $this->generatePdf($order)->output();
    }

    /**
     * Generate filename for PDF.
     */
    protected function getFilename(WC_Order $order): string
    {
        $invoiceNumber = $this->getInvoiceNumber($order);

        return "factuur-{$invoiceNumber}.pdf";
    }

    /**
     * Set custom invoice number on order.
     */
    public function setInvoiceNumber(WC_Order $order, string $number): void
    {
        $order->update_meta_data('_invoice_number', $number);
        $order->save();
    }

    /**
     * Set custom invoice date on order.
     */
    public function setInvoiceDate(WC_Order $order, string $date): void
    {
        $order->update_meta_data('_invoice_date', $date);
        $order->save();
    }

    /**
     * Check if order can have invoice generated.
     */
    public function canGenerateInvoice(WC_Order $order): bool
    {
        // Only generate for paid/processing/completed orders
        $allowedStatuses = ['processing', 'completed', 'on-hold'];

        return in_array($order->get_status(), $allowedStatuses);
    }
}
