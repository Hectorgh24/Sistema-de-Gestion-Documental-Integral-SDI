<?php
/**
 * Vista de Login - SDI Gestión Documental
 * Formulario de autenticación con validación cliente y servidor
 */

// Prevenir acceso directo
if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/../../config/autoload.php';
}

// Obtener mensajes de error/success
$error = isset($_GET['error']) ? sanitizeInput($_GET['error']) : '';
$mensaje = isset($_GET['mensaje']) ? sanitizeInput($_GET['mensaje']) : '';
$errorLogin = isset($_SESSION['error_login']) ? $_SESSION['error_login'] : [];
unset($_SESSION['error_login']); // Limpiar después de mostrar

// Valores del formulario (para mantener en caso de error)
$emailValue = isset($_POST['email']) ? escapeOutput($_POST['email']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SDI Gestión Documental</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <style>
        /* Estilos adicionales para modo oscuro/claro */
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f3f4f6;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
        }
        
        [data-theme="dark"] {
            --bg-primary: #1f2937;
            --bg-secondary: #111827;
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --border-color: #374151;
        }
        
        body {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            transition: background-color 0.3s, color 0.3s;
        }
        
        .login-container {
            background-color: var(--bg-primary);
            border-color: var(--border-color);
        }
        
        .input-field {
            background-color: var(--bg-primary);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        
        .input-field:focus {
            border-color: #3b82f6;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-md w-full space-y-8 login-container border rounded-lg shadow-lg p-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                SDI Gestión Documental
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Inicia sesión en tu cuenta
            </p>
        </div>
        
        <!-- Mensajes de Error -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo escapeOutput($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errorLogin)): ?>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    <?php foreach ($errorLogin as $err): ?>
                        <li><?php echo escapeOutput($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Mensajes de Éxito -->
        <?php if (!empty($mensaje)): ?>
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo escapeOutput($mensaje); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Formulario de Login -->
        <form class="mt-8 space-y-6" action="/login.php" method="POST" id="loginForm" novalidate>
            
            <div class="space-y-4">
                <!-- Campo Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Correo Electrónico
                    </label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required 
                        class="appearance-none relative block w-full px-3 py-2 border input-field rounded-md placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors"
                        placeholder="tu@email.com"
                        value="<?php echo $emailValue; ?>"
                    >
                    <span class="text-red-500 text-xs mt-1 hidden" id="email-error"></span>
                </div>
                
                <!-- Campo Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Contraseña
                    </label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        autocomplete="current-password" 
                        required 
                        class="appearance-none relative block w-full px-3 py-2 border input-field rounded-md placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors"
                        placeholder="••••••••"
                    >
                    <span class="text-red-500 text-xs mt-1 hidden" id="password-error"></span>
                </div>
            </div>
            
            <!-- Botón de Envío -->
            <div>
                <button 
                    type="submit" 
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    id="submitBtn"
                >
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                    </span>
                    Iniciar Sesión
                </button>
            </div>
        </form>
        
        <!-- Footer -->
        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
            <p>Sistema de Gestión Documental</p>
        </div>
    </div>
    
    <!-- Toggle Modo Oscuro/Claro -->
    <button 
        id="themeToggle" 
        class="fixed top-4 right-4 p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
        aria-label="Cambiar tema"
        title="Cambiar tema"
    >
        <svg id="sunIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        <svg id="moonIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
        </svg>
    </button>
    
    <!-- JavaScript para validación del lado del cliente -->
    <script>
        // Tema Oscuro/Claro
        (function() {
            const themeToggle = document.getElementById('themeToggle');
            const sunIcon = document.getElementById('sunIcon');
            const moonIcon = document.getElementById('moonIcon');
            const html = document.documentElement;
            
            // Cargar tema guardado
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                html.setAttribute('data-theme', 'dark');
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            }
            
            // Toggle tema
            themeToggle.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-theme');
                if (currentTheme === 'dark') {
                    html.removeAttribute('data-theme');
                    localStorage.setItem('theme', 'light');
                    sunIcon.classList.add('hidden');
                    moonIcon.classList.remove('hidden');
                } else {
                    html.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                    sunIcon.classList.remove('hidden');
                    moonIcon.classList.add('hidden');
                }
            });
        })();
        
        // Validación del formulario (lado del cliente)
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const emailError = document.getElementById('email-error');
            const passwordError = document.getElementById('password-error');
            let isValid = true;
            
            // Limpiar errores previos
            emailError.classList.add('hidden');
            passwordError.classList.add('hidden');
            email.classList.remove('border-red-500');
            password.classList.remove('border-red-500');
            
            // Validar email
            if (!email.value.trim()) {
                emailError.textContent = 'El email es requerido';
                emailError.classList.remove('hidden');
                email.classList.add('border-red-500');
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                emailError.textContent = 'El email no es válido';
                emailError.classList.remove('hidden');
                email.classList.add('border-red-500');
                isValid = false;
            }
            
            // Validar contraseña
            if (!password.value) {
                passwordError.textContent = 'La contraseña es requerida';
                passwordError.classList.remove('hidden');
                password.classList.add('border-red-500');
                isValid = false;
            } else if (password.value.length < 6) {
                passwordError.textContent = 'La contraseña debe tener al menos 6 caracteres';
                passwordError.classList.remove('hidden');
                password.classList.add('border-red-500');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Deshabilitar botón durante el envío
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Iniciando sesión...';
        });
    </script>
</body>
</html>

