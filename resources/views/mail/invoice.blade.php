<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style type="text/css">
        /* Reset Styles */
        body, table, td, th {
            margin: 0;
            padding: 0;
            border: 0;
            outline: 0;
            font-size: 100%;
            vertical-align: top;
            background: transparent;
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
        }
        .ReadMsgBody {
            width: 100%;
            background-color: #ffffff;
        }
        .ExternalClass {
            width: 100%;
            background-color: #ffffff;
        }
        body {
            width: 100% !important;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333333;
        }

        /* Responsive Container */
        @media screen and (max-width: 600px) {
            .devicewidth { width: 100% !important; }
            .responsive-padding { padding-left: 15px !important; padding-right: 15px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color: #f4f4f4;">
    <!-- Main Background Table -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="backgroundTable main-temp">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                <!-- Inner Container -->
                <table width="600" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidth" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="padding: 0;">

                            <!-- Header Section -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 20px 0;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="text-align: center;">
                                            <tr>
                                                <td>
                                                    <img
                                                        src="https://imgur.com/ahNrz0Q.png"
                                                        alt="TFW Rugby League Logo"
                                                        style="height: 60px; display: inline-block;"
                                                    />
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 20px 0;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-bottom: 1px solid #eeeeee;">
                                            <tr>
                                                <td style="font-size: 24px; font-weight: 700; color: #00A878; text-align: center;">Invoice</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; color: #555555; text-align: center; padding-top: 10px;">{{ $order->orderNumber }} | {{ $order->created_at->toFormattedDateString() }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Bill From & Bill To -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding: 20px 0;">
                                <tr>
                                    <td style="padding: 0 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size: 14px; color: #555555;">
                                                    <strong style="color: #00A878; display: block; margin-bottom: 4px;">Bill From</strong>
                                                    TFW Rugby League<br>
                                                    Email: <a href="mailto:{{ env('ADMIN_EMAIL_ADDRESS', 'admin@tfw9s.com.au') }}">{{ env('ADMIN_EMAIL_ADDRESS', 'admin@tfw9s.com.au') }}</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; color: #555555; padding-top: 20px;">
                                                    <strong style="color: #00A878; display: block; margin-bottom: 4px;">Bill To</strong>
                                                    {{ $order->customerFullName }}<br>
                                                    {{ $order->address }}<br>
                                                    Email: <a href="mailto:{{ $order->email }}">{{ $order->email }}</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Invoice Info -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding: 20px 0;">
                                <tr>
                                    <td style="font-size: 14px; color: #555555;">
                                        <strong style="color: #00A878; display: block; margin-bottom: 4px;">Invoice Info</strong>
                                        Invoice Number: {{ $order->orderNumber }}<br>
                                        Date: {{ $order->created_at->toFormattedDateString() }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Product Items -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding: 20px 0;">
                                <tr>
                                    <td>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px solid #eeeeee;">
                                            <thead>
                                                <tr>
                                                    <th style="background-color: #00A878; color: white; padding: 12px 8px; text-align: left; border-bottom: 1px solid #eeeeee;">Product</th>
                                                    <th style="background-color: #00A878; color: white; padding: 12px 8px; text-align: left; border-bottom: 1px solid #eeeeee;">Description</th>
                                                    <th style="background-color: #00A878; color: white; padding: 12px 8px; text-align: right; border-bottom: 1px solid #eeeeee;">Qty</th>
                                                    <th style="background-color: #00A878; color: white; padding: 12px 8px; text-align: right; border-bottom: 1px solid #eeeeee;">Unit Price</th>
                                                    <th style="background-color: #00A878; color: white; padding: 12px 8px; text-align: right; border-bottom: 1px solid #eeeeee;">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($order->items as $lineItem)
                                                <tr>
                                                    <td style="padding: 12px 8px; border-bottom: 1px solid #eeeeee;">{{ $lineItem->item->name }}</td>
                                                    <td style="padding: 12px 8px; border-bottom: 1px solid #eeeeee;">{{ $lineItem->item->snippet }}</td>
                                                    <td style="padding: 12px 8px; text-align: right; border-bottom: 1px solid #eeeeee;">{{ $lineItem->quantity }}</td>
                                                    <td style="padding: 12px 8px; text-align: right; border-bottom: 1px solid #eeeeee;">${{ number_format($lineItem->value, 2) }}</td>
                                                    <td style="padding: 12px 8px; text-align: right; border-bottom: 1px solid #eeeeee;">${{ number_format($lineItem->total, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Totals Section -->
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
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding: 20px 0;">
                                <tr>
                                    <td style="font-size: 14px; color: #555555; padding: 12px 8px; border-bottom: 1px solid #eeeeee;">Subtotal:</td>
                                    <td style="font-size: 14px; color: #555555; text-align: right; padding: 12px 8px; border-bottom: 1px solid #eeeeee;">${{ number_format($order->subTotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 14px; color: #555555; padding: 12px 8px; border-bottom: 1px solid #eeeeee;">Tax Amount:</td>
                                    <td style="font-size: 14px; color: #555555; text-align: right; padding: 12px 8px; border-bottom: 1px solid #eeeeee;">${{ number_format($taxAmount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 14px; color: #555555; padding: 12px 8px; border-bottom: 1px solid #eeeeee;">GST:</td>
                                    <td style="font-size: 14px; color: #555555; text-align: right; padding: 12px 8px; border-bottom: 1px solid #eeeeee;">{{$taxToggle->toggleControl2 ? 'GST Inclusive' : 'GST Exclusive'}}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 14px; color: #555555; padding: 12px 8px; border-bottom: 1px solid #eeeeee;">Discount:</td>
                                    <td style="font-size: 14px; color: #555555; text-align: right; padding: 12px 8px; border-bottom: 1px solid #eeeeee;">{{ number_format($discountRate) }}%</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 18px; font-weight: 700; color: #333333; padding: 12px 8px;">Order Total:</td>
                                    <td style="font-size: 18px; font-weight: 700; color: #00A878; text-align: right; padding: 12px 8px;">${{ number_format(($order->total/100), 2) }}</td>
                                </tr>
                            </table>

                            <!-- Remarks Section -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding: 20px 0;">
                                <tr>
                                    <td>
                                        <strong style="font-size: 16px; color: #333333; display: block; margin-bottom: 10px;">Remarks</strong>
                                        <div style="font-size: 14px; color: #555555; line-height: 1.6; padding: 14px 18px; background-color: #f7f7f7; border-left: 4px solid #00A878; border-radius: 6px;">
                                            {{ $order->remarks }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding: 20px 0; border-top: 1px solid #eeeeee;">
                                <tr>
                                    <td style="text-align: center; font-size: 13px; color: #888888;">
                                        &copy; @php 2024-date("Y") @endphp TFW Rugby League. All rights reserved. | <a href="https://www.tfw9s.com.au" style="color: #00A878; text-decoration: none;">www.tfw9s.com.au</a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>