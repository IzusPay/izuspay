@extends('layouts.app')

@section('title', 'Leitor de QR Code - Associação')

@section('content')
<div class="container mx-auto p-4 lg:p-8 text-slate-800 dark:text-slate-200">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Leitor de QR Code</h2>
            <div class="flex gap-2">
                <button id="start-btn" class="px-3 py-2 bg-slate-900 dark:bg-white text-white dark:text-black rounded-lg text-sm font-semibold">Iniciar câmera</button>
                <button id="stop-btn" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-lg text-sm font-semibold">Parar</button>
                <button id="force-scan-btn" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-lg text-sm font-semibold">Forçar leitura</button>
            </div>
        </div>

        <div class="bg-white dark:bg-black p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="relative aspect-video bg-black/90 rounded-lg overflow-hidden">
                <video id="qr-video" class="w-full h-full object-cover" muted playsinline></video>
                <div id="qr-fallback" class="hidden w-full h-full"></div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <select id="camera-select" class="px-2 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-lg text-sm"></select>
                <p id="status" class="text-sm text-slate-500">Aguardando iniciar a câmera…</p>
                <button id="torch-btn" class="px-2 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-lg text-xs">Luz</button>
                <input id="zoom-range" type="range" min="1" max="5" step="0.1" class="w-24">
            </div>
        </div>

        <div id="result-card" class="mt-4 hidden bg-white dark:bg-black p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-sm">
            <div class="flex items-center gap-2 mb-2 text-emerald-600 dark:text-emerald-400">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span class="text-sm font-medium">Ingresso validado</span>
            </div>
            <div id="result-content" class="text-sm text-slate-700 dark:text-slate-300"></div>
        </div>
        <div class="mt-4 bg-white dark:bg-black p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <input id="file-input" type="file" accept="image/*" class="text-sm">
                <button id="scan-file-btn" class="px-3 py-2 bg-slate-900 dark:bg-white text-white dark:text-black rounded-lg text-sm font-semibold">Ler imagem</button>
            </div>
            <div class="flex items-center gap-2">
                <input id="manual-input" type="text" placeholder="Código do ingresso" class="flex-1 px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-lg text-sm">
                <button id="manual-validate-btn" class="px-3 py-2 bg-slate-900 dark:bg-white text-white dark:text-black rounded-lg text-sm font-semibold">Validar código</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>
<script>
    const videoElem = document.getElementById('qr-video');
    const startBtn = document.getElementById('start-btn');
    const stopBtn = document.getElementById('stop-btn');
    const forceScanBtn = document.getElementById('force-scan-btn');
    const statusElem = document.getElementById('status');
    const resultCard = document.getElementById('result-card');
    const resultContent = document.getElementById('result-content');
    const csrfToken = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
    const cameraSelect = document.getElementById('camera-select');
    const fileInput = document.getElementById('file-input');
    const scanFileBtn = document.getElementById('scan-file-btn');
    const manualInput = document.getElementById('manual-input');
    const manualValidateBtn = document.getElementById('manual-validate-btn');
    const torchBtn = document.getElementById('torch-btn');
    const zoomRange = document.getElementById('zoom-range');

    let scanner = null;
    let lastToken = null;
    let cooldown = false;
    let currentDeviceId = null;
    let activeTrack = null;
    let torchOn = false;
    let trackCaps = {};
    let autoScanTimer = null;
    let lastDebugTs = 0;
    if (window.QrScanner) {
        QrScanner.WORKER_PATH = 'https://unpkg.com/qr-scanner@1.4.2/qr-scanner-worker.min.js';
    }

    async function validateToken(token) {
        try {
            const res = await fetch('{{ route('associacao.qr-reader.validate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ token }),
            });
            const data = await res.json();

            if (data && data.success) {
                const eventName = (data && data.data && data.data.event) ? data.data.event : '—';
                const typeName = (data && data.data && data.data.type) ? data.data.type : '—';
                const ownerName = (data && data.data && data.data.owner) ? data.data.owner : '—';
                resultContent.textContent = `Evento: ${eventName} • Tipo: ${typeName} • Cliente: ${ownerName}`;
                resultCard.classList.remove('hidden');
                if (window.showNotification) window.showNotification('Ingresso validado com sucesso', 'success');
                statusElem.textContent = 'Pronto para novo QR';
            } else {
                resultCard.classList.add('hidden');
                const msg = (data && data.message) ? data.message : 'Falha ao validar ingresso';
                if (window.showNotification) window.showNotification(msg, 'error');
                statusElem.textContent = msg;
            }
        } catch (e) {
            resultCard.classList.add('hidden');
            if (window.showNotification) window.showNotification('Erro de rede ao validar', 'error');
            statusElem.textContent = 'Erro de rede ao validar';
        }
    }

    function extractToken(input) {
        const s = String(input || '').trim();
        const uuidMatch = s.match(/[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}/i);
        if (uuidMatch) return uuidMatch[0];
        const tokenParam = s.match(/[?&]token=([0-9a-f-]{36})/i);
        if (tokenParam) return tokenParam[1];
        const pathToken = s.match(/\/(ingresso|ticket|tickets)\/([A-Za-z0-9-_]{8,64})/i);
        if (pathToken) return pathToken[2];
        const parts = s.split('/');
        const last = parts[parts.length - 1] || '';
        if (/[0-9a-f-]{36}/i.test(last)) return last;
        if (/^[A-Za-z0-9-_]{8,64}$/.test(last)) return last;
        const anyAlnum = s.match(/[A-Za-z0-9-_]{8,64}/);
        if (anyAlnum) return anyAlnum[0];
        return s;
    }

    function onScan(result) {
        if (!result) return;
        const value = typeof result === 'object' && result.data ? result.data : result;
        let token = extractToken(value);

        statusElem.textContent = `Decodificado: ${String(value).slice(0, 120)} • Token: ${token}`;

        // Evita repetir o mesmo token em sequência
        if (cooldown || (lastToken && lastToken === token)) return;
        lastToken = token;
        cooldown = true;

        statusElem.textContent = 'Validando ingresso…';
        validateToken(token).finally(() => {
            setTimeout(() => { cooldown = false; }, 1500);
        });
    }

    async function populateCameras() {
        try {
            const cameras = await QrScanner.listCameras(true);
            cameraSelect.innerHTML = '';
            cameras.forEach(cam => {
                const opt = document.createElement('option');
                opt.value = cam.id;
                opt.textContent = cam.label || cam.id || 'Câmera';
                cameraSelect.appendChild(opt);
            });
            if (cameras.length > 0) {
                const preferredCam = cameras.find(c => /back|rear|environment|traseir|traseira|trás/i.test(c.label || '')) || cameras[0];
                currentDeviceId = preferredCam ? preferredCam.id : cameras[0].id;
                cameraSelect.value = currentDeviceId;
            }
        } catch (e) {}
    }

    async function startScanner() {
        try {
            if (!window.isSecureContext) {
                statusElem.textContent = 'Requer conexão segura (https ou localhost)';
                if (window.showNotification) window.showNotification('Requer https ou localhost para usar a câmera', 'error');
                return;
            }
            if (!window.QrScanner) {
                const msg = 'Biblioteca de leitura de QR não carregou';
                statusElem.textContent = msg;
                if (window.showNotification) window.showNotification(msg, 'error');
                return;
            }
            QrScanner.WORKER_PATH = 'https://unpkg.com/qr-scanner@1.4.2/qr-scanner-worker.min.js';
            if (scanner) await scanner.stop();
            const options = currentDeviceId
                ? {
                    deviceId: currentDeviceId,
                    onDecodeError: () => {
                        const now = Date.now();
                        if (now - lastDebugTs > 1000) {
                            statusElem.textContent = 'Detectando...';
                            lastDebugTs = now;
                        }
                    },
                    highlightScanRegion: false,
                    highlightCodeOutline: false,
                    returnDetailedScanResult: true,
                    maxScansPerSecond: 60,
                }
                : {
                    preferredCamera: 'environment',
                    onDecodeError: () => {
                        const now = Date.now();
                        if (now - lastDebugTs > 1000) {
                            statusElem.textContent = 'Detectando...';
                            lastDebugTs = now;
                        }
                    },
                    highlightScanRegion: false,
                    highlightCodeOutline: false,
                    returnDetailedScanResult: true,
                    maxScansPerSecond: 60,
                };
            scanner = new QrScanner(videoElem, onScan, options);
            if (scanner.setInversionMode) scanner.setInversionMode('both');
            await populateCameras();
            await scanner.start();
            activeTrack = videoElem.srcObject && videoElem.srcObject.getVideoTracks ? videoElem.srcObject.getVideoTracks()[0] : null;
            trackCaps = activeTrack && activeTrack.getCapabilities ? activeTrack.getCapabilities() : {};
            try {
                const adv = [];
                const caps = activeTrack && activeTrack.getCapabilities ? activeTrack.getCapabilities() : {};
                if (caps.focusMode && Array.isArray(caps.focusMode) && caps.focusMode.includes('continuous')) {
                    adv.push({ focusMode: 'continuous' });
                }
                if (caps.exposureMode && Array.isArray(caps.exposureMode) && caps.exposureMode.includes('continuous')) {
                    adv.push({ exposureMode: 'continuous' });
                }
                if (adv.length > 0) {
                    await activeTrack.applyConstraints({ advanced: adv });
                }
                const z = caps.zoom;
                if (z && (typeof z.min === 'number') && (typeof z.max === 'number')) {
                    zoomRange.min = String(z.min);
                    zoomRange.max = String(z.max);
                }
            } catch (e) {}
            statusElem.textContent = 'Câmera iniciada. Aponte para o QR do ingresso.';
            if (autoScanTimer) { clearInterval(autoScanTimer); autoScanTimer = null; }
            autoScanTimer = setInterval(async () => {
                try {
                    if (!videoElem || cooldown) return;
                    const r = await QrScanner.scanImage(videoElem, { returnDetailedScanResult: true, alsoTryWithoutScanRegion: true });
                    const v = r && r.data ? r.data : r;
                    onScan(v);
                } catch (e) { /* silencioso */ }
            }, 2000);
        } catch (e) {
            const msg = e && e.message ? e.message : 'Não foi possível iniciar a câmera';
            statusElem.textContent = msg;
            if (window.showNotification) window.showNotification(msg, 'error');
        }
    }

    async function stopScanner() {
        try {
            if (scanner) await scanner.stop();
            statusElem.textContent = 'Câmera parada.';
        } catch (e) {
            // silencioso
        }
        if (autoScanTimer) { clearInterval(autoScanTimer); autoScanTimer = null; }
    }

    startBtn.addEventListener('click', startScanner);
    stopBtn.addEventListener('click', stopScanner);
    cameraSelect.addEventListener('change', async (e) => {
        currentDeviceId = e.target.value || null;
        await startScanner();
    });
    forceScanBtn.addEventListener('click', async () => {
        try {
            statusElem.textContent = 'Forçando leitura…';
            const r = await QrScanner.scanImage(videoElem, { returnDetailedScanResult: true, alsoTryWithoutScanRegion: true });
            const v = r && r.data ? r.data : r;
            onScan(v);
        } catch (e) {
            statusElem.textContent = 'Não foi possível ler no quadro atual';
            if (window.showNotification) window.showNotification('Não foi possível ler no quadro atual', 'error');
        }
    });
    torchBtn.addEventListener('click', async () => {
        try {
            if (!activeTrack) return;
            const sup = activeTrack.getCapabilities && activeTrack.getCapabilities().torch;
            if (!sup) {
                statusElem.textContent = 'Luz não suportada';
                return;
            }
            torchOn = !torchOn;
            await activeTrack.applyConstraints({ advanced: [{ torch: torchOn }] });
            statusElem.textContent = torchOn ? 'Luz ativa' : 'Luz desligada';
        } catch (e) {
            statusElem.textContent = 'Falha ao alternar luz';
        }
    });
    zoomRange.addEventListener('input', async (e) => {
        try {
            if (!activeTrack) return;
            const capZoom = trackCaps.zoom;
            if (!capZoom) return;
            let val = parseFloat(e.target.value);
            const min = capZoom.min ?? 1;
            const max = capZoom.max ?? 5;
            if (val < min) val = min;
            if (val > max) val = max;
            await activeTrack.applyConstraints({ advanced: [{ zoom: val }] });
            statusElem.textContent = `Zoom: ${val}`;
        } catch (e) {}
    });
    scanFileBtn.addEventListener('click', async () => {
        const file = fileInput.files && fileInput.files[0];
        if (!file) {
            statusElem.textContent = 'Selecione uma imagem';
            return;
        }
        try {
            statusElem.textContent = 'Lendo imagem…';
            const result = await QrScanner.scanImage(file, { returnDetailedScanResult: true, alsoTryWithoutScanRegion: true });
            const value = result && result.data ? result.data : result;
            onScan(value);
        } catch (e) {
            const msg = 'QR não detectado na imagem';
            statusElem.textContent = msg;
            if (window.showNotification) window.showNotification(msg, 'error');
        }
    });
    manualValidateBtn.addEventListener('click', () => {
        const token = String(manualInput.value || '').trim();
        if (!token) {
            statusElem.textContent = 'Informe o código';
            return;
        }
        statusElem.textContent = 'Validando ingresso…';
        validateToken(token);
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush
