<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Izus Payment</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#000000',
                        'secondary': '#4F46E5',
                        'light-gray': '#F9FAFB',
                        'dark-gray': '#6B7280',
                        'border-gray': '#E5E7EB'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
        }

        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .login-form-section {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-graphic-section {
            flex: 1;
            background: linear-gradient(135deg, #969696ff 0%, #000000ff 100%);
            display: none;
        }

        .form-container {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
        }

        .form-logo {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: #6B7280;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #D1D5DB;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: #F9FAFB;
        }

        .form-input:focus {
            outline: none;
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            background-color: white;
        }

        .btn-login {
            width: 100%;
            background-color: #000000;
            color: white;
            padding: 0.875rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-login:hover {
            background-color: #1F2937;
        }

        .btn-login i {
            margin-left: 0.5rem;
            transition: transform 0.2s;
        }

        .btn-login:hover i {
            transform: translateX(4px);
        }

        .forgot-password {
            text-align: right;
            margin: 1rem 0 2rem;
        }

        .forgot-password a {
            color: #4F46E5;
            font-size: 0.875rem;
            text-decoration: none;
            font-weight: 500;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
            color: #9CA3AF;
            font-size: 0.875rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #E5E7EB;
            margin: 0 1rem;
        }

        .signup-link {
            text-align: center;
            color: #6B7280;
            font-size: 0.95rem;
        }

        .signup-link a {
            color: #4F46E5;
            font-weight: 600;
            text-decoration: none;
            margin-left: 0.25rem;
        }

        @media (min-width: 1024px) {
            .login-graphic-section {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Seção do formulário -->
        <div class="login-form-section">
            <div class="form-container">
                <div class="form-logo">
                    <h1 class="text-2xl font-bold text-gray-900">{{ env('APP_NAME', 'Izus Payment') }}</h1>
                </div>
                
                <h2 class="form-title">Entre na sua conta</h2>
                <p class="form-subtitle">Bem-vindo de volta! Por favor, insira seus dados.</p>
                
                @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        <h3 class="text-red-800 font-semibold text-sm">Erro no login</h3>
                    </div>
                    <ul class="text-red-700 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                        <li class="flex items-center">
                            <i class="fas fa-circle text-red-500 text-xs mr-2"></i>
                            {{ $error }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}" id="login-form">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               class="form-input" 
                               placeholder="Digite seu e-mail"
                               required>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <div class="flex justify-between items-center">
                            <label for="password" class="form-label">Senha</label>
                            @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">
                                Esqueceu a senha?
                            </a>
                            @endif
                        </div>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-input pr-10" 
                                   placeholder="••••••••"
                                   required>
                            <button type="button" 
                                    onclick="togglePassword()" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    aria-label="Mostrar senha">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="form-group flex items-center">
                        <input type="checkbox" 
                               id="remember" 
                               name="remember" 
                               {{ old('remember') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Lembrar de mim
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-login" id="login-btn">
                        Entrar <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
                
                <div class="divider">ou</div>
                
                <p class="signup-link">
                    Não tem uma conta? 
                    <a href="{{ route('association.register.form') }}">Criar agora</a>
                </p>
            </div>
        </div>
        
        <!-- Seção gráfica (visível apenas em telas grandes) -->
        <div class="login-graphic-section">
            <!-- Aqui você pode adicionar uma imagem ou ilustração -->
            <div class="text-white text-center p-8 max-w-md">
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Função para alternar visibilidade da senha
            window.togglePassword = function() {
                const passwordField = document.getElementById('password');
                const passwordIcon = document.getElementById('password-icon');
                
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    passwordIcon.classList.remove('fa-eye');
                    passwordIcon.classList.add('fa-eye-slash');
                } else {
                    passwordField.type = 'password';
                    passwordIcon.classList.remove('fa-eye-slash');
                    passwordIcon.classList.add('fa-eye');
                }
            };

            // Adicionar efeito de loading no botão de login
            const loginForm = document.getElementById('login-form');
            const loginBtn = document.getElementById('login-btn');
            
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    loginBtn.disabled = true;
                    loginBtn.innerHTML = 'Entrando... <i class="fas fa-circle-notch fa-spin ml-2"></i>';
                });
            }
            
            // Validação em tempo real
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(String(email).toLowerCase());
            }
            
            function updateFieldValidation(input, isValid) {
                if (isValid) {
                    input.classList.remove('border-red-500');
                    input.classList.add('border-green-500');
                } else {
                    input.classList.remove('border-green-500');
                    input.classList.add('border-red-500');
                }
            }
            
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    updateFieldValidation(this, validateEmail(this.value));
                });
            }
            
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    updateFieldValidation(this, this.value.length >= 6);
                });
            }
        });
    </script>
</body>
</html>

