<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Presensi Magang UP2D STI - Login</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="row g-0">
                <!-- Header Section -->
                <div class="col-12">
                    <div class="login-header">
                        <div class="logo-section">
                            <div class="logo-box">
                                <img src="{{ asset('image/Danantara.png') }}" alt="Danantara Logo" class="danantara-logo">
                            </div>
                            <div class="logo-box">
                                <img src="{{ asset('image/PLN.png') }}" alt="PLN Logo" class="pln-logo">
                            </div>
                        </div>
                        <div class="presensi-title">
                            <h1 class="mb-2">
                                Day-In
                            </h1>
                            <p class="welcome-text">Daily Activity of Internship</p>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="col-12">
                    <div class="login-form">
                        <div class="text-center mb-4">
                            <p class="text-muted">Gunakan Akun yang diberikan</p>
                        </div>

                        <form id="loginForm" action="{{ route('login.post') }}" method="POST">
                            @csrf
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Username" value="{{ old('name') }}" required>
                                <label for="name">
                                    <i class="fas fa-user me-2"></i>Username
                                </label>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-floating mb-4">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Password" required>
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Ingat saya
                                </label>
                            </div>

                            <button type="submit" class="btn btn-login" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Masuk
                            </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Login JS -->
    <script src="{{ asset('js/login.js') }}"></script>
</body>

</html>