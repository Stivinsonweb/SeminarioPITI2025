let requestInProgress = false;
let lastRequestTime = 0;
const MIN_REQUEST_INTERVAL = 2000;

document.addEventListener("DOMContentLoaded", function () {
    const formCert = document.getElementById("formCertificado");
    
    if (formCert) {
        formCert.addEventListener("submit", async function (e) {
            e.preventDefault();
            
            if (requestInProgress) {
                Swal.fire({
                    icon: "warning",
                    title: "Solicitud en progreso",
                    text: "Por favor espere a que termine la solicitud actual.",
                    confirmButtonColor: "#f39c12"
                });
                return;
            }
            
            const now = Date.now();
            if (now - lastRequestTime < MIN_REQUEST_INTERVAL) {
                Swal.fire({
                    icon: "warning",
                    title: "Demasiadas solicitudes",
                    text: "Por favor espere unos segundos antes de intentar nuevamente.",
                    confirmButtonColor: "#f39c12"
                });
                return;
            }
            
            const numDoc = document.getElementById("numeroDocumento").value.trim();
            
            if (numDoc === "") {
                Swal.fire({
                    icon: "warning",
                    title: "Campo vacío",
                    text: "Por favor ingrese su número de documento.",
                    confirmButtonColor: "#f39c12"
                });
                return;
            }
            
            if (!/^[0-9]{6,10}$/.test(numDoc)) {
                Swal.fire({
                    icon: "warning",
                    title: "Documento inválido",
                    text: "El número de documento debe contener solo números (6-10 dígitos).",
                    confirmButtonColor: "#f39c12"
                });
                return;
            }
            
            requestInProgress = true;
            lastRequestTime = now;
            
            Swal.fire({
                title: 'Validando certificado...',
                html: '<div class="spinner"></div><p>Por favor espere</p>',
                allowEscapeKey: false,
                allowOutsideClick: false,
                showConfirmButton: false,
                customClass: {
                    popup: 'loading-popup'
                }
            });
            
            if (!document.getElementById('spinner-styles')) {
                const style = document.createElement('style');
                style.id = 'spinner-styles';
                style.textContent = `
                    .spinner {
                        border: 4px solid #f3f3f3;
                        border-top: 4px solid #3498db;
                        border-radius: 50%;
                        width: 40px;
                        height: 40px;
                        animation: spin 1s linear infinite;
                        margin: 20px auto;
                    }
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                `;
                document.head.appendChild(style);
            }
            
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 15000);
                
                const rutasPosibles = [
                    "../../SQL/certificado.php",
                    "../SQL/certificado.php",
                    "../../SQL/certificado.php"
                ];
                
                let response = null;
                let rutaCorrecta = null;
                let lastError = null;
                
                for (const ruta of rutasPosibles) {
                    try {
                        response = await fetch(ruta, {
                            method: "POST",
                            headers: { 
                                "Content-Type": "application/x-www-form-urlencoded" 
                            },
                            body: "action=validar&doc=" + encodeURIComponent(numDoc),
                            signal: controller.signal
                        });
                        
                        if (response.ok) {
                            rutaCorrecta = ruta;
                            break;
                        }
                    } catch (e) {
                        lastError = e;
                        continue;
                    }
                }
                
                clearTimeout(timeoutId);
                
                if (!response || !rutaCorrecta) {
                    throw new Error(lastError?.message || "No se pudo conectar con el servidor");
                }
                
                if (response.status === 429) {
                    throw new Error("Demasiadas solicitudes. Intente en unos minutos.");
                }
                
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    console.error("Respuesta no JSON:", text);
                    throw new Error("Error del servidor. Intente más tarde.");
                }
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Certificado disponible",
                        html: `
                            <div style="text-align: center;">
                                <p>Señor(a) <strong>${result.nombre} ${result.apellido}</strong>,</p>
                                <p>Su certificado está disponible para descarga.</p>
                                <br>
                                <button onclick="descargarCertificado('${numDoc}', '${rutaCorrecta}')" 
                                        class="btn-descargar"
                                        style="
                                            padding: 12px 24px; 
                                            border: none; 
                                            border-radius: 8px; 
                                            background: linear-gradient(45deg, #28a745, #20c997);
                                            color: white; 
                                            cursor: pointer;
                                            font-size: 16px;
                                            font-weight: bold;
                                            transition: transform 0.2s;
                                        "
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                    📄 Descargar Certificado
                                </button>
                            </div>
                        `,
                        showConfirmButton: false,
                        showCloseButton: true,
                        width: 450
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "No registrado",
                        text: result.message || "Usted no se encuentra registrado en el evento.",
                        confirmButtonColor: "#e74c3c"
                    });
                }
                
            } catch (error) {
                console.error("Error:", error);
                
                let mensaje = "No se pudo validar el certificado.";
                if (error.name === 'AbortError') {
                    mensaje = "La solicitud tardó demasiado. Intente nuevamente.";
                } else if (error.message.includes("429") || error.message.includes("muchas solicitudes")) {
                    mensaje = "Demasiadas solicitudes. Por favor espere unos minutos.";
                }
                
                Swal.fire({
                    icon: "error",
                    title: "Error de conexión",
                    text: mensaje,
                    confirmButtonColor: "#e74c3c"
                });
            } finally {
                requestInProgress = false;
            }
        });
    }
});

async function descargarCertificado(numeroDoc, rutaBase) {
    if (requestInProgress) {
        Swal.fire({
            icon: "warning",
            title: "Descarga en progreso",
            text: "Por favor espere a que termine la descarga actual.",
            confirmButtonColor: "#f39c12"
        });
        return;
    }
    
    const result = await Swal.fire({
        title: '¿Descargar certificado?',
        html: `
            <div style="text-align: center; padding: 20px;">
                <p style="font-size: 16px; margin-bottom: 20px;">
                    ¿Está seguro que desea descargar su certificado?
                </p>
                <div style="font-size: 48px; margin: 20px 0;">
                    📜
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '✅ Sí, descargar',
        cancelButtonText: '❌ Cancelar',
        reverseButtons: true
    });
    
    if (!result.isConfirmed) {
        return;
    }
    
    requestInProgress = true;
    
    Swal.fire({
        title: 'Generando certificado...',
        html: `
            <div class="spinner"></div>
            <p>Generando PDF, por favor espere...</p>
            <small>Este proceso puede tomar unos segundos</small>
        `,
        allowEscapeKey: false,
        allowOutsideClick: false,
        showConfirmButton: false
    });
    
    const url = `${rutaBase}?action=generar&doc=${encodeURIComponent(numeroDoc)}&confirmed=yes`;
    
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.style.width = '0px';
    iframe.style.height = '0px';
    document.body.appendChild(iframe);
    
    let downloadSuccess = false;
    
    let timeoutId = setTimeout(() => {
        downloadSuccess = true;
        Swal.close();
        mostrarConfetti();
        
        Swal.fire({
            icon: "success",
            title: "¡Certificado descargado!",
            html: `
                <div style="text-align: center;">
                    <p style="font-size: 18px; margin: 20px 0;">🎉 ¡Felicidades! 🎉</p>
                    <p>El certificado se ha descargado correctamente.</p>
                    <p><small>Revise su carpeta de descargas</small></p>
                </div>
            `,
            timer: 5000,
            timerProgressBar: true,
            showConfirmButton: true,
            confirmButtonText: 'Cerrar'
        });
        
        cleanupDownload();
    }, 3000);
    
    function cleanupDownload() {
        if (iframe.parentNode) {
            document.body.removeChild(iframe);
        }
        requestInProgress = false;
        clearTimeout(timeoutId);
    }
    
    iframe.onload = function() {
        try {
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            if (iframeDoc && iframeDoc.body) {
                const content = iframeDoc.body.textContent || iframeDoc.body.innerText || '';
                
                if (content && (content.includes('Error') || content.includes('Demasiadas solicitudes'))) {
                    clearTimeout(timeoutId);
                    
                    let mensaje = "Hubo un problema al generar el PDF.";
                    if (content.includes('Demasiadas solicitudes')) {
                        mensaje = "Demasiadas solicitudes. Por favor espere unos minutos e intente nuevamente.";
                    }
                    
                    Swal.fire({
                        icon: "error",
                        title: "Error al generar certificado",
                        text: mensaje,
                        confirmButtonColor: "#e74c3c"
                    });
                    
                    cleanupDownload();
                }
            }
        } catch (e) {
            console.log("Descarga probablemente exitosa");
        }
    };
    
    iframe.onerror = function() {
        clearTimeout(timeoutId);
        Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo generar el certificado. Verifique su conexión e intente nuevamente.",
            confirmButtonColor: "#e74c3c"
        });
        cleanupDownload();
    };
    
    iframe.src = url;
}

function mostrarConfetti() {
    const duration = 3 * 1000;
    const animationEnd = Date.now() + duration;
    const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 10000 };

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    const interval = setInterval(function() {
        const timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            return clearInterval(interval);
        }

        const particleCount = 50 * (timeLeft / duration);
        
        confetti(Object.assign({}, defaults, { 
            particleCount, 
            origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } 
        }));
        confetti(Object.assign({}, defaults, { 
            particleCount, 
            origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } 
        }));
    }, 250);
}

document.addEventListener("DOMContentLoaded", function () {
    const docInput = document.getElementById("numeroDocumento");
    
    if (docInput) {
        let validationTimeout;
        
        docInput.addEventListener("input", function () {
            clearTimeout(validationTimeout);
            
            validationTimeout = setTimeout(() => {
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length > 10) {
                    this.value = this.value.slice(0, 10);
                }
                
                const isValid = this.value.length >= 6 && this.value.length <= 10;
                this.style.borderColor = this.value.length === 0 ? '' : (isValid ? '#28a745' : '#dc3545');
            }, 300);
        });
        
        docInput.addEventListener("focus", function() {
            if (this.value.length === 0) {
                this.style.borderColor = '';
            }
        });
    }
});
