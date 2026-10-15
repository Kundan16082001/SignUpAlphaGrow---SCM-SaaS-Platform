<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | AlphaGrow</title>
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
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
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
    <div class="error-card fade-in-up">
        <div class="error-code">404</div>
        <h2 class="fw-bold text-dark mb-3">Page Not Found</h2>
        <p class="text-muted mb-4">
            Oops! The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ url('/dashboard') }}" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-house-door me-2"></i>Go to Dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-outline-secondary px-4 py-2 fw-semibold">
                <i class="bi bi-arrow-left me-2"></i>Go Back
            </a>
        </div>
    </div>
</body>
</html>