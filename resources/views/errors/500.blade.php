<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | AlphaGrow</title>
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
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
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
        <div class="error-code">500</div>
        <h2 class="fw-bold text-dark mb-3">Internal Server Error</h2>
        <p class="text-muted mb-4">
            Something went wrong on our end. Our engineering team has been automatically notified and is working on a fix.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ url('/dashboard') }}" class="btn btn-primary px-4 py-2 fw-semibold">
                <i class="bi bi-house-door me-2"></i>Go to Dashboard
            </a>
            <button onclick="window.location.reload()" class="btn btn-outline-secondary px-4 py-2 fw-semibold">
                <i class="bi bi-arrow-clockwise me-2"></i>Try Again
            </button>
        </div>
    </div>
</body>
</html>