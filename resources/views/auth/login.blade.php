<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SignupAlphaGrow</title>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- OFFLINE BOOTSTRAP -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
        }

        /* Left Side Branding */
        .login-brand-side {
            background: linear-gradient(135deg, #064e3b 0%, #059669 50%, #10b981 100%);
            position: relative;
            overflow: hidden;
        }

        .login-brand-side::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: pulse 15s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .brand-content {
            position: relative;
            z-index: 2;
        }

        /* Right Side Form */
        .login-form-side {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.85rem 1rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #111827;
            background: #f9fafb;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control:focus {
            border-color: #10b981;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        .btn-login {
            background: #10b981;
            color: #ffffff;
            border: none;
            padding: 0.85rem 1.5rem;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
        }

        .btn-login:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
            color: #ffffff;
        }

        .alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .link-emerald {
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .link-emerald:hover {
            color: #059669;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container-fluid vh-100 p-0">
        <div class="row h-100 g-0">
            
            <!-- Left Side: Branding (Hidden on mobile) -->
            <div class="col-md-6 d-none d-md-flex login-brand-side align-items-center justify-content-center">
                <div class="text-center text-white p-5 brand-content">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mb-4 shadow-lg rounded-circle p-3 bg-white" style="width: 100px; height: 100px; object-fit: contain;">
                    <h2 class="fw-bold mb-3">SignupAlphaGrow</h2>
                    <p class="lead opacity-75" style="font-size: 1.1rem; max-width: 400px; margin: 0 auto;">
                        The complete SCM SaaS platform for Manufacturers, Warehouses, and Distributors.
                    </p>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="col-md-6 login-form-side">
                <div class="login-card">
                    
                    <!-- Logo on Mobile -->
                    <div class="text-center mb-4 d-md-none">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width: 70px; height: 70px; object-fit: contain;" class="rounded-circle p-2 border shadow-sm bg-white">
                        <h4 class="fw-bold mt-3 mb-1">SignupAlphaGrow</h4>
                    </div>
                    
                    <div class="mb-4">
                        <h4 class="fw-bold mb-1" style="color: #111827;">Welcome Back!</h4>
                        <p class="text-muted mb-0">Please enter your details to sign in.</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <ul class="mb-0 ps-1 d-inline">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="name@company.com" required autofocus autocomplete="email">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label text-muted" for="remember" style="font-size: 0.9rem;">Remember me</label>
                            </div>
                            <a href="#" class="link-emerald" style="font-size: 0.9rem;">Forgot Password?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-login">
                            Sign In <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </form>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">
                            Don't have an account? 
                            <a href="{{ route('signup.form') }}" class="link-emerald">Create an account</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- OFFLINE BOOTSTRAP JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>