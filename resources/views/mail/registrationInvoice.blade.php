<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        #outlook a {
            padding:0;
        }
        body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0; font-family: Helvetica, arial, sans-serif;}
        .ExternalClass {width:100%;}
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
        .backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}
        .main-temp table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; font-family: Helvetica, arial, sans-serif;}
        .main-temp table td {border-collapse: collapse;}
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="backgroundTable main-temp" style="background-color: #d5d5d5;">
        <tbody>
            <tr>
                <td>
                    <table width="600" align="center" cellpadding="15" cellspacing="0" border="0" class="devicewidth" style="background-color: #ffffff;">
                        <tbody>
                            <!-- Start header Section -->
                            <tr>
                                <td style="padding-top: 30px;">
                                    <table width="50" align="center" cellpadding="15" cellspacing="0" border="0" class="devicewidth" style="background-color:#ffffff">
                                        <tbody>
                                            <tr>
                                                <td align="center">
                                                    <a href="http://128.199.231.34/">
                                                        <img
                                                            style="padding: 5rem; width: 300px; height: auto;"
                                                            src="https://imgur.com/ahNrz0Q.png"
                                                            alt="TFW Rugby League Logo"
                                                        />
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner" style="border-bottom: 1px solid #eeeeee; text-align: center;">
                                        <tbody>
                                            <tr align="left" width="100%">
                                                <td width="30%">
                                                    <h3 style="font-size:2rem; line-height: 18px; color: #666666;">
                                                        Invoice
                                                    </h3>
                                                </td>
                                                <td width="30%" style="font-size: 14px; line-height: 18px; color: #666666; padding-bottom: 25px;">
                                                    <div><span style="font-weight: bold; display: block;">Invoice Number:</span>{{ $order->orderNumber }}</div>
                                                    <div><span style="font-weight: bold; display: block;">Registration Date:</span> {{ $order->created_at->toFormattedDateString() }}</div>
                                                </td>
                                                <td width="40%" style="font-size: 14px; line-height: 18px; color: #666666; padding-bottom: 25px;">
                                                    <div>
                                                        <b>TFW Rugby League</b>
                                                    </div>
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                    <div style="font-size: 14px; line-height: 18px; color: #666666;">
                                                        <div>
                                                            <span>
                                                                <b>Email:</b>
                                                            </span>
                                                            <span>
                                                                {{ env('ADMIN_EMAIL_ADDRESS', 'admin@tfw9s.com.au') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @if ($order->item->type === 'weekly')
                                                        @foreach($order->players as $player)
                                                            <div><span style="font-weight: bold; display: block;">Player Name:</span>
                                                            {{ ($player->player_firstname ?? 'unknown') . ' ' . ($player->player_lastname ?? 'unknown') }}
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        @foreach($order->teams as $team)
                                                            <div><span style="font-weight: bold; display: block;">Team Name:</span>{{ $team->name ?? 'unknown' }}</div>
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td>
                                                    <table>
                                                        <tbody>
                                                            <tr align="right">
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <!-- End header Section -->

                            <!-- Start product Section -->

                            <tr>
                                <td style="padding-top: 0;">
                                    <table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner" style="border-bottom: 1px solid #eeeeee;">
                                        <tbody>
                                            <tr>
                                                <td rowspan="5" style="padding-right: 10px; padding-bottom: 10px;">
                                                    <img style="height: 80px;" src="{{ $order->item->thumbnail }}" alt="Product Image" />
                                                </td>
                                                <td colspan="2" style="font-size: 14px; font-weight: bold; color: #666666; padding-bottom: 11px;">
                                                @php
                                                    if ($order->item->type === 'weekly') {
                                                        $agegroups = $order->players->map(function($player) {
                                                            return $player->agegroup->name ?? 'unknown';
                                                        })->unique()->implode(', ');
                                                    } else {
                                                        $agegroups = $order->teams->map(function($team) {
                                                            return $team->agegroup->name ?? 'unknown';
                                                        })->unique()->implode(', ');
                                                    }
                                                @endphp

                                                {{ $order->item->name }} - {{ $agegroups }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; line-height: 18px; color: #757575; width: 440px;">
                                                    Description: {{ $order->item->snippet }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <!-- End product Section -->

                            <!-- Start calculation Section -->
                            <tr>
                                <td style="padding-top: 0;">
                                    <table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner" style="border-bottom: 1px solid #bbbbbb; margin-top: -5px;">
                                        <tbody>
                                            <tr>
                                                <td rowspan="5" style="width: 55%;"></td>
                                                <td style="font-size: 14px; line-height: 18px; color: #666666;">
                                                    Sub-Total:
                                                </td>
                                                <td style="font-size: 14px; line-height: 18px; color: #666666; width: 130px; text-align: right;">
                                                    ${{ number_format($order->item->price / 100, 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; line-height: 18px; color: #666666;">
                                                    GST:
                                                </td>
                                                <td style="font-size: 14px; line-height: 18px; color: #666666; width: 130px; text-align: right;">
                                                    {{$taxToggle->toggleControl2 ? 'GST Inclusive' : 'GST Exclusive'}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; line-height: 18px; color: #666666;">
                                                    Tax Amount:
                                                </td>
                                                <td style="font-size: 14px; line-height: 18px; color: #666666; width: 130px; text-align: right;">
                                                    <?php
                                                        $calculatedTaxAmount = $order->price - $order->item->price;
                                                        $taxRate = $taxValue / 100;
                                                        $calculatedTaxAmount = $order->item->price * $taxRate;
                                                        $taxAmount = $taxToggle->toggleControl1 ? $calculatedTaxAmount : ($taxToggle->toggleControl2 ? $calculatedTaxAmount : 0.00);
                                                    ?>
                                                    ${{ number_format($taxAmount / 100, 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; line-height: 18px; color: #666666; padding-bottom: 10px; border-bottom: 1px solid #eeeeee;">
                                                    Discount:
                                                </td>
                                                <td style="font-size: 14px; line-height: 18px; color: #666666; padding-bottom: 10px; border-bottom: 1px solid #eeeeee; width: 130px; text-align: right;">
                                                    <?php
                                                        $itemtotal = $order->item->price/100;
                                                        $taxRate = $taxValue / 100;
                                                        $discountedTotal = $order->price / 100;

                                                        $taxAmount = $taxToggle->toggleControl2 ? 0 : ($itemtotal * $taxRate);

                                                        $originalTotal = $taxToggle->toggleControl2 
                                                            ? ($itemtotal)
                                                            : ($itemtotal + $taxAmount);
    
                                                        $discountAmount = $originalTotal - $discountedTotal;
    
                                                        $discountRate = $originalTotal != 0 ? ($discountAmount / $originalTotal) * 100 : 0;
                                                    ?>
                                                    {{ number_format($discountRate) }}%
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; font-weight: bold; line-height: 18px; color: #666666; padding-top: 10px; padding-bottom: 10px;">
                                                    Total payment
                                                </td>
                                                <td style="font-size: 14px; font-weight: bold; line-height: 18px; color: #666666; padding-top: 10px; text-align: right; padding-bottom: 10px;">
                                                    ${{ number_format($discountedTotal, 2) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <!-- End calculation Section -->

                            <!-- Start payment method Section -->
                            <tr>
                                <td style="padding: 0 10px;">
                                    <table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                        <tbody>
                                            <tr>
                                                <td colspan="2" style="font-size: 16px; font-weight: bold; color: #666666; padding-bottom: 0.7rem;">
                                                    Payment Method ({{ ucfirst($order->payment_gateway->value) }})
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 100%; text-align:center; padding-bottom: 0.7rem; padding-top: 0.7rem;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="width: 100%; text-align: center; font-style: italic; font-size: 14px; font-weight: 600; color: #666666; padding: 15px 0; border-top: 1px solid #eeeeee;">
                                                    Thank you for signing up with TFW Rugby League! Your registration has been successfully completed and confirmed. Attached is your invoice for ${{ number_format($discountedTotal, 2) }}. We're grateful for your participation.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <!-- End payment method Section -->
                            <tr>
                                <td>
                                    <table width="560" align="center" cellpadding="15" cellspacing="15" class="devicewidthinner">
                                        <tbody>
                                            <tr>
                                                <td colspan="2" style="width: 100%; text-align: center;">
                                                    <p>&copy; 2024-<?=date("Y");?> TFW Rugby League</p>
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
</body>
</html>
