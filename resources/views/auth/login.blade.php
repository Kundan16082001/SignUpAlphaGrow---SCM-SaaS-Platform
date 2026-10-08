<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SignUpAlphaGrow</title>
    <!-- OFFLINE BOOTSTRAP -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
</head>
<body class="bg-light">
    <div class="container-fluid vh-100">
        <div class="row h-100 g-0">
            <!-- Left Side: Branding (Hidden on mobile) -->
            <div class="col-md-6 d-none d-md-flex login-brand-side align-items-center justify-content-center">
                <div class="text-center text-white p-5 position-relative" style="z-index: 2;">
                    <!-- OFFLINE LOGO -->
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mb-4 shadow-lg rounded-circle p-2 bg-white" style="width: 120px;">
                    <h2 class="fw-bold">SignUpAlphaGrow</h2>
                    <p class="lead mt-3 opacity-75">Complete SCM SaaS Platform for Manufacturers, Warehouses, and Distributors.</p>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="col-md-6 d-flex align-items-center justify-content-center p-4">
                <div class="card shadow-lg border-0 w-100" style="max-width: 450px;">
                    <div class="card-body p-5">
                        <!-- Logo on Mobile -->
                        <div class="text-center mb-4 d-md-none">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width: 80px;" class="rounded-circle p-1 border">
                        </div>
                        
                        <h4 class="fw-bold mb-1">Welcome Back!</h4>
                        <p class="text-muted mb-4">Please login to your account.</p>

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" placeholder="admin@alphagrow.com" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                <a href="#" class="text-decoration-none fw-semibold">Forgot Password?</a>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">Login</button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">Don't have an account? <a href="#" class="fw-semibold text-decoration-none">Sign Up</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- OFFLINE BOOTSTRAP JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>