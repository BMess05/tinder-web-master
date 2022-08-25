<!DOCTYPE html>
<html>
<head>
    <title>Tündür - Welcome Onboard</title>
</head>
<body>
    <div style="text-align:center;">
        <h1>{{ $details['company_name'] ?? 'Your company' }} is registered on Tündür.</h1>
        <p style="font-weight: bold;">Thank you!! Your email "{{ $details['email'] }}" is just registered with Tündür. Please set your password to use your account by clicking below button.</p>
        <p>
        
        </p>
        <br><br>
        <a style="border: 1px solid #E22C5E;
        background: #E22C5E;
        text-decoration: none;
        color: #fff;
        padding: 15px;
        margin: 15px auto;" href="{{ route('setPassword', urlencode($details['email'])) }}">Set Password</a>
        <br><br>
        <p>Thank you</p>
        <p>Tündür</p>
    </div>
</body>
</html> 