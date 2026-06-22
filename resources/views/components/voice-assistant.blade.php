<div class="voice-assistant-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999999;">
    <button id="voiceBtn" type="button" style="width: 65px; height: 65px; border-radius: 50%; background: linear-gradient(135deg, #0a2b5e 0%, #1a4a8a 100%); border: 2px solid #ffffff; box-shadow: 0px 4px 15px rgba(0,0,0,0.4); cursor: pointer; display: flex; align-items: center; justify-content: center; outline: none; padding: 0;">
        <svg viewBox="0 0 24 24" style="width: 28px; height: 28px; fill: #ffffff;" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/>
        </svg>
    </button>
</div>

<div id="voiceModal" class="modal fade" tabindex="-1" aria-hidden="true" style="z-index: 99999999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white" style="background-color: #0a2b5e;">
                <h5 class="modal-title">🎙️ Asistente por Voz - Condominio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mic-icon mb-3" id="micIconContainer" style="cursor: pointer; display: inline-block;">
                    <svg id="micIconSvg" viewBox="0 0 24 24" style="width: 80px; height: 80px; fill: #0a2b5e;" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/>
                    </svg>
                </div>
                <p id="voiceStatus" class="text-muted" style="font-weight: 500;">Haz clic en el micrófono y habla</p>
                <div id="voiceResult" class="mt-3"></div>
                <div class="alert alert-info mt-3" style="font-size: 0.85rem; text-align: left;">
                    <strong>🎯 Comandos soportados:</strong><br>
                    <span style="display:inline-block; margin-top:4px;">"ver panel", "mis cuotas", "ver multas", "registrar pago", "ver reservas", "ver visitas", "ver reclamos", "ver notificaciones", "ver bitácora".</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes voicePulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.15); fill: #dc3545; }
        100% { transform: scale(1); opacity: 1; }
    }
    .pulse-animation-svg {
        animation: voicePulse 1.5s infinite;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const voiceBtn = document.getElementById('voiceBtn');
    const voiceModalEl = document.getElementById('voiceModal');
    const voiceResult = document.getElementById('voiceResult');
    const voiceStatus = document.getElementById('voiceStatus');
    const micIconContainer = document.getElementById('micIconContainer');
    const micIconSvg = document.getElementById('micIconSvg');

    let voiceModal = null;
    let recognition = null;
    let isListening = false;

    function initModal() {
        if (typeof bootstrap !== 'undefined') {
            voiceModal = new bootstrap.Modal(voiceModalEl);
        } else {
            setTimeout(initModal, 200);
        }
    }
    initModal();

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        voiceBtn.disabled = true;
        voiceBtn.style.opacity = '0.6';
        voiceBtn.title = "Tu navegador no soporta reconocimiento de voz";
        return;
    }

    recognition = new SpeechRecognition();
    recognition.lang = 'es-ES';
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = function() {
        isListening = true;
        voiceStatus.textContent = "🎤 Escuchando... habla ahora";
        micIconSvg.classList.add('pulse-animation-svg');
    };

    recognition.onend = function() {
        isListening = false;
        micIconSvg.classList.remove('pulse-animation-svg');
    };

    recognition.onresult = function(event) {
        const command = event.results[0][0].transcript.toLowerCase();
        voiceResult.innerHTML = `<p><strong>Dijiste:</strong> "${command}"</p><div class="spinner-border text-success" role="status"></div><p>Procesando...</p>`;

        fetch('{{ route("voice-command") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ command: command })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                voiceResult.innerHTML = `<div class="alert alert-success">✅ ${data.message}</div>`;
                setTimeout(() => {
                    if (voiceModal) { voiceModal.hide(); }
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }, 1300);
            } else {
                voiceResult.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
                voiceStatus.textContent = "Haz clic en el micrófono para intentar de nuevo";
            }
        })
        .catch(() => {
            voiceResult.innerHTML = '<div class="alert alert-danger">Error al procesar el comando</div>';
            voiceStatus.textContent = "Haz clic en el micrófono para intentar de nuevo";
        });
    };

    recognition.onerror = function(event) {
        let errorMsg = "Error al reconocer la voz";
        switch (event.error) {
            case 'not-allowed': errorMsg = "❌ Permiso denegado. Activa el micrófono."; break;
            case 'no-speech': errorMsg = "No se detectó voz. Intenta nuevamente."; break;
            case 'network': errorMsg = "Error de red. Verifica tu conexión."; break;
            default: errorMsg = `Error: ${event.error}`;
        }
        voiceResult.innerHTML = `<div class="alert alert-warning">${errorMsg}</div>`;
        voiceStatus.textContent = "Haz clic en el micrófono para intentar de nuevo";
    };

    voiceBtn.addEventListener('click', function() {
        if (voiceModal) { voiceModal.show(); }
        voiceResult.innerHTML = '';
        voiceStatus.textContent = "Iniciando...";
    });

    voiceModalEl.addEventListener('shown.bs.modal', function() {
        voiceStatus.textContent = "Solicitando micrófono...";
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(function(stream) {
                stream.getTracks().forEach(track => track.stop());
                if (!isListening) {
                    try { recognition.start(); } catch (e) {}
                }
            })
            .catch(function() {
                voiceStatus.textContent = "❌ Permite el acceso al micrófono.";
            });
    });

    voiceModalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (voiceModal) { voiceModal.hide(); }
            if (isListening && recognition) {
                try { recognition.stop(); } catch (e) {}
            }
        });
    });

    micIconContainer.addEventListener('click', function() {
        if (!isListening) {
            voiceResult.innerHTML = '';
            try { recognition.start(); } catch (e) {}
        }
    });
});
</script>
