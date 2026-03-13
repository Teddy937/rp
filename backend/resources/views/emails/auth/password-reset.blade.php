<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Your Password</title>
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

        .btn-wrap {
            text-align: center;
            margin: 28px 0;
        }

        .btn {
            display: inline-block;
            background-color: #1a3c5e;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
        }

        .url-fallback {
            font-size: 12px;
            color: #888888;
            word-break: break-all;
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
            <p>We received a request to reset your password. Click the button below to set a new password. This link
                expires in <strong>{{ $expiry }} minutes</strong>.</p>

            <div class="btn-wrap">
                <a href="{{ $resetUrl }}" class="btn">Reset My Password</a>
            </div>

            <p class="url-fallback">
                If the button doesn't work, copy and paste this link into your browser:<br />
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </p>

            <p class="note">
                If you did not request a password reset, you can safely ignore this email. Your password will not
                change.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} KK Wholesalers &mdash; Inventory Management System
        </div>
    </div>
</body>

</html>
