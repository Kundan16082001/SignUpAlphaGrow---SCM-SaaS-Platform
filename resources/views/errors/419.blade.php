<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Page Expired | AlphaGrow</title>
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
            background: linear-gradient(135deg, #64748b 0%, #94a3b8 100%);
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
        <i class="bi bi-clock-history text-secondary" style="font-size: 4rem;"></i>
        <div class="error-code mt-3">419</div>
        <h2 class="fw-bold text-dark mb-3">Page Expired</h2>
        <p class="text-muted mb-4">
            Your session has expired due to inactivity. For your security, please refresh the page and try again.
        </p>
        <button onclick="window.location.reload()" class="btn btn-primary px-4 py-2 fw-semibold">
            <i class="bi bi-arrow-clockwise me-2"></i>Refresh Page
        </button>
    </div>
</body>
</html>