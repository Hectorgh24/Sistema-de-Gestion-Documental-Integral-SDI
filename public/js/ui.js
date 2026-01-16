/**
 * Módulo de UI - Notificaciones y componentes visuales
 */

const ui = {
    /**
     * Mostrar notificación toast
     */
    toast(mensaje, tipo = 'info', duracion = 3000) {
        const toast = document.createElement('div');
        const colores = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        };

        toast.className = `${colores[tipo]} text-white px-4 py-3 rounded-lg shadow-lg fade-in`;
        toast.textContent = mensaje;

        const container = document.getElementById('toastContainer');
        if (container) {
            container.appendChild(toast);
            setTimeout(() => toast.remove(), duracion);
        }
    },

    /**
     * Mostrar modal
     */
    modal(titulo, contenido, botones = []) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
                <h2 class="text-xl font-bold mb-4">${titulo}</h2>
                <div class="mb-6">${contenido}</div>
                <div class="flex gap-2 justify-end">
                    ${botones.map(b => `<button class="px-4 py-2 ${b.class}" onclick="${b.onclick}">${b.text}</button>`).join('')}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        return modal;
    },

    /**
     * Mostrar spinner de carga
     */
    mostrarCarga(mensaje = 'Cargando...') {
        const carga = document.createElement('div');
        carga.className = 'flex items-center justify-center gap-2';
        carga.innerHTML = `<i class="fas fa-spinner fa-spin text-blue-500"></i><span>${mensaje}</span>`;
        return carga;
    }
};
