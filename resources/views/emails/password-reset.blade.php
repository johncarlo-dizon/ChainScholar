<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-height: 50px; }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4299e1;
            color: white !important;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer { margin-top: 30px; font-size: 12px; color: #718096; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('storage/images/logo.png') }}" alt="ChainScholar" class="logo">
        </div>
        
        <h1>Password Reset Request</h1>
        
        <p>Hello!</p>
        
        <p>You are receiving this email because we received a password reset request for your account.</p>
        
        <a href="{{ $url }}" class="button">Reset Password</a>
        
        <p>This password reset link will expire in {{ $count }} minutes.</p>
        
        <p>If you did not request a password reset, no further action is required.</p>
        
        <div class="footer">
            Thanks,<br>
            ChainScholar<br><br>
            <small>
                If you're having trouble clicking the button, copy and paste this URL into your browser:<br>
                <a href="{{ $url }}">{{ $url }}</a>
            </small>
        </div>
    </div>
</body>
</html>