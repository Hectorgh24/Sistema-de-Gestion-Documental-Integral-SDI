/**
 * Módulo de Autenticación - SDI Gestión Documental
 */

const auth = {
    usuario: null,

    /**
     * Logging de errores de autenticación
     */
    _logError(context, error) {
        console.error(`[AUTH] Error en ${context}:`, {
            message: error?.message || String(error),
            status: error?.status,
            data: error?.data,
            stack: error?.stack
        });
    },

    async verificar() {
        try {
            console.log('[AUTH] Verificando autenticación...');
            const resultado = await api.get('/auth/verificar');
            
            console.log('[AUTH] Resultado de verificación:', resultado);
            
            if (resultado && resultado.success) {
                this.usuario = resultado.data;
                console.log('[AUTH] Usuario autenticado:', this.usuario);
                return true;
            }
            
            console.log('[AUTH] Usuario no autenticado');
            return false;
            
        } catch (error) {
            this._logError('verificar', error);
            
            // Si es un 401, simplemente no está autenticado (no es un error)
            if (error?.status === 401) {
                console.log('[AUTH] Usuario no autenticado (401)');
                return false;
            }
            
            // Otros errores son problemas reales
            console.error('[AUTH] Error al verificar autenticación:', error);
            return false;
        }
    },

    async login(email, password) {
        try {
            console.log('[AUTH] Iniciando login para:', email);
            
            const resultado = await api.post('/auth/login', {
                email: email.trim().toLowerCase(),
                password
            });
            
            console.log('[AUTH] Resultado de login:', resultado);
            
            if (resultado && resultado.success) {
                console.log('[AUTH] Login exitoso');
                this.usuario = resultado.data;
            }
            
            return resultado;
            
        } catch (error) {
            this._logError('login', error);
            
            // Si el error tiene información del servidor, retornarla
            if (error?.data) {
                return error.data;
            }
            
            // Si no, crear una respuesta de error estándar
            return {
                success: false,
                message: error?.message || 'Error al iniciar sesión',
                data: null,
                status: error?.status || 500
            };
        }
    },

    async logout() {
        try {
            console.log('[AUTH] Cerrando sesión...');
            const resultado = await api.get('/auth/logout');
            this.usuario = null;
            console.log('[AUTH] Sesión cerrada');
            return resultado;
        } catch (error) {
            this._logError('logout', error);
            // Aún así, limpiar el usuario local
            this.usuario = null;
            return {
                success: false,
                message: error?.message || 'Error al cerrar sesión',
                data: null
            };
        }
    },

    async cambiarPassword(passwordActual, passwordNueva) {
        try {
            console.log('[AUTH] Cambiando contraseña...');
            return await api.post('/auth/cambiarPassword', {
                password_actual: passwordActual,
                password_nueva: passwordNueva
            });
        } catch (error) {
            this._logError('cambiarPassword', error);
            
            if (error?.data) {
                return error.data;
            }
            
            return {
                success: false,
                message: error?.message || 'Error al cambiar contraseña',
                data: null
            };
        }
    },

    getUsuario() { return this.usuario; },
    getRol() { return this.usuario?.rol; },
    tieneRol(rol) { return this.usuario?.rol === rol; },
    esAdmin() { return this.tieneRol('Administrador'); },
    esAdministrativo() { return this.tieneRol('Personal Administrativo'); },
    esEstudiante() { return this.tieneRol('Estudiante SS'); }
};
