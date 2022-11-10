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
        .text--wpi-red {
            color: #3981da;
        }
        .guestMessage {
            text-indent: 4ch;
            line-height: 1.25rem;
            padding: 0 1rem;
        }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="backgroundTable main-temp" style="background-color: #d5d5d5; height: 100vh;">
        <tbody>
            <tr>
                <td>
                    <table width="600" align="center" cellpadding="15" cellspacing="0" border="0" class="devicewidth" style="background-color: #ffffff;">
                        <tbody>
                            <!-- Start header Section -->
                            <tr>
                                <td style="padding-top: 30px;">
                                    <table width="560" align="center" cellpadding="15" cellspacing="0" border="0" class="devicewidth" style="background-color:#ffffff">
                                        <tbody>
                                            <tr>
                                                <td align="center">
                                                    <a href="http://revampedofficial.com/">
                                                        <img
                                                            style="width: 35%; padding: 0.5rem;"
                                                            src="https://i.imgur.com/wuhL9yy.png"
                                                            alt="Revamped Logo"
                                                        />
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner" style="text-align: center;">
                                        <tbody>
                                            <tr align="center" width="100%">
                                                <td>
                                                    <h3 style="font-size:2rem; line-height: 18px; color: #666666;">
                                                        You have a new message
                                                    </h3>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <hr>
                                </td>
                            </tr>
                            <!-- End header Section -->
                            
                            <!-- Start payment method Section -->
                            <tr>
                                <td style="padding: 0 10px;">
                                    <table width="640" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                        <tbody>
                                            <tr>
                                                <td colspan="2" style="width: 100%; text-align: left; font-size: 16px; font-weight: 600; color: #666666; padding: 15px 0;">
                                                    <div style="padding: 1rem 4rem;">
                                                        <p>Hi Admin!</p>
                                                        <div style="margin-bottom: 1rem;">&nbsp;</div>
                                                        <p>
                                                            You have a new message from a guest on revampedofficial.com
                                                        </p>
                                                        <div style="margin-bottom: 1rem;">&nbsp;</div>
                                                        <div>
                                                            <div style="margin-bottom: 1rem;">
                                                                <span class="text--wpi-red">Name: </span>
                                                                <span>
                                                                    {{ $name }}
                                                                </span>
                                                            </div>
                                                            <div style="margin-bottom: 1rem;">
                                                                <span class="text--wpi-red">Email: </span>
                                                                <span>
                                                                    <a href="mailto:{{ $email }}">
                                                                        {{ $email }}
                                                                    </a>
                                                                </span>
                                                            </div>
                                                            <div style="margin-bottom: 1rem;">
                                                                <div class="text--wpi-red" style="margin-bottom: .5rem;">Message: </div>
                                                                <div class="guestMessage">
                                                                    {{ $guestMessage }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <!-- End payment method Section -->
                            <!-- start footer -->
                            <tr>
                                <td>
                                    <hr>
                                    <table width="560" align="center" cellpadding="15" cellspacing="15" class="devicewidthinner">
                                        <tbody>
                                            <tr>
                                                <td colspan="2" style="width: 100%; text-align: center;">
                                                    <p>&copy; 2022 Revamped</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <!-- end footer -->
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>