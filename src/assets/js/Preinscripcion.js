window.mostrarFormulario = function(tipo) {
    const formParticipante = document.getElementById('form-participante');
    const formPonente = document.getElementById('form-ponente');
    
    if (!formParticipante || !formPonente) {
        console.error('Formularios no encontrados');
        return;
    }
    
    formParticipante.style.display = 'none';
    formPonente.style.display = 'none';
    
    if (tipo === 'participante') {
        formParticipante.style.display = 'block';
    } else if (tipo === 'ponente') {
        formPonente.style.display = 'block';
    }
    
    const btnParticipante = document.querySelector('button[onclick*="participante"]');
    const btnPonente = document.querySelector('button[onclick*="ponente"]');
    
    if (btnParticipante && btnPonente) {
        if (tipo === 'participante') {
            btnParticipante.classList.remove('btn-outline-primary');
            btnParticipante.classList.add('btn-primary');
            btnPonente.classList.remove('btn-secondary');
            btnPonente.classList.add('btn-outline-secondary');
        } else {
            btnPonente.classList.remove('btn-outline-secondary');
            btnPonente.classList.add('btn-secondary');
            btnParticipante.classList.remove('btn-primary');
            btnParticipante.classList.add('btn-outline-primary');
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    configurarFormularios();
    configurarCamposDinamicos();
    configurarContadorPalabras();
    configurarValidacionTildes();
    configurarValidacionArchivos();
    mostrarFormulario('participante');
});

function configurarCamposDinamicos() {
    const tipoPonente = document.getElementById('tipoPonente');
    const camposEgresado = document.getElementById('camposEgresado');
    const camposNacional = document.getElementById('camposNacional');
    
    if (tipoPonente && camposEgresado && camposNacional) {
        tipoPonente.addEventListener('change', function() {
            const valorSeleccionado = this.value;
            
            camposEgresado.style.display = 'none';
            camposNacional.style.display = 'none';
            habilitarValidacionEgresado(false);
            habilitarValidacionNacional(false);
            limpiarCamposEgresado();
            limpiarCamposNacional();
            
            if (valorSeleccionado === '1') {
                camposNacional.style.display = 'block';
                habilitarValidacionNacional(true);
            } else if (valorSeleccionado === '2') {
                camposEgresado.style.display = 'block';
                habilitarValidacionEgresado(true);
            }
        });
    }
}

function habilitarValidacionEgresado(habilitar) {
    const campos = [
        'fechaGraduacion', 'ultimoEstudio',
        'ciudadResidencia', 'cargo', 'empresa', 'experiencia', 
        'tematicaEgresado', 'tituloPresentacionEgresado', 'hojaVida', 'diapositivasEgresado', 'motivacion'
    ];
    
    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            if (habilitar) {
                campo.setAttribute('required', 'required');
            } else {
                campo.removeAttribute('required');
            }
        }
    });
}

function habilitarValidacionNacional(habilitar) {
    const campos = ['tematicaNacional', 'tituloPresentacionNacional', 'diapositivasNacional'];
    
    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            if (habilitar) {
                campo.setAttribute('required', 'required');
            } else {
                campo.removeAttribute('required');
            }
        }
    });
}

function limpiarCamposEgresado() {
    const campos = [
        'fechaGraduacion', 'ultimoEstudio',
        'ciudadResidencia', 'cargo', 'empresa', 'experiencia', 
        'tematicaEgresado', 'tituloPresentacionEgresado', 'hojaVida', 'diapositivasEgresado', 'motivacion'
    ];
    
    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            if (campo.type === 'file') {
                campo.value = '';
            } else if (campo.tagName === 'SELECT') {
                campo.selectedIndex = 0;
            } else {
                campo.value = '';
            }
        }
    });
}

function limpiarCamposNacional() {
    const campos = ['tematicaNacional', 'tituloPresentacionNacional', 'diapositivasNacional'];
    
    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            if (campo.type === 'file') {
                campo.value = '';
            } else if (campo.tagName === 'SELECT') {
                campo.selectedIndex = 0;
            } else {
                campo.value = '';
            }
        }
    });
}

function configurarContadorPalabras() {
    const experiencia = document.getElementById('experiencia');
    const contador = document.getElementById('contadorPalabras');
    
    if (experiencia && contador) {
        experiencia.addEventListener('input', function() {
            const texto = this.value.trim();
            const palabras = texto === '' ? 0 : texto.split(/\s+/).length;
            contador.textContent = palabras;
            
            if (palabras > 300) {
                this.classList.add('is-invalid');
                contador.style.color = '#dc3545';
            } else {
                this.classList.remove('is-invalid');
                contador.style.color = '#6c757d';
            }
        });
    }
}

function configurarFormularios() {
    const formParticipante = document.getElementById('formParticipante');
    if (formParticipante) {
        formParticipante.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (typeof preinscripcionHabilitada !== 'undefined' && !preinscripcionHabilitada) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Inscripciones cerradas',
                    text: 'Las inscripciones no están disponibles en este momento.',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }
            
            if (this.checkValidity()) {
                enviarFormulario(this, 'participante');
            }
            this.classList.add('was-validated');
        });
    }

    const formPonente = document.getElementById('formPonente');
    if (formPonente) {
        formPonente.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (typeof preinscripcionHabilitada !== 'undefined' && !preinscripcionHabilitada) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Inscripciones cerradas',
                    text: 'Las inscripciones no están disponibles en este momento.',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }
            
            const tipoPonente = document.getElementById('tipoPonente');
            if (tipoPonente && tipoPonente.value === '2') {
                const experiencia = document.getElementById('experiencia');
                if (experiencia && experiencia.value.trim() !== '') {
                    const palabras = experiencia.value.trim().split(/\s+/).length;
                    if (palabras > 300) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Límite excedido',
                            text: 'La descripción de experiencia no puede superar 300 palabras.',
                            confirmButtonColor: '#f39c12'
                        });
                        return;
                    }
                }
            }
            
            if (this.checkValidity()) {
                enviarFormulario(this, 'ponente');
            }
            this.classList.add('was-validated');
        });
    }
    
    document.querySelectorAll('input[name="numDoc"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    
    const telefonoE = document.getElementById('telefonoE');
    if (telefonoE) {
        telefonoE.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
}

function configurarValidacionArchivos() {
    const hojaVida = document.getElementById('hojaVida');
    if (hojaVida) {
        hojaVida.addEventListener('change', function() {
            validarArchivo(this, 5, ['pdf'], 'hoja de vida');
        });
    }
    
    const diapositivasEgresado = document.getElementById('diapositivasEgresado');
    if (diapositivasEgresado) {
        diapositivasEgresado.addEventListener('change', function() {
            validarArchivo(this, 10, ['pdf', 'ppt', 'pptx'], 'diapositivas');
        });
    }
    
    const diapositivasNacional = document.getElementById('diapositivasNacional');
    if (diapositivasNacional) {
        diapositivasNacional.addEventListener('change', function() {
            validarArchivo(this, 10, ['pdf', 'ppt', 'pptx'], 'diapositivas');
        });
    }
}

function validarArchivo(input, maxSizeMB, extensionesPermitidas, nombreArchivo) {
    const archivo = input.files[0];
    if (archivo) {
        const tamano = archivo.size / 1024 / 1024;
        const extension = archivo.name.split('.').pop().toLowerCase();
        
        if (tamano > maxSizeMB) {
            Swal.fire({
                icon: 'warning',
                title: 'Archivo muy grande',
                text: `El archivo de ${nombreArchivo} no puede superar los ${maxSizeMB}MB`,
                confirmButtonColor: '#f39c12'
            });
            input.value = '';
            return false;
        }
        
        if (!extensionesPermitidas.includes(extension)) {
            Swal.fire({
                icon: 'warning',
                title: 'Formato no válido',
                text: `Solo se permiten archivos ${extensionesPermitidas.join(', ').toUpperCase()} para ${nombreArchivo}`,
                confirmButtonColor: '#f39c12'
            });
            input.value = '';
            return false;
        }
    }
    return true;
}

async function enviarFormulario(form, tipo) {
    const btn = form.querySelector('button[type="submit"]');
    const btnText = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    btn.disabled = true;

    try {
        const formData = new FormData(form);
        
        const response = await fetch('../../SQL/preinscripcion.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            await Swal.fire({
                icon: 'success',
                title: '¡Inscripción exitosa!',
                text: result.message,
                confirmButtonColor: '#28a745',
                timer: 3000,
                timerProgressBar: true
            });

            form.reset();
            form.classList.remove('was-validated');
            
            if (tipo === 'ponente') {
                const camposEgresado = document.getElementById('camposEgresado');
                const camposNacional = document.getElementById('camposNacional');
                if (camposEgresado) camposEgresado.style.display = 'none';
                if (camposNacional) camposNacional.style.display = 'none';
            }
            
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error en la inscripción',
                text: result.message,
                confirmButtonColor: '#dc3545'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo procesar la inscripción. Inténtalo de nuevo.',
            confirmButtonColor: '#dc3545'
        });
    } finally {
        btn.innerHTML = btnText;
        btn.disabled = false;
    }
}

function configurarValidacionTildes() {
    const camposNombreApellido = [
        'nombreP', 'apellidoP',
        'nombreE', 'apellidoE'
    ];
    
    camposNombreApellido.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.addEventListener('input', function() {
                validarTildes(this);
            });
            
            campo.addEventListener('blur', function() {
                validarTildes(this);
            });
        }
    });
}

function validarTildes(campo) {
    const valor = campo.value;
    const tieneTildes = /[áéíóúÁÉÍÓÚñÑüÜ]/.test(valor);
    
    if (tieneTildes) {
        Swal.fire({
            icon: 'warning',
            title: 'No se permiten tildes',
            text: 'Por favor escribe tu ' + (campo.name === 'nombre' ? 'nombre' : 'apellido') + ' sin tildes ni caracteres especiales.',
            confirmButtonColor: '#f39c12',
            timer: 3000,
            timerProgressBar: true
        });
        
        campo.value = removerTildes(valor);
        
        campo.classList.add('is-invalid');
        setTimeout(() => {
            campo.classList.remove('is-invalid');
        }, 2000);
    }
}

function removerTildes(texto) {
    const mapaAcentos = {
        'á': 'a', 'é': 'e', 'í': 'i', 'ó': 'o', 'ú': 'u',
        'Á': 'A', 'É': 'E', 'Í': 'I', 'Ó': 'O', 'Ú': 'U',
        'ñ': 'n', 'Ñ': 'N',
        'ü': 'u', 'Ü': 'U'
    };
    
    return texto.split('').map(letra => mapaAcentos[letra] || letra).join('');
}