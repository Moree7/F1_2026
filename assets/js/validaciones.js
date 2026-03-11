// Validaciones del lado del cliente para todos los formularios
// No uso el atributo required de HTML, lo hago todo desde JS
// También están aquí las confirmaciones de borrar y editar

"use strict";

// Recorre los campos con data-requerido="true" y comprueba que no estén vacíos
// Devuelve false si hay algún error para que el form no se envíe
function validarFormulario(formularioId) {
    const form = document.getElementById(formularioId);
    if (!form) return true;

    let valido = true;
    const campos = form.querySelectorAll('[data-requerido="true"]');

    // Quito los errores del intento anterior
    campos.forEach(campo => {
        campo.classList.remove('is-invalid');
        const errDiv = campo.nextElementSibling;
        if (errDiv && errDiv.classList.contains('invalid-feedback')) {
            errDiv.textContent = '';
        }
    });

    campos.forEach(campo => {
        const valor = campo.value.trim();

        if (valor === '') {
            marcarError(campo, 'Este campo es obligatorio.');
            valido = false;
            return;
        }

        // Compruebo formato de email
        if (campo.type === 'email') {
            const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regexEmail.test(valor)) {
                marcarError(campo, 'Introduce un email válido.');
                valido = false;
                return;
            }
        }

        // Longitud mínima para contraseñas
        if (campo.type === 'password' && campo.dataset.minlen) {
            if (valor.length < parseInt(campo.dataset.minlen)) {
                marcarError(campo, `Mínimo ${campo.dataset.minlen} caracteres.`);
                valido = false;
                return;
            }
        }

        // Los campos número no pueden ser negativos
        if (campo.type === 'number') {
            if (parseFloat(valor) < 0) {
                marcarError(campo, 'El valor debe ser positivo.');
                valido = false;
                return;
            }
        }
    });

    // Si hay campos de repetir contraseña, los comparo
    const passNueva  = form.querySelector('#password_nuevo');
    const passRepite = form.querySelector('#password_repetir');
    if (passNueva && passRepite && passNueva.value && passRepite.value) {
        if (passNueva.value !== passRepite.value) {
            marcarError(passRepite, 'Las contraseñas no coinciden.');
            valido = false;
        }
    }

    // Si algo falla muestro un aviso con SweetAlert
    if (!valido) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos incompletos',
            text: 'Por favor, rellena correctamente todos los campos obligatorios.',
            confirmButtonColor: '#e8002d',
            background: '#1a1a1a',
            color: '#f0f0f0'
        });
    }

    return valido;
}

// Pone el campo en rojo y muestra el mensaje de error debajo
function marcarError(campo, mensaje) {
    campo.classList.add('is-invalid');
    let errDiv = campo.nextElementSibling;
    if (!errDiv || !errDiv.classList.contains('invalid-feedback')) {
        errDiv = document.createElement('div');
        errDiv.classList.add('invalid-feedback');
        campo.parentNode.insertBefore(errDiv, campo.nextSibling);
    }
    errDiv.textContent = mensaje;
}

// Confirmación antes de borrar un registro
// Lo llamo desde onclick="return confirmarBorrado(event, this.href)"
function confirmarBorrado(event, url) {
    event.preventDefault();

    Swal.fire({
        icon: 'warning',
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e8002d',
        cancelButtonColor: '#555',
        background: '#1a1a1a',
        color: '#f0f0f0'
    }).then(result => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });

    return false;
}

// Confirmación antes de guardar cambios en una edición
function confirmarEdicion(event, formularioId) {
    event.preventDefault();

    // Primero valido, si hay errores no sigo
    if (!validarFormulario(formularioId)) return false;

    Swal.fire({
        icon: 'question',
        title: 'Confirmar cambios',
        text: '¿Deseas guardar los cambios realizados?',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e8002d',
        cancelButtonColor: '#555',
        background: '#1a1a1a',
        color: '#f0f0f0'
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById(formularioId).submit();
        }
    });

    return false;
}

// Cuando carga la página asigno los eventos de borrar y marco el enlace activo
document.addEventListener('DOMContentLoaded', function () {

    // Engancho el evento de confirmación a todos los botones de borrar
    document.querySelectorAll('.btn-borrar').forEach(btn => {
        btn.addEventListener('click', function (e) {
            confirmarBorrado(e, this.getAttribute('href'));
        });
    });

    // Resalto el enlace del navbar que corresponde a la página actual
    const rutaActual = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') && rutaActual.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
});
