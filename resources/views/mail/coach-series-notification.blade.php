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
                            <!-- Header Section -->
                            <tr>
                                <td style="padding: 20px 20px 0 20px; background-color: #ffffff; text-align: center;">
                                    <!-- Logo Section (simplified) -->
                                    <a href="http://128.199.231.34/" style="display: inline-block;">
                                        <img
                                            style="width: 100px; height: auto; margin-bottom: 20px;"
                                            src="https://imgur.com/ahNrz0Q.png"
                                            alt="TFW Rugby League Logo"
                                        />
                                    </a>
                                </td>
                            </tr>
                            
                            <!-- Content Section -->
                            <tr>
                                <td style="padding: 0 20px 30px 20px; background-color: #ffffff; text-align: center;">
                                    <p style="font-size: 18px; color: #333333; margin: 0 0 10px 0;">Hello Coach, <strong>{{ $coach }}</strong>
                                    </p>
                                    <p style="font-size: 16px; color: #666666; margin: 0 0 25px 0;">
                                        You have been assigned to the series: <strong style="color: #333333;">{{ $seriesName }}</strong>
                                    </p>
                                    @if($code !== '')
                                    <a href="{{ $link }}" 
                                       style="background-color: #5ecb3e; 
                                              color: #ffffff; 
                                              padding: 12px 30px; 
                                              text-decoration: none; 
                                              border-radius: 4px; 
                                              font-weight: bold;
                                              font-size: 16px;
                                              display: inline-block;
                                              margin-bottom: 20px;">
                                        This is your link to register, here is your discount code <p><code>{{ $code }}</code></p>
                                    </a>
                                    @else
                                    <a href="{{ $link }}" 
                                       style="background-color: #5ecb3e; 
                                              color: #ffffff; 
                                              padding: 12px 30px; 
                                              text-decoration: none; 
                                              border-radius: 4px; 
                                              font-weight: bold;
                                              font-size: 16px;
                                              display: inline-block;
                                              margin-bottom: 20px;">
                                        This is your link to register
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            <!-- Footer Section -->
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