@php
  /** @var App\View\Models\Invoice $invoice */
  /** @var App\View\Models\InvoiceItem $item */
@endphp

<style>
  *,
  ::before,
  ::after {
    box-sizing: border-box;
    border-width: 0;
    border-style: solid;
    border-color: #e5e7eb;
  }

  html {
    line-height: 1.5;
    font-family: 'Helvetica', sans-serif;
  }

  body {
    margin: 0;
    line-height: inherit;
    color: #1d232f;
  }

  blockquote, dl, dd, h1, h2, h3, h4, h5, h6, hr, figure, p, pre {
    margin: 0;
  }

  img, svg {
    display: block;
    vertical-align: middle;
  }

  img {
    max-width: 100%;
    height: auto;
  }

  table {
    text-indent: 0;
    border-color: inherit;
    border-collapse: collapse;
  }

  @page {
    margin: 0;
  }

  @media print {
    body {
      -webkit-print-color-adjust: exact;
    }
  }

  /* Layout */
  .w-full { width: 100%; }
  .w-1\/2 { width: 50%; }
  .border-collapse { border-collapse: collapse; }
  .border-spacing-0 { border-spacing: 0; }
  .align-top { vertical-align: top; }
  .whitespace-nowrap { white-space: nowrap; }

  /* Spacing */
  .px-14 { padding-left: 3.5rem; padding-right: 3.5rem; }
  .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
  .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
  .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
  .py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
  .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
  .p-3 { padding: 0.75rem; }
  .pb-3 { padding-bottom: 0.75rem; }
  .pl-2 { padding-left: 0.5rem; }
  .pl-3 { padding-left: 0.75rem; }
  .pr-3 { padding-right: 0.75rem; }
  .pr-4 { padding-right: 1rem; }
  .pl-4 { padding-left: 1rem; }
  .pt-1 { padding-top: 0.25rem; }

  /* Text */
  .text-center { text-align: center; }
  .text-right { text-align: right; }
  .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
  .text-xs { font-size: 0.75rem; line-height: 1rem; }
  .font-semibold { font-weight: 700; }

  /* Colors - using our site palette */
  .text-gray-900 { color: #1d232f; }
  .text-gray-800 { color: #4a5565; }
  .text-gray-700 { color: #4a5565; }
  .text-gray-500 { color: #7183a7; }
  .text-gray-600 { color: #586a8d; }
  .text-gray-400 { color: #909ebb; }
  .text-gray-300 { color: #b0bace; }
  .text-white { color: #fff; }
  .text-green-600 { color: #1a8245; }
  .bg-main { background-color: #1d232f; }
  .bg-gray-100 { background-color: #f0f2f7; }

  /* Borders */
  .border-b { border-bottom-width: 1px; }
  .border-b-2 { border-bottom-width: 2px; }
  .border-r { border-right-width: 1px; }
  .border-main { border-color: #b10b0b; }
  .border-gray-200 { border-color: #cfd5e2; }

  /* Fixed footer */
  .fixed { position: fixed; }
  .bottom-0 { bottom: 0; }
  .left-0 { left: 0; }

  .h-7 { height: 1.75rem; }
  .text-red-600 { color: #b10b0b; }
  .text-2xl { font-size: 1.5rem; line-height: 2rem; }
  .tracking-wide { letter-spacing: 0.05em; }
  .uppercase { text-transform: uppercase; }
  .pb-4 { padding-bottom: 1rem; }
  .border-b-red { border-bottom: 2px solid #b10b0b; }
</style>

<div>
  <div class="py-4">
    {{-- ═══════ HEADER: Logo + Company Details ═══════ --}}
    <div class="px-14 py-4">
      <table class="w-full border-collapse border-spacing-0">
        <tbody>
          <tr>
            <td class="w-full align-top">
              @if($invoice->company_logo)
                <img class="h-7" style="width:auto" src="{{ $invoice->company_logo }}">
              @else
                <p class="font-semibold text-gray-900" style="font-size: 1.25rem;">{{ $invoice->company_name }}</p>
              @endif
            </td>
            <td class="align-top" style="white-space: nowrap;">
              <div class="text-sm text-gray-900">
                <p class="font-semibold">{{ $invoice->company_name }}</p>
                <p>{{ $invoice->company_address }}</p>
                <p>{{ $invoice->company_postcode }} {{ $invoice->company_city }}</p>
                <p>Kvk: {{ $invoice->company_kvk }}</p>
                <p>Btw: {{ $invoice->company_vat }}</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- ═══════ FACTUUR TITLE ═══════ --}}
    <div class="px-14 pb-4">
      <p class="text-2xl font-semibold text-gray-900" style="padding-bottom: 0.5rem;">Factuur</p>
    </div>

    {{-- ═══════ ORDER META ═══════ --}}
    <div class="px-14 pb-4 text-sm text-gray-600">
      <table class="border-collapse border-spacing-0">
        <tbody>
        <tr>
          <td class="pr-4 text-gray-700">Factuurnummer: <span class="font-semibold text-gray-900">{{ $invoice->invoice_number }}</span></td>
          <td class="pl-4 pr-4 text-gray-700" style="border-left: 1px solid #cfd5e2;">Orderdatum: <span class="font-semibold text-gray-900">{{ $invoice->invoice_date }}</span></td>
          <td class="pl-4 pr-4 text-gray-700" style="border-left: 1px solid #cfd5e2;">Ordernummer: <span class="font-semibold text-gray-900">{{ $invoice->order_number }}</span></td>
        </tr>
        </tbody>
      </table>
    </div>

    {{-- ═══════ ADDRESS BAR ═══════ --}}
    <div class="bg-gray-100 px-14 py-6 text-sm">
      <table class="w-full border-collapse border-spacing-0">
        <tbody>
          <tr>
            <td class="w-1/2 align-top">
              <div class="text-sm text-gray-900">
                <p class="font-semibold" style="margin-bottom: 0.5rem;">Factuuradres</p>
              @if($invoice->billing_company)
                  <p>{{ $invoice->billing_company }}</p>
                  @if($invoice->customer_vat_number)
                    <p>{{ $invoice->customer_vat_number }}</p>
                  @endif
                  @if($invoice->customer_reference)
                    <p>{{ $invoice->customer_reference }}</p>
                  @endif
                  <br>
                  <p>T.a.v. {{ $invoice->billing_name }}</p>
                @else
                  <p>{{ $invoice->billing_name }}</p>
                @endif
                <p>{{ $invoice->billing_address }}</p>
                <p>{{ $invoice->billing_postcode }} {{ $invoice->billing_city }}</p>
              </div>
            </td>
            <td class="w-1/2 align-top text-right">
              <div class="text-sm text-gray-900">
                <p class="font-semibold" style="margin-bottom: 0.5rem;">Verzendadres</p>
                <p>{{ $invoice->shipping_name }}</p>
                <p>{{ $invoice->shipping_address }}</p>
                <p>{{ $invoice->shipping_postcode }} {{ $invoice->shipping_city }}</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>



    {{-- ═══════ LINE ITEMS ═══════ --}}
    <div class="px-14 py-8 text-sm text-gray-900">
      <table class="w-full border-collapse border-spacing-0">
        <thead>
          <tr>
            <td class="border-b-2 border-main pb-3 pl-3 font-semibold text-gray-900">Product</td>
            <td class="border-b-2 border-main pb-3 pl-2 text-right font-semibold text-gray-900">Prijs</td>
            <td class="border-b-2 border-main pb-3 pl-2 text-center font-semibold text-gray-900">Aantal</td>
            <td class="border-b-2 border-main pb-3 pl-2 text-center font-semibold text-gray-900">BTW</td>
            <td class="border-b-2 border-main pb-3 pl-2 pr-3 text-right font-semibold text-gray-900">Totaal</td>
          </tr>
        </thead>
        <tbody>
          @foreach($invoice->items as $item)
            <tr>
              <td class="border-b border-gray-200 py-3 pl-3">{!! $item->product->title !!}</td>
              <td class="border-b border-gray-200 py-3 pl-2 text-right">{{ $item->unit_price }}</td>
              <td class="border-b border-gray-200 py-3 pl-2 text-center">{{ $item->quantity }}</td>
              <td class="border-b border-gray-200 py-3 pl-2 text-center">{{ $item->tax_rate }}</td>
              <td class="border-b border-gray-200 py-3 pl-2 pr-3 text-right">{{ $item->subtotal }}</td>
            </tr>
          @endforeach

          {{-- ═══════ TOTALS (nested right-aligned) ═══════ --}}
          <tr>
            <td colspan="5">
              <table class="w-full border-collapse border-spacing-0">
                <tbody>
                  <tr>
                    <td class="w-full"></td>
                    <td>
                      <table class="w-full border-collapse border-spacing-0">
                        <tbody>
                          <tr>
                            <td class="border-b border-gray-200 p-3">
                              <div class="whitespace-nowrap text-gray-800">Subtotaal:</div>
                            </td>
                            <td class="border-b border-gray-200 p-3 text-right">
                              <div class="whitespace-nowrap font-semibold text-gray-900">{{ $invoice->subtotal }}</div>
                            </td>
                          </tr>
                          @if(!$invoice->discount_total->is_free)
                            <tr>
                              <td class="border-b border-gray-200 p-3">
                                <div class="whitespace-nowrap text-green-600">Korting:</div>
                              </td>
                              <td class="border-b border-gray-200 p-3 text-right">
                                <div class="whitespace-nowrap font-semibold text-green-600">-{{ $invoice->discount_total->amount->formatted() }}</div>
                              </td>
                            </tr>
                          @endif
                          @if(!$invoice->shipping_total->is_free)
                            <tr>
                              <td class="border-b border-gray-200 p-3">
                                <div class="whitespace-nowrap text-gray-800">Verzendkosten:</div>
                              </td>
                              <td class="border-b border-gray-200 p-3 text-right">
                                <div class="whitespace-nowrap font-semibold text-gray-900">{{ $invoice->shipping_total }}</div>
                              </td>
                            </tr>
                          @endif
                          @foreach($invoice->tax_lines as $tax)
                            <tr>
                              <td class="border-b border-gray-200 p-3">
                                <div class="whitespace-nowrap text-gray-800">{{ $tax['label'] }}:</div>
                              </td>
                              <td class="border-b border-gray-200 p-3 text-right">
                                <div class="whitespace-nowrap font-semibold text-gray-900">{{ $tax['amount'] }}</div>
                              </td>
                            </tr>
                          @endforeach
                          <tr>
                            <td class="border-b border-gray-200 p-3">
                              <div class="whitespace-nowrap text-gray-800">
                                Totaal incl. {{ count($invoice->tax_lines) > 1 ? $invoice->tax_total : '' }} btw
                              </div>
                            </td>
                            <td class="border-b border-gray-200 p-3 text-right">
                              <div class="whitespace-nowrap font-semibold text-gray-900">{{ $invoice->total }}</div>
                            </td>
                          </tr>
                          <tr>
                            <td class="bg-main p-3">
                              <div class="whitespace-nowrap font-semibold text-white">Reeds voldaan:</div>
                            </td>
                            <td class="bg-main p-3 text-right">
                              <div class="whitespace-nowrap font-semibold text-white">{{ $invoice->total }}</div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="px-14 py-10 text-sm text-gray-800">
      <p>Hartelijk dank voor uw aankoop en graag tot ziens.</p>
    </div>

    {{-- ═══════ FOOTER ═══════ --}}
    <footer class="fixed bottom-0 left-0 bg-gray-100 w-full text-gray-600 text-center text-xs py-3">
      {{ $invoice->company_name }}
      <span class="text-gray-300 px-2">|</span>
      {{ $invoice->company_email }}
      @if($invoice->company_phone)
        <span class="text-gray-300 px-2">|</span>
        {{ $invoice->company_phone }}
      @endif
    </footer>
  </div>
</div>
