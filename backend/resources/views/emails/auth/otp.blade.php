<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your OTP Code</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .wrapper {
            max-width: 560px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .header {
            background-color: #1a3c5e;
            padding: 28px 32px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 20px;
            letter-spacing: 0.5px;
        }

        .body {
            padding: 32px;
            color: #333333;
        }

        .body p {
            margin: 0 0 16px;
            font-size: 15px;
            line-height: 1.6;
        }

        .otp-box {
            margin: 24px 0;
            text-align: center;
        }

        .otp-code {
            display: inline-block;
            letter-spacing: 12px;
            font-size: 40px;
            font-weight: 700;
            color: #1a3c5e;
            background: #f0f4f8;
            padding: 16px 28px;
            border-radius: 8px;
            border: 2px dashed #c5d5e8;
        }

        .note {
            font-size: 13px;
            color: #888888;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #eeeeee;
        }

        .footer {
            background: #f4f6f9;
            padding: 16px 32px;
            text-align: center;
            font-size: 12px;
            color: #aaaaaa;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="header">
            <h1>KK Wholesalers</h1>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $userName }}</strong>,</p>
            <p>Use the code below to complete your login. It expires in <strong>{{ $expiry }} minutes</strong>.
            </p>

            <div class="otp-box">
                <span class="otp-code">{{ $otp }}</span>
            </div>

            <p>If you did not attempt to log in, please contact your system administrator immediately.</p>

            <p class="note">
                Do not share this code with anyone. KK Wholesalers staff will never ask for your OTP.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} KK Wholesalers &mdash; Retail pay System
        </div>
    </div>
</body>

</html>
