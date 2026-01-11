<style>
  @page { margin: 1cm; }
  body { font-family: 'Helvetica', sans-serif; font-size: 9pt; color: #1a1a1a; line-height: 1.3; }

  /* Grid */
  .row { width: 100%; clear: both; }
  .col-left { width: 55%; float: left; }
  .col-right { width: 45%; float: left; text-align: right; }

  /* Header */
  .invoice-title { font-size: 24pt; font-weight: bold; color: #ddd; margin-bottom: 10px; }
  .company-info { font-size: 8pt; color: #666; margin-top: 5px; line-height: 1.5; }

  /* Tables */
  .clear {
    clear: both;
    display: block;
    font-size: 0;
    height: 0;
    line-height: 0;
  }

  /* Ensure the table resets the layout flow */
  .items-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    clear: both; /* Added this */
    table-layout: fixed; /* Helps keep columns stable */
  }
  .items-table th {
    text-align: left; padding: 10px; background: #000; color: #fff;
    font-size: 7.5pt; text-transform: uppercase; letter-spacing: 1px;
  }
  .items-table td { padding: 10px; border-bottom: 1px solid #eee; }
  .text-right { text-align: right; }

  /* Summary */
  .summary-box { width: 300px; float: right; }
  .summary-table { width: 100%; border-collapse: collapse; }
  .summary-table td { padding: 5px 0; }
  .grand-total { font-size: 13pt; font-weight: bold; border-top: 2px solid #000; }

  .btw-specification { width: 250px; float: left; font-size: 8pt; color: #444; margin-top: 20px;}
  .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 7pt; color: #aaa; border-top: 1px solid #eee; padding-top: 5px; }
</style>

<div class="row">
  <div class="col-left">
    @if($invoice->company_logo)
      <img height="24" style="width:auto" src="{{ $invoice->company_logo }}">
    @else
      <h2 style="margin:0;">{{ $invoice->company_name }}</h2>
    @endif
    <div class="company-info">
      {{ $invoice->company_address }}<br>
      {{ $invoice->company_postcode }} {{ $invoice->company_city }}, {{ $invoice->company_country }}<br>
      <strong>KVK:</strong> {{ $invoice->company_kvk }} | <strong>BTW:</strong> {{ $invoice->company_vat }}
    </div>
  </div>
  <div class="col-right">
    <div class="invoice-title">FACTUUR</div>
    <table style="width: 100%; font-size: 9pt;">
      <tr><td class="text-right"><strong>Factuurnr:</strong></td><td class="text-right" style="width: 100px;">{{ $invoice->invoice_number }}</td></tr>
      <tr><td class="text-right"><strong>Datum:</strong></td><td class="text-right">{{ $invoice->invoice_date }}</td></tr>
      <tr><td class="text-right"><strong>Bestelnr:</strong></td><td class="text-right">{{ $invoice->order_number }}</td></tr>
    </table>
  </div>
</div>

<div class="row" style="margin-top: 40px;">
  <div class="col-left">
    <div style="font-size: 7pt; font-weight: bold; color: #aaa; margin-bottom: 5px;">FACTUUR ADRES</div>
    <strong>{{ $invoice->billing_name }}</strong><br>
    {{ $invoice->billing_address }}<br>
    {{ $invoice->billing_postcode }} {{ $invoice->billing_city }}
  </div>
  <div class="col-right" style="text-align: left; padding-left: 50px;">
    <div style="font-size: 7pt; font-weight: bold; color: #aaa; margin-bottom: 5px;">VERZEND ADRES</div>
    {{ $invoice->shipping_name }}<br>
    {{ $invoice->shipping_address }}<br>
    {{ $invoice->shipping_postcode }} {{ $invoice->shipping_city }}
  </div>
</div>

<table class="items-table">
  <thead>
  <tr>
    <th style="width: 50%;">Product</th>
    <th class="text-right">Aantal</th>
    <th class="text-right">Prijs</th>
    <th class="text-right">BTW</th>
    <th class="text-right">Totaal</th>
  </tr>
  </thead>
  <tbody>
  @foreach($invoice->items as $item)
    <tr>
      <td>
        <strong>{{ $item->product->title }}</strong><br>
        <span style="font-size: 7pt; color: #888;">{{ $item->sku }}</span>
      </td>
      <td class="text-right">{{ $item->quantity }}</td>
      <td class="text-right">{{ $item->unit_price }}</td>
      <td class="text-right">{{ $item->tax_rate }}</td>
      <td class="text-right">{{ $item->subtotal }}</td>
    </tr>
  @endforeach
  </tbody>
</table>

<div class="row">
  <div class="btw-specification">
    <strong>BTW SPECIFICATIE</strong>
    <table style="width: 100%; margin-top: 5px;">
      @foreach($invoice->tax_lines as $tax)
        <tr>
          <td>{{ $tax['label'] }}</td>
          <td class="text-right">{{ $tax['amount'] }}</td>
        </tr>
      @endforeach
    </table>
    <p style="margin-top: 15px;">Betaald via: {{ $invoice->payment_method }}</p>
  </div>

  <div class="summary-box">
    <table class="summary-table">
      <tr>
        <td>Subtotaal</td>
        <td class="text-right">{{ $invoice->subtotal }}</td>
      </tr>
      @if(!$invoice->discount_total->is_free)
        <tr>
          <td style="color: #c00;">Korting</td>
          <td class="text-right" style="color: #c00;">-{{ $invoice->discount_total }}</td>
        </tr>
      @endif
      <tr>
        <td>Verzendkosten</td>
        <td class="text-right">{{ $invoice->shipping_total }}</td>
      </tr>
      <tr class="grand-total">
        <td>TOTAAL</td>
        <td class="text-right">{{ $invoice->total }}</td>
      </tr>
      <tr>
        <td colspan="2" class="text-right" style="font-size: 8pt; color: #666; padding-top: 5px;">
          Inclusief {{ $invoice->tax_total }} BTW
        </td>
      </tr>
    </table>
  </div>
</div>

<div class="footer">
  {{ $invoice->company_name }} &bull; {{ $invoice->company_email }} &bull; KVK: {{ $invoice->company_kvk }} &bull; BTW: {{ $invoice->company_vat }}
</div>
