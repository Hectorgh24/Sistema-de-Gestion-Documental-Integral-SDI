<?php
/**
 * Vista del Dashboard - SDI Gestión Documental
 * Dashboard principal con baldosas según el rol del usuario
 * 
 * Incluye: Modo día/noche y botón flotante de accesibilidad
 */

// Prevenir acceso directo
if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/../../config/autoload.php';
}

// Obtener datos del usuario
$nombreUsuario = escapeOutput($usuario['nombre']);
$rolUsuario = escapeOutput($usuario['rol']);
$emailUsuario = escapeOutput($usuario['email']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SDI Gestión Documental</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <style>
        /* Estilos para modo oscuro/claro */
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f3f4f6;
            --bg-card: #ffffff;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
        }
        
        [data-theme="dark"] {
            --bg-primary: #1f2937;
            --bg-secondary: #111827;
            --bg-card: #374151;
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --border-color: #4b5563;
        }
        
        body {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            transition: background-color 0.3s, color 0.3s;
        }
        
        .card {
            background-color: var(--bg-card);
            border-color: var(--border-color);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        [data-theme="dark"] .card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        /* Botón flotante de accesibilidad */
        .accessibility-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .accessibility-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        /* Panel de accesibilidad */
        .accessibility-panel {
            position: fixed;
            bottom: 90px;
            left: 20px;
            z-index: 999;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            min-width: 250px;
            display: none;
        }
        
        .accessibility-panel.show {
            display: block;
        }
        
        /* Modo de alto contraste */
        body.high-contrast {
            background-color: #000000;
            color: #ffffff;
        }
        
        body.high-contrast .card {
            background-color: #000000;
            border: 2px solid #ffffff;
            color: #ffffff;
        }
        
        body.high-contrast a {
            color: #ffff00;
        }
        
        body.high-contrast button {
            border: 2px solid #ffffff;
        }
    </style>
</head>
<body>
    
    <!-- Barra de Navegación -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        SDI Gestión Documental
                    </h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Información del Usuario -->
                    <div class="hidden md:flex items-center space-x-3">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo $nombreUsuario; ?>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <?php echo $rolUsuario; ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                            <?php echo strtoupper(substr($nombreUsuario, 0, 1)); ?>
                        </div>
                    </div>
                    
                    <!-- Botón Cerrar Sesión -->
                    <a href="/login.php?accion=logout" 
                       class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors">
                        Cerrar Sesión
                    </a>
                    
                    <!-- Toggle Modo Oscuro -->
                    <button 
                        id="themeToggle" 
                        class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        aria-label="Cambiar tema"
                    >
                        <svg id="sunIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg id="moonIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Bienvenida -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Bienvenido, <?php echo $nombreUsuario; ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                Panel de control - <?php echo $rolUsuario; ?>
            </p>
        </div>
        
        <!-- Estadísticas -->
        <?php if (!empty($estadisticas)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php 
            $colores = ['blue', 'green', 'purple', 'orange', 'red', 'indigo'];
            $i = 0;
            foreach ($estadisticas as $key => $value): 
                $color = $colores[$i % count($colores)];
                $titulo = ucwords(str_replace('_', ' ', $key));
            ?>
            <div class="card border rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            <?php echo escapeOutput($titulo); ?>
                        </p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            <?php echo number_format($value); ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-<?php echo $color; ?>-100 dark:bg-<?php echo $color; ?>-900 flex items-center justify-center">
                        <svg class="w-6 h-6 text-<?php echo $color; ?>-600 dark:text-<?php echo $color; ?>-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <?php 
                $i++;
            endforeach; 
            ?>
        </div>
        <?php endif; ?>
        
        <!-- Módulos (Baldosas) -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Módulos Disponibles
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($modulos as $modulo): ?>
                <a href="<?php echo escapeOutput($modulo['url']); ?>" 
                   class="card border rounded-lg p-6 block hover:shadow-lg">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-lg bg-<?php echo $modulo['color']; ?>-100 dark:bg-<?php echo $modulo['color']; ?>-900 flex items-center justify-center">
                                <svg class="w-6 h-6 text-<?php echo $modulo['color']; ?>-600 dark:text-<?php echo $modulo['color']; ?>-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?php if ($modulo['icono'] === 'document-text'): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    <?php elseif ($modulo['icono'] === 'folder'): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                    <?php elseif ($modulo['icono'] === 'users'): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    <?php elseif ($modulo['icono'] === 'chart-bar'): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    <?php elseif ($modulo['icono'] === 'document-duplicate'): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                    <?php elseif ($modulo['icono'] === 'search'): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    <?php elseif ($modulo['icono'] === 'folder-open'): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h12a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path>
                                    <?php endif; ?>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                <?php echo escapeOutput($modulo['titulo']); ?>
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <?php echo escapeOutput($modulo['descripcion']); ?>
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
    </main>
    
    <!-- Botón Flotante de Accesibilidad -->
    <button 
        id="accessibilityBtn" 
        class="accessibility-btn"
        aria-label="Opciones de accesibilidad"
        title="Opciones de accesibilidad"
    >
        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
    </button>
    
    <!-- Panel de Accesibilidad -->
    <div id="accessibilityPanel" class="accessibility-panel">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
            Opciones de Accesibilidad
        </h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Tamaño de Fuente
                </label>
                <div class="flex space-x-2">
                    <button onclick="changeFontSize(-2)" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                        A-
                    </button>
                    <button onclick="changeFontSize(0)" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                        A
                    </button>
                    <button onclick="changeFontSize(2)" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                        A+
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Contraste
                </label>
                <button onclick="toggleHighContrast()" class="w-full px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                    Activar Alto Contraste
                </button>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
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
        
        // Panel de Accesibilidad
        (function() {
            const btn = document.getElementById('accessibilityBtn');
            const panel = document.getElementById('accessibilityPanel');
            
            btn.addEventListener('click', () => {
                panel.classList.toggle('show');
            });
            
            // Cerrar al hacer clic fuera
            document.addEventListener('click', (e) => {
                if (!btn.contains(e.target) && !panel.contains(e.target)) {
                    panel.classList.remove('show');
                }
            });
        })();
        
        // Funciones de accesibilidad
        function changeFontSize(size) {
            const html = document.documentElement;
            const currentSize = parseFloat(getComputedStyle(html).fontSize) || 16;
            const newSize = size === 0 ? 16 : currentSize + size;
            html.style.fontSize = newSize + 'px';
            localStorage.setItem('fontSize', newSize);
        }
        
        function toggleHighContrast() {
            document.body.classList.toggle('high-contrast');
            const isActive = document.body.classList.contains('high-contrast');
            localStorage.setItem('highContrast', isActive);
        }
        
        // Cargar preferencias guardadas
        (function() {
            const savedFontSize = localStorage.getItem('fontSize');
            if (savedFontSize) {
                document.documentElement.style.fontSize = savedFontSize + 'px';
            }
            
            const savedHighContrast = localStorage.getItem('highContrast');
            if (savedHighContrast === 'true') {
                document.body.classList.add('high-contrast');
            }
        })();
    </script>
</body>
</html>

