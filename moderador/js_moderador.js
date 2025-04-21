document.addEventListener('DOMContentLoaded', () => {
    // Lógica del sidebar (existente)
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    let isLocked = false;

    // Abrir el sidebar al pasar el mouse por el botón (si no está bloqueado)
    menuToggle.addEventListener('mouseenter', () => {
        if (!isLocked) {
            sidebar.classList.add('open');
        }
    });

    // Cerrar el sidebar al salir del sidebar (si no está bloqueado)
    sidebar.addEventListener('mouseleave', () => {
        if (!isLocked) {
            sidebar.classList.remove('open');
        }
    });

    // Enclavar el sidebar abierto al hacer clic en el botón
    menuToggle.addEventListener('click', () => {
        if (!isLocked) {
            sidebar.classList.add('open');
            isLocked = true; // Enclavar abierto
        }
    });

    // Desenclavar y cerrar el sidebar al hacer clic en un botón dentro de él
    const buttons = sidebar.querySelectorAll('button');
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            if (isLocked) {
                isLocked = false;
                sidebar.classList.remove('open');
            }
        });
    });

    // Desenclavar y cerrar el sidebar al hacer clic fuera de él, si está bloqueado
    document.addEventListener('click', (e) => {
        if (isLocked && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            isLocked = false;
            sidebar.classList.remove('open');
        }
    });

    // Nueva lógica para el formulario de búsqueda y filtro
    const form = document.getElementById('filtroForm');
    const busquedaInput = document.getElementById('busqueda');
    const areaSelect = document.getElementById('area');

    // Verificar que los elementos existen antes de agregar eventos
    if (form && busquedaInput && areaSelect) {
        // Función para enviar el formulario con retraso (debounce)
        let timeoutId;
        function submitForm() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                form.submit();
            }, 300); // Retraso de 300ms para evitar envíos excesivos mientras se escribe
        }

        // Evento para el campo de búsqueda (se dispara al escribir)
        busquedaInput.addEventListener('input', submitForm);

        // Evento para el select de áreas (se dispara al cambiar la selección)
        areaSelect.addEventListener('change', () => {
            form.submit(); // Sin retraso para cambios en el select
        });
    } else {
        console.error('No se encontraron los elementos del formulario: filtroForm, busqueda o area');
    }
});