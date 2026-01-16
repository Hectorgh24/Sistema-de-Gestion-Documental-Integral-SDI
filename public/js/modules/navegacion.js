/**
 * Módulo de Navegación - SDI Gestión Documental
 * 
 * Gestiona el historial de navegación y el botón "Volver Atrás"
 * 
 * @author SDI Development Team
 * @version 2.0
 */

const navegacionModule = {
    historial: [],
    moduloActual: 'dashboard',

    /**
     * Inicializar módulo de navegación
     */
    init() {
        // Agregar dashboard como punto inicial
        this.agregarAlHistorial('dashboard');
    },

    /**
     * Agregar módulo al historial
     */
    agregarAlHistorial(modulo) {
        // No agregar si es el mismo módulo actual
        if (this.moduloActual === modulo) {
            this.actualizarBotonVolver();
            return;
        }

        // Agregar el módulo anterior al historial solo si no es dashboard o si ya hay historial
        if (this.moduloActual && this.moduloActual !== modulo) {
            // Si el módulo actual no es dashboard, agregarlo al historial
            if (this.moduloActual !== 'dashboard') {
                this.historial.push(this.moduloActual);
            } else if (this.historial.length === 0 && modulo !== 'dashboard') {
                // Si venimos del dashboard y no hay historial, no agregar dashboard
                // (dashboard es el punto de inicio)
            }
        }

        // Limitar historial a 10 elementos
        if (this.historial.length > 10) {
            this.historial.shift();
        }

        this.moduloActual = modulo;
        this.actualizarBotonVolver();
    },

    /**
     * Volver al módulo anterior
     */
    async volverAtras() {
        // Si hay historial, volver al último módulo
        if (this.historial.length > 0) {
            const moduloAnterior = this.historial.pop();
            // Actualizar módulo actual sin agregar al historial
            this.moduloActual = moduloAnterior;
            await cargarModuloSinHistorial(moduloAnterior);
        } else {
            // Si no hay historial, volver al dashboard
            this.moduloActual = 'dashboard';
            await cargarModuloSinHistorial('dashboard');
        }
        
        // Actualizar botón después de volver
        this.actualizarBotonVolver();
    },

    /**
     * Obtener módulo anterior
     */
    obtenerModuloAnterior() {
        if (this.historial.length === 0) {
            return 'dashboard';
        }
        return this.historial[this.historial.length - 1];
    },

    /**
     * Actualizar visibilidad del botón volver
     */
    actualizarBotonVolver() {
        const backButtonContainer = document.getElementById('backButtonContainer');
        
        if (!backButtonContainer) {
            return;
        }

        // Mostrar botón solo si no estamos en dashboard
        // Siempre se puede volver al dashboard si estamos en otro módulo
        if (this.moduloActual !== 'dashboard') {
            backButtonContainer.classList.remove('hidden');
        } else {
            backButtonContainer.classList.add('hidden');
        }
    },

    /**
     * Limpiar historial
     */
    limpiarHistorial() {
        this.historial = [];
        this.moduloActual = 'dashboard';
        this.actualizarBotonVolver();
    },

    /**
     * Reiniciar navegación (volver al dashboard)
     */
    async reiniciar() {
        this.limpiarHistorial();
        await cargarModulo('dashboard');
    }
};

// Hacer disponible globalmente
window.navegacionModule = navegacionModule;