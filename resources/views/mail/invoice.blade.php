<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Invoice</title>
  <style>
    /* Reset & Base Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f7fa;
      color: #333;
      line-height: 1.6;
      padding: 20px;
    }

    /* Main Container */
    .invoice-container {
      max-width: 900px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    /* Header */
    .invoice-header {
      background: #00a878;
      color: white;
      padding: 30px;
      text-align: center;
      position: relative;
    }

    .invoice-header h1 {
      font-size: 2rem;
      font-weight: 700;
    }

    .invoice-header p {
      font-size: 1rem;
      opacity: 0.9;
      margin-top: 4px;
    }

    .invoice-logo {
      position: absolute;
      top: 20px;
      left: 30px;
      height: 50px;
    }

    /* Info Grid */
    .invoice-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      padding: 30px;
      background: #f9f9f9;
      border-bottom: 1px solid #eee;
    }

    .info-section h3 {
      font-size: 1.1rem;
      color: #00a878;
      margin-bottom: 10px;
      border-bottom: 2px solid #00a878;
      display: inline-block;
      padding-bottom: 4px;
    }

    .info-section p {
      font-size: 14px;
      color: #555;
      margin: 4px 0;
    }

    /* Items Table */
    .invoice-items {
      padding: 0 30px;
    }

    .items-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
      font-size: 14px;
    }

    .items-table th {
      background: #00a878;
      color: white;
      text-align: left;
      padding: 12px 8px;
      border-radius: 6px 6px 0 0;
    }

    .items-table th:first-child {
      border-radius: 6px 0 0 0;
    }

    .items-table th:last-child {
      text-align: right;
      border-radius: 0 6px 0 0;
    }

    .items-table td {
      padding: 12px 8px;
      border-bottom: 1px solid #eee;
    }

    .items-table tr:last-child td {
      border-bottom: none;
    }

    .items-table tr:hover {
      background-color: #f8fdfb;
    }

    .items-table .text-right {
      text-align: right;
    }

    .items-table .product-name {
      font-weight: 600;
      color: #333;
    }

    .items-table .description {
      font-size: 13px;
      color: #777;
    }

    /* Totals */
    .invoice-totals {
      padding: 0 30px 30px;
      display: flex;
      justify-content: flex-end;
    }

    .totals-card {
      width: 100%;
      max-width: 350px;
      background: #f9f9f9;
      border-radius: 8px;
      padding: 16px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .totals-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 14px;
      color: #555;
    }

    .totals-row.total {
      font-size: 1.2rem;
      font-weight: 700;
      color: #333;
      margin-top: 10px;
      padding-top: 12px;
      border-top: 2px solid #00a878;
    }

    .totals-row.total span {
      color: #00a878;
    }

    /* Remarks */
    .invoice-remarks {
      padding: 0 30px;
      margin: 20px 0;
    }

    .invoice-remarks h3 {
      font-size: 1.1rem;
      color: #00a878;
      margin-bottom: 10px;
    }

    .invoice-remarks p {
      font-size: 14px;
      color: #555;
      line-height: 1.6;
      padding: 14px 18px;
      background: #f7f7f7;
      border-left: 4px solid #00a878;
      border-radius: 6px;
    }

    /* Footer */
    .invoice-footer {
      text-align: center;
      padding: 20px;
      background: #f0f0f0;
      color: #777;
      font-size: 13px;
      border-top: 1px solid #ddd;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .invoice-header {
        padding: 20px;
      }

      .invoice-header h1 {
        font-size: 1.6rem;
      }

      .invoice-logo {
        height: 40px;
        left: 20px;
        top: 15px;
      }

      .invoice-info,
      .invoice-totals {
        padding: 20px;
      }

      .items-table th,
      .items-table td {
        padding: 10px 6px;
      }

      .totals-card {
        max-width: 100%;
      }
    }

    @media (max-width: 480px) {
      body {
        padding: 10px;
      }

      .invoice-container {
        border-radius: 8px;
      }

      .invoice-header {
        padding: 20px 15px;
      }

      .invoice-info,
      .invoice-items,
      .invoice-totals,
      .invoice-remarks {
        padding: 15px;
      }

      .items-table th,
      .items-table td {
        font-size: 13px;
        padding: 8px 4px;
      }

      .invoice-remarks p {
        font-size: 13px;
        padding: 10px 12px;
      }
    }

    /* Print Styles */
    @media print {
      body {
        background: white;
        padding: 0;
      }

      .invoice-container {
        box-shadow: none;
        border-radius: 0;
      }

      .invoice-footer {
        page-break-after: avoid;
      }
    }
  </style>
</head>
<body>

  <div class="invoice-container">

    <!-- Header -->
    <div class="invoice-header">
      <img src="https://imgur.com/ahNrz0Q.png" alt="Company Logo" class="invoice-logo">
      <h1>Invoice</h1>
      <p>{{ $order->orderNumber }} | {{ $order->created_at->toFormattedDateString() }}</p>
    </div>

    <!-- Info Section -->
    <div class="invoice-info">
      <div class="info-section">
        <h3>Bill From</h3>
        <p><strong>TFW Rugby League</strong></p>
        <p>Email: {{ env('ADMIN_EMAIL_ADDRESS', 'admin@tfw9s.com.au') }}</p>
      </div>
      @if(!is_null($order->address))
      <div class="info-section">
        <h3>Bill To</h3>
        <p><strong>{{ $order->customerFullName }}</strong></p>
        <p>Address: {{ $order->address }}</p>
        <p>Email: {{ $order->email }}</p>
      </div>
      @else
      <div class="info-section">
        <h3>Bill To</h3>
        <p><strong>{{ $order->customerFullName }}</strong></p>
        <p>Email: {{ $order->email }}</p>
      </div>
      @endif
      <div class="info-section">
        <h3>Invoice Info</h3>
        <p><strong>Invoice Number:</strong> {{ $order->orderNumber }}</p>
        <p><strong>Date:</strong> {{ $order->created_at->toFormattedDateString() }}</p>
      </div>
    </div>

    <!-- Items -->
    <div class="invoice-items">
      <table class="items-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Description</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Total</th>
          </tr>
        </thead>
        <tbody>
        @foreach($order->items as $lineItem)
          <tr>
            <td class="product-name">{{ $lineItem->item->name }}</td>
            <td class="description">{{ $lineItem->item->snippet }}</td>
            <td class="text-right">{{ $lineItem->quantity }}</td>
            <td class="text-right">${{ number_format($lineItem->value, 2) }}</td>
            <td class="text-right">${{ number_format($lineItem->total, 2) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    <!-- Totals -->
    @php
        $itemTotal = $order->subTotal;
        $discountedTotal = $order->total / 100;
        $taxRate = $taxValue / 100;

        $taxAmount = $taxToggle->toggleControl2 ? 0 : ($itemTotal * $taxRate);

        $originalTotal = $taxToggle->toggleControl2 
            ? ($itemTotal)
            : ($itemTotal + $taxAmount);

        $discountAmount = $originalTotal - $discountedTotal;

        $discountRate = $originalTotal != 0 ? ($discountAmount / $originalTotal) * 100 : 0;

        $taxRate = $taxValue / 100;
        $calculatedTaxAmount = $order->subTotal * $taxRate;
        $taxAmount = $taxToggle->toggleControl1 ? $calculatedTaxAmount : ($taxToggle->toggleControl2 ? $calculatedTaxAmount : 0.00);
    @endphp
    <div class="invoice-totals">
      <div class="totals-card">
        <div class="totals-row"><span>Subtotal:</span>&nbsp;<span>${{ number_format($order->subTotal, 2) }}</span></div>
        <div class="totals-row"><span>Tax Amount:</span>&nbsp;<span>${{ number_format($taxAmount, 2) }}</span></div>
        <div class="totals-row"><span>GST:</span>&nbsp;<span>{{$taxToggle->toggleControl2 ? 'GST Inclusive' : 'GST Exclusive'}}</span></div>
        <div class="totals-row"><span>Discount:</span>&nbsp;<span>{{ number_format($discountRate) }}%</span></div>
        <div class="totals-row total"><span>Order Total:</span>&nbsp;<span>${{ number_format(($order->total/100), 2) }}</span></div>
      </div>
    </div>

    <!-- Remarks -->
    <div class="invoice-remarks">
      <h3>Remarks</h3>
      <p>{{ $order->remarks }}</p>
    </div>

    <!-- Footer -->
    <div class="invoice-footer">
      &copy; @php 2024-date("Y"); @endphp TFW Rugby League. All rights reserved. | www.tfw9s.com.au
    </div>

  </div>

</body>
</html>