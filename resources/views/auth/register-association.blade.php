<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cadastro de Empresa - Izus Payment</title>
    
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

        .register-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .register-form-section {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow-y: auto;
        }

        .register-graphic-section {
            flex: 1;
            background: linear-gradient(135deg, #969696ff 0%, #000000ff 100%);
            display: none;
        }

        .form-container {
            max-width: 500px;
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

        .btn-register {
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
            margin-top: 1rem;
        }

        .btn-register:hover {
            background-color: #1F2937;
        }

        .btn-register i {
            margin-left: 0.5rem;
            transition: transform 0.2s;
        }

        .btn-register:hover i {
            transform: translateX(4px);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #6B7280;
            font-size: 0.875rem;
        }

        .login-link a {
            color: #4F46E5;
            text-decoration: none;
            font-weight: 500;
            margin-left: 0.25rem;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .form-group.md\\:col-span-2 {
                grid-column: span 2 / span 2;
            }
        }

        @media (min-width: 1024px) {
            .register-container {
                padding-right: 50vw;
            }
            .register-graphic-section {
                display: flex;
                align-items: center;
                justify-content: center;
                position: fixed;
                right: 0;
                top: 0;
                bottom: 0;
                width: 50vw;
            }
            .graphic-img {
                height: auto;
                object-fit: contain;
                filter: drop-shadow(0 10px 20px rgba(0,0,0,0.35));
            }
            .register-form-section {
                flex: 0 0 50vw;
                min-height: 100vh;
                overflow-y: auto;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Seção do formulário -->
        <div class="register-form-section">
            <div class="form-container">
                <div class="form-logo">
                    <h1 class="text-2xl font-bold text-gray-900">{{ env('APP_NAME', 'Izus Payment') }}</h1>
                </div>
                
                <h2 class="form-title">Criar conta</h2>
                <p class="form-subtitle">Preencha os dados abaixo para criar sua conta</p>
                
                @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        <h3 class="text-red-800 font-semibold text-sm">Erro no cadastro</h3>
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

                <form method="POST" action="{{ route('association.register') }}" id="register-form">
                    @csrf
                    
                    <h3 class="text-md font-semibold text-gray-900 mb-4">Dados Pessoais</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="tipo" class="form-label">Tipo de Cadastro</label>
                            <select id="tipo" name="tipo" class="form-input" required>
                                <option value="">Selecione</option>
                                <option value="pf" {{ old('tipo') === 'pf' ? 'selected' : '' }}>Pessoa Física (CPF)</option>
                                <option value="cnpj" {{ old('tipo') === 'cnpj' ? 'selected' : '' }}>Pessoa Jurídica (CNPJ)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" 
                                   id="name" 
                                   name="nome" 
                                   value="{{ old('name') }}" 
                                   class="form-input" 
                                   required 
                                   autofocus>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" 
                                   id="email" 
                                    name="email" 
                                   value="{{ old('email') }}" 
                                   class="form-input" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Telefone</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="telefone" 
                                   value="{{ old('phone') }}" 
                                   class="form-input" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="document" class="form-label">CPF/CNPJ</label>
                            <input type="text" 
                                   id="document" 
                                   name="documento" 
                                   value="{{ old('document') }}" 
                                   class="form-input" 
                                   required>
                        </div>
                    </div>
                    
                    <h3 class="text-md font-semibold text-gray-900 mt-8 mb-4">Segurança</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="password" class="form-label">Senha</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" class="form-input" required>
                                <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Confirmar Senha</label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                                <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="text-md font-semibold text-gray-900 mt-8 mb-4">Endereço</h3>
                    
                    <div class="form-grid">
                        <div class="form-group md:col-span-2">
                            <label for="zipcode" class="form-label">CEP</label>
                            <div class="flex">
                                <input type="text" 
                                       id="zipcode" 
                                       name="cep" 
                                       value="{{ old('zipcode') }}" 
                                       class="form-input" 
                                       required>
                                <button type="button" 
                                        id="search-cep" 
                                        class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                    Buscar
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group md:col-span-2">
                            <label for="street" class="form-label">Logradouro</label>
                            <input type="text" 
                                   id="street" 
                                   name="endereco" 
                                   value="{{ old('street') }}" 
                                   class="form-input" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="number" class="form-label">Número</label>
                            <input type="text" 
                                   id="number" 
                                   name="numero" 
                                   value="{{ old('number') }}" 
                                   class="form-input" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="complement" class="form-label">Complemento</label>
                            <input type="text" 
                                   id="complement" 
                                   name="complemento" 
                                   value="{{ old('complement') }}" 
                                   class="form-input">
                        </div>
                        
                        <div class="form-group">
                            <label for="neighborhood" class="form-label">Bairro</label>
                            <input type="text" 
                                   id="neighborhood" 
                                   name="bairro" 
                                   value="{{ old('neighborhood') }}" 
                                   class="form-input" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="city" class="form-label">Cidade</label>
                            <input type="text" 
                                   id="city" 
                                   name="cidade" 
                                   value="{{ old('city') }}" 
                                   class="form-input" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="state" class="form-label">Estado</label>
                            <select id="state" 
                                    name="estado" 
                                    class="form-input"
                                    required>
                                <option value="">Selecione um estado</option>
                                <option value="AC" {{ old('estado') == 'AC' ? 'selected' : '' }}>Acre</option>
                                <option value="AL" {{ old('estado') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                                <option value="AP" {{ old('estado') == 'AP' ? 'selected' : '' }}>Amapá</option>
                                <option value="AM" {{ old('estado') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                                <option value="BA" {{ old('estado') == 'BA' ? 'selected' : '' }}>Bahia</option>
                                <option value="CE" {{ old('estado') == 'CE' ? 'selected' : '' }}>Ceará</option>
                                <option value="DF" {{ old('estado') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                                <option value="ES" {{ old('estado') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                                <option value="GO" {{ old('estado') == 'GO' ? 'selected' : '' }}>Goiás</option>
                                <option value="MA" {{ old('estado') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                                <option value="MT" {{ old('estado') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                                <option value="MS" {{ old('estado') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                                <option value="MG" {{ old('estado') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                                <option value="PA" {{ old('estado') == 'PA' ? 'selected' : '' }}>Pará</option>
                                <option value="PB" {{ old('estado') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                                <option value="PR" {{ old('estado') == 'PR' ? 'selected' : '' }}>Paraná</option>
                                <option value="PE" {{ old('estado') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                                <option value="PI" {{ old('estado') == 'PI' ? 'selected' : '' }}>Piauí</option>
                                <option value="RJ" {{ old('estado') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                                <option value="RN" {{ old('estado') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                                <option value="RS" {{ old('estado') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                                <option value="RO" {{ old('estado') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                                <option value="RR" {{ old('estado') == 'RR' ? 'selected' : '' }}>Roraima</option>
                                <option value="SC" {{ old('estado') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                                <option value="SP" {{ old('estado') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                                <option value="SE" {{ old('estado') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                                <option value="TO" {{ old('estado') == 'TO' ? 'selected' : '' }}>Tocantins</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group mt-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="terms" 
                                       name="terms" 
                                       type="checkbox" 
                                       class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded" 
                                       required>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="terms" class="font-medium text-gray-700">Li e aceito os <a href="#" class="text-primary hover:text-primary-dark">Termos de Uso</a> e <a href="#" class="text-primary hover:text-primary-dark">Política de Privacidade</a>.</label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-register">
                        Criar Conta
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
                
                <p class="login-link">
                    Já tem uma conta? 
                    <a href="{{ route('login') }}">Faça login</a>
                </p>
            </div>
        </div>
        
        <!-- Seção gráfica (visível apenas em telas grandes) -->
        <div class="register-graphic-section">
            <img src="{{ asset('img/izuspay-login.png') }}" alt="Izuspay" class="graphic-img">
        </div>
    </div>
    
    <script>
        // Função para alternar visibilidade da senha
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.querySelector(`[onclick="togglePassword('${fieldId}')"] i`);
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Máscara para CPF/CNPJ
        document.getElementById('document').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                // CNPJ: 00.000.000/0000-00
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
            } else {
                // CPF: 000.000.000-00
                value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4');
            }
            
            e.target.value = value;
        });
        
        // Máscara para telefone
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 10) {
                // (00) 00000-0000
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 5) {
                // (00) 0000-0000
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                // (00) 0
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else if (value.length > 0) {
                // (0
                value = value.replace(/^(\d*)/, '($1');
            }
            
            e.target.value = value;
        });
        
        // Máscara para CEP
        document.getElementById('zipcode').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 5) {
                value = value.replace(/^(\d{5})(\d{0,3})/, '$1-$2');
            }
            
            e.target.value = value;
        });
        
        // Buscar CEP
        document.getElementById('search-cep').addEventListener('click', function() {
            const zipcode = document.getElementById('zipcode').value.replace(/\D/g, '');
            
            if (zipcode.length !== 8) {
                alert('Por favor, digite um CEP válido com 8 dígitos.');
                return;
            }
            
            fetch(`https://viacep.com.br/ws/${zipcode}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        throw new Error('CEP não encontrado');
                    }
                    
                    document.getElementById('street').value = data.logradouro;
                    document.getElementById('neighborhood').value = data.bairro;
                    document.getElementById('city').value = data.localidade;
                    document.getElementById('state').value = data.uf;
                })
                .catch(error => {
                    console.error('Erro ao buscar CEP:', error);
                    alert('Não foi possível encontrar o endereço para o CEP informado.');
                });
        });
        
        // Adicionar máscara ao CNPJ da Empresa
        document.getElementById('association_document').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 0) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
            }
            
            e.target.value = value;
        });
        
        // Adicionar máscara ao telefone da Empresa
        document.getElementById('association_phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 10) {
                // (00) 00000-0000
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 5) {
                // (00) 0000-0000
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                // (00) 0
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else if (value.length > 0) {
                // (0
                value = value.replace(/^(\d*)/, '($1');
            }
            
            e.target.value = value;
        });
        
        // Desabilitar o botão de submit após o envio para evitar múltiplos cliques
        document.getElementById('register-form').addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = 'Criando conta...';
        });
    </script>
</body>
</html>
