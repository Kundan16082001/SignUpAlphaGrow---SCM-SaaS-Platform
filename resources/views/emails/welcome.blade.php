<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; padding: 40px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .body { padding: 30px; }
        .credentials { background: #f8fafc; border: 2px dashed #6366f1; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .credentials .label { color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .credentials .value { font-size: 18px; font-weight: 700; color: #1e293b; font-family: monospace; margin: 5px 0 15px; }
        .btn { display: inline-block; background: #6366f1; color: white !important; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 15px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to SignupAlphaGrow! 🎉</h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Your account is ready</p>
        </div>
        <div class="body">
            <p style="color: #475569; font-size: 16px;">Hello {{ $user->name }},</p>
            <p style="color: #475569; line-height: 1.6;">
                Welcome aboard! Your account has been created on the SignupAlphaGrow platform. 
                Use the credentials below to log in:
            </p>
            
            <div class="credentials">
                <div class="label">Email</div>
                <div class="value">{{ $user->email }}</div>
                <div class="label">Temporary Password</div>
                <div class="value">{{ $tempPassword }}</div>
                <small style="color: #ef4444;">⚠️ Please change this after first login</small>
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/login') }}" class="btn">Login to Dashboard</a>
            </div>
        </div>
        <div class="footer">
            <p><strong>SignupAlphaGrow</strong> · Need help? Contact support@signupalphagrow.com</p>
        </div>
    </div>
</body>
</html>