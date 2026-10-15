<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied | AlphaGrow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #f59e0b 0%, #eab308 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }
        .error-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.08);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
            border: 1px solid rgba(0,0,0,0.04);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <i class="bi bi-shield-lock text-warning" style="font-size: 4rem;"></i>
        <div class="error-code mt-3">403</div>
        <h2 class="fw-bold text-dark mb-3">Access Denied</h2>
        <p class="text-muted mb-4">
            You don't have the necessary permissions to view this page. Please contact your administrator if you believe this is a mistake.
        </p>
        <a href="{{ url('/dashboard') }}" class="btn btn-primary px-4 py-2 fw-semibold">
            <i class="bi bi-house-door me-2"></i>Return to Dashboard
        </a>
    </div>
</body>
</html>