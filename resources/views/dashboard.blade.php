<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitoring Kumbung Jamur Tiram - IoT Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Chart.js & Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0B1120;
            color: #F1F5F9;
        }
        
        .card-panel {
            background: #151F32;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .spin-active {
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .mist-active {
            animation: pulse-mist 1.5s ease-in-out infinite;
        }
        @keyframes pulse-mist {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 1; }
        }
    </style>
</head>
<body class="min-h-screen pb-12 antialiased">

    <!-- Header Navigation -->
    <header class="border-b border-slate-800 bg-slate-900/90 sticky top-0 z-40 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Title -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <i data-lucide="sprout" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-white leading-tight">
                            Monitoring Kumbung Jamur Tiram
                        </h1>
                        <p class="text-xs text-slate-400">Dashboard Kontrol & Monitoring IoT</p>
                    </div>
                </div>

                <!-- Right Info -->
                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-2 bg-slate-800 px-3 py-1.5 rounded-full text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Firebase Sync: <strong class="text-emerald-400" id="syncStatus">Terhubung</strong></span>
                    </div>
                    <span id="headerClock" class="font-mono text-slate-400 hidden sm:inline">--:--:--</span>
                </div>

            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 space-y-6">

        <!-- 1. STATUS KONDISI KUMBUNG -->
        <div id="statusAlertBox" class="card-panel rounded-2xl p-5 border-l-4 border-l-emerald-500 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div id="statusIconBox" class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0">
                    <i id="statusIcon" data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase text-slate-400">Status Kondisi</span>
                        <span id="statusBadge" class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-500/20 text-emerald-300">
                            IDEAL
                        </span>
                    </div>
                    <p id="statusMessage" class="text-sm font-semibold text-slate-100 mt-0.5">
                        Suhu dan kelembapan dalam rentang optimal (Suhu: 22-28°C, Kelembapan: 80-90%).
                    </p>
                </div>
            </div>

            <div class="text-xs text-slate-400 shrink-0">
                Live Monitoring: <span id="lastUpdated" class="font-mono text-emerald-400 font-bold">Baru saja</span>
            </div>
        </div>

        <!-- 2. MONITORING SUHU, KELEMBAPAN & AKTUATOR (4 Grid Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- Card 1: Monitoring Suhu -->
            <div class="card-panel rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span class="font-semibold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="thermometer" class="w-4 h-4 text-rose-400"></i> Suhu
                    </span>
                    <span id="tempBadge" class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-bold">
                        NORMAL
                    </span>
                </div>

                <div class="flex items-baseline justify-between">
                    <div>
                        <span id="valTemp" class="text-3xl font-extrabold text-white font-mono">26.5</span>
                        <span class="text-sm font-medium text-slate-400">°C</span>
                    </div>
                    <span class="text-xs text-slate-400">Target: <strong class="text-slate-200">22-28°C</strong></span>
                </div>

                <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                    <div id="barTemp" class="bg-rose-500 h-full transition-all duration-500" style="width: 46%;"></div>
                </div>
            </div>

            <!-- Card 2: Monitoring Kelembapan -->
            <div class="card-panel rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span class="font-semibold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="droplets" class="w-4 h-4 text-cyan-400"></i> Kelembapan
                    </span>
                    <span id="humBadge" class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-bold">
                        OPTIMAL
                    </span>
                </div>

                <div class="flex items-baseline justify-between">
                    <div>
                        <span id="valHum" class="text-3xl font-extrabold text-white font-mono">85.0</span>
                        <span class="text-sm font-medium text-slate-400">% RH</span>
                    </div>
                    <span class="text-xs text-slate-400">Target: <strong class="text-slate-200">80-90%</strong></span>
                </div>

                <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                    <div id="barHum" class="bg-cyan-500 h-full transition-all duration-500" style="width: 78%;"></div>
                </div>
            </div>

            <!-- Card 3: Status Aktuator Kipas -->
            <div class="card-panel rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span class="font-semibold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="fan" id="fanIcon" class="w-4 h-4 text-slate-400"></i> Kipas Pendingin
                    </span>
                    <span id="fanStatusBadge" class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-bold">
                        MATI
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <div id="fanText" class="text-lg font-bold text-white">OFF</div>
                        <p class="text-xs text-slate-400">Aktif jika Suhu > 28°C</p>
                    </div>

                    <label id="fanLabel" class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="toggleFan" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>
            </div>

            <!-- Card 4: Status Aktuator Humidifier -->
            <div class="card-panel rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span class="font-semibold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="cloud-rain" id="humIcon" class="w-4 h-4 text-slate-400"></i> Humidifier / Pompa
                    </span>
                    <span id="humidifierStatusBadge" class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-bold">
                        MATI
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <div id="humText" class="text-lg font-bold text-white">OFF</div>
                        <p class="text-xs text-slate-400">Aktif jika Kelembapan < 80%</p>
                    </div>

                    <label id="humLabel" class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="toggleHumidifier" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>
            </div>

        </div>

        <!-- MODE SELECTION -->
        <div class="card-panel rounded-2xl p-4 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <i data-lucide="cpu" class="w-4 h-4 text-emerald-400"></i>
                <span class="font-semibold text-slate-300">Mode Kontrol Aktuator:</span>
                <span id="modeBadgeText" class="text-slate-400 font-medium ml-2">AUTO (Otomatis Sensor)</span>
            </div>
            <div class="flex items-center gap-2 bg-slate-900 p-1 rounded-xl">
                <button id="btnAuto" class="px-4 py-1.5 rounded-lg font-bold bg-emerald-500 text-white transition-all cursor-pointer">
                    AUTO
                </button>
                <button id="btnManual" class="px-4 py-1.5 rounded-lg font-bold text-slate-400 hover:text-white transition-all cursor-pointer">
                    MANUAL
                </button>
            </div>
        </div>

        <!-- 3. GRAFIK HISTORI -->
        <div class="card-panel rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="line-chart" class="w-5 h-5 text-emerald-400"></i> Grafik Histori Suhu & Kelembapan
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Riwayat pembacaan sensor secara real-time</p>
                </div>
                <div class="text-xs text-slate-400 bg-slate-900 px-3 py-1.5 rounded-lg flex gap-4">
                    <span>Rata-rata Suhu: <strong id="avgTempText" class="text-rose-400">--</strong></span>
                    <span>Rata-rata Kelembapan: <strong id="avgHumText" class="text-cyan-400">--</strong></span>
                </div>
            </div>

            <div class="relative w-full h-72">
                <canvas id="historyChart"></canvas>
            </div>
        </div>

        <!-- 4. PANEL SIMULASI (Pengujian Nilai 100) -->
        <details class="card-panel rounded-2xl p-5 group">
            <summary class="cursor-pointer font-bold text-sm text-slate-300 flex items-center justify-between list-none">
                <span class="flex items-center gap-2">
                    <i data-lucide="sliders" class="w-4 h-4 text-teal-400"></i>
                    Panel Simulasi Sensor (Pengujian & Demo Live)
                </span>
                <span class="text-xs text-teal-400 font-normal group-open:hidden">+ Buka Panel Simulasi</span>
                <span class="text-xs text-slate-500 font-normal hidden group-open:inline">- Tutup Panel</span>
            </summary>

            <div class="mt-4 pt-4 border-t border-slate-800 space-y-4 text-xs">
                <!-- Stream Live Sensor Toggle -->
                <div class="flex items-center justify-between bg-slate-900/80 p-3 rounded-xl border border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <span class="font-bold text-white">Simulasi Fluktuasi Sensor Live (Demo Tanpa Wokwi):</span>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-emerald-400">
                        <input type="checkbox" id="autoStreamToggle" onchange="toggleAutoStream(this.checked)" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                        <span id="autoStreamStatusText">Aktifkan Live Simulation</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Simulasi Suhu:</span>
                            <strong id="simTempVal" class="text-rose-400 font-mono">26.5°C</strong>
                        </div>
                        <input type="range" id="simTempInput" min="15" max="40" step="0.5" value="26.5" 
                               oninput="document.getElementById('simTempVal').innerText = this.value + '°C'" 
                               class="w-full h-1.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-rose-500">
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Simulasi Kelembapan:</span>
                            <strong id="simHumVal" class="text-cyan-400 font-mono">85.0%</strong>
                        </div>
                        <input type="range" id="simHumInput" min="40" max="100" step="1" value="85" 
                               oninput="document.getElementById('simHumVal').innerText = this.value + '%'" 
                               class="w-full h-1.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-cyan-500">
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-2">
                    <button onclick="setSimPreset(26.5, 85)" class="px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20">
                        🟩 Ideal (26.5°C / 85%)
                    </button>
                    <button onclick="setSimPreset(32.0, 65)" class="px-3 py-1 rounded-lg bg-amber-500/10 text-amber-300 hover:bg-amber-500/20">
                        🟧 Panas & Kering (32°C / 65%)
                    </button>
                    <button onclick="setSimPreset(31.0, 85)" class="px-3 py-1 rounded-lg bg-rose-500/10 text-rose-300 hover:bg-rose-500/20">
                        🟥 Panas Saja (31°C / 85%)
                    </button>
                    <button onclick="setSimPreset(25.0, 70)" class="px-3 py-1 rounded-lg bg-cyan-500/10 text-cyan-300 hover:bg-cyan-500/20">
                        🟦 Kering Saja (25°C / 70%)
                    </button>
                </div>

                <div class="flex justify-end pt-2">
                    <button onclick="submitSimulation()" class="px-4 py-2 rounded-xl bg-teal-500 text-slate-950 font-bold hover:bg-teal-400 transition-all">
                        Kirim Simulasi ke System
                    </button>
                </div>
            </div>
        </details>

    </main>

    <!-- Script Logic (Firebase Realtime Database + Chart.js) -->
    <!-- Firebase Compat SDK (global) — pakai script biasa agar tidak ada timing issue module -->
    <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-database-compat.js"></script>
    <script>

        // ── Firebase Config (dibaca otomatis dari .env via Controller) 
        const firebaseConfig = @json($firebaseConfig);

        try {
            firebase.initializeApp(firebaseConfig);
        } catch(e) {
            console.warn("Firebase init error:", e);
        }
        const db = firebase.database();

        // ── State Lokal ───────────────────────────────────────────────
        let envData = @json($initialData);
        let historyChart = null;
        let autoStreamInterval = null;
        let isWriting = false; // flag: suppress Firebase listener during local writes

        // ── Inisialisasi Halaman (script posisi di akhir body, DOM sudah ada) ─
        lucide.createIcons();
        updateClock();
        setInterval(updateClock, 1000);
        initChart();
        renderUI(envData);
        startFirebaseListeners();

        // ── Event Listeners untuk Toggle Aktuator ──────────────────
        document.getElementById('toggleFan').addEventListener('change', function() {
            toggleActuator('fan', this.checked);
        });
        document.getElementById('toggleHumidifier').addEventListener('change', function() {
            toggleActuator('humidifier', this.checked);
        });
        document.getElementById('btnAuto').addEventListener('click', function() {
            switchMode('AUTO');
        });
        document.getElementById('btnManual').addEventListener('click', function() {
            switchMode('MANUAL');
        });

        function updateClock() {
            document.getElementById('headerClock').innerText = new Date().toLocaleTimeString('id-ID');
        }

        // ── Firebase Realtime Listeners ───────────────────────────────
        function startFirebaseListeners() {
            // 0. Listener untuk node /records (langsung dari Wokwi ESP32)
            db.ref('records').limitToLast(20).on('value', (snap) => {
                const raw = snap.val();
                if (raw) {
                    const validRecords = {};
                    let latestRecord = null;
                    let latestTs = 0;

                    Object.entries(raw).forEach(([k, v]) => {
                        const ts = v.timestamp || 0;
                        if (ts > 1000000000) {
                            validRecords[k] = v;
                            if (ts > latestTs) {
                                latestTs = ts;
                                latestRecord = v;
                            }
                        }
                    });

                    if (Object.keys(validRecords).length > 0) {
                        envData.history = validRecords;
                        updateChart(validRecords);
                        updateSyncStatus(true);

                        if (latestRecord) {
                            const curMode = (envData.actuators?.mode || 'AUTO').toUpperCase();
                            const temp = parseFloat(latestRecord.temperature);
                            const hum  = parseFloat(latestRecord.humidity);

                            envData.sensor = {
                                temperature: temp,
                                humidity: hum,
                                updated_at: latestRecord.timestamp
                            };
                            updateSensorUI(envData.sensor);

                            if (curMode === 'AUTO') {
                                const fan        = !!latestRecord.fan;
                                const humidifier = !!latestRecord.humidifier;
                                envData.actuators = { fan, humidifier, mode: 'AUTO' };
                                updateActuatorUI(envData.actuators);

                                let condition = 'Ideal', message = '';
                                if (fan && humidifier) {
                                    condition = 'Bahaya: Panas & Kering';
                                    message   = `Suhu panas (${temp}°C > 28°C) & Kelembapan rendah (${hum}% < 80%). Kipas & Humidifier NYALA!`;
                                } else if (fan) {
                                    condition = 'Waspada: Suhu Tinggi';
                                    message   = `Suhu panas (${temp}°C > 28°C). Kipas NYALA Otomatis mendinginkan kumbung.`;
                                } else if (humidifier) {
                                    condition = 'Waspada: Kelembapan Rendah';
                                    message   = `Kelembapan rendah (${hum}% < 80%). Humidifier NYALA Otomatis menyemprotkan embun.`;
                                } else {
                                    condition = 'Ideal';
                                    message   = `Suhu (${temp}°C) & Kelembapan (${hum}%) optimal. Kipas & Humidifier MATI (Kondisi Stabil).`;
                                }
                                envData.status = { condition, message };
                                updateStatusUI(envData.status);
                            }
                        }
                    }
                }
            });

            // 1. Sensor (suhu & kelembapan) jika ada di mushroom_environment
            db.ref('mushroom_environment/sensor').on('value', (snap) => {
                const data = snap.val();
                if (data) {
                    envData.sensor = data;
                    updateSensorUI(data);
                    updateSyncStatus(true);
                }
            }, () => updateSyncStatus(false));

            // 2. Status kondisi lingkungan
            db.ref('mushroom_environment/status').on('value', (snap) => {
                const data = snap.val();
                if (data) {
                    envData.status = data;
                    updateStatusUI(data);
                }
            });

            // 3. Aktuator (kipas, humidifier, mode)
            db.ref('mushroom_environment/actuators').on('value', (snap) => {
                if (isWriting) return; // jangan override saat sedang menulis
                const data = snap.val();
                if (data) {
                    envData.actuators = data;
                    updateActuatorUI(data);
                }
            });

            // 4. Histori jika ada di mushroom_environment/history (hanya jika records kosong)
            db.ref('mushroom_environment/history').limitToLast(20).on('value', (snap) => {
                if (!envData.history || Object.keys(envData.history).length === 0) {
                    const history = {};
                    snap.forEach(child => { history[child.key] = child.val(); });
                    envData.history = history;
                    updateChart(history);
                }
            });
        }

        function updateSyncStatus(connected) {
            const el = document.getElementById('syncStatus');
            if (connected) {
                el.innerText = 'Firebase Terhubung';
                el.className = 'text-emerald-400 font-semibold';
                const now = new Date().toLocaleTimeString('id-ID');
                document.getElementById('lastUpdated').innerText = 'Baru saja (' + now + ')';
            } else {
                el.innerText = 'Offline';
                el.className = 'text-rose-400 font-semibold';
            }
        }

        // ── Render UI ─────────────────────────────────────────────────
        function renderUI(data) {
            if (!data) return;
            updateSensorUI(data.sensor || {});
            updateStatusUI(data.status || {});
            updateActuatorUI(data.actuators || {});
            updateChart(data.history || {});
        }

        function updateSensorUI(sensor) {
            const temp = parseFloat(sensor.temperature ?? 26.5);
            const hum  = parseFloat(sensor.humidity ?? 85.0);

            document.getElementById('valTemp').innerText = temp.toFixed(1);
            document.getElementById('barTemp').style.width = Math.min(Math.max(((temp - 15) / 25) * 100, 0), 100) + '%';

            const tempBadge = document.getElementById('tempBadge');
            if (temp >= 22 && temp <= 28) {
                tempBadge.innerText = 'NORMAL';
                tempBadge.className = 'px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-bold';
            } else if (temp > 28) {
                tempBadge.innerText = 'PANAS';
                tempBadge.className = 'px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-400 font-bold';
            } else {
                tempBadge.innerText = 'DINGIN';
                tempBadge.className = 'px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400 font-bold';
            }

            document.getElementById('valHum').innerText = hum.toFixed(1);
            document.getElementById('barHum').style.width = Math.min(Math.max(((hum - 30) / 70) * 100, 0), 100) + '%';

            const humBadge = document.getElementById('humBadge');
            if (hum >= 80 && hum <= 90) {
                humBadge.innerText = 'OPTIMAL';
                humBadge.className = 'px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-bold';
            } else if (hum < 80) {
                humBadge.innerText = 'KERING';
                humBadge.className = 'px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 font-bold';
            } else {
                humBadge.innerText = 'BASAH';
                humBadge.className = 'px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 font-bold';
            }
        }

        function updateStatusUI(status) {
            document.getElementById('statusBadge').innerText = (status.condition || 'IDEAL').toUpperCase();
            document.getElementById('statusMessage').innerText = status.message || 'Suhu dan kelembapan dalam rentang optimal.';

            const alertBox = document.getElementById('statusAlertBox');
            const cond = status.condition || '';
            if (cond.includes('Bahaya')) {
                alertBox.className = 'card-panel rounded-2xl p-5 border-l-4 border-l-rose-500 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4';
                document.getElementById('statusBadge').className = 'px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-500/20 text-rose-300';
            } else if (cond.includes('Waspada')) {
                alertBox.className = 'card-panel rounded-2xl p-5 border-l-4 border-l-amber-500 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4';
                document.getElementById('statusBadge').className = 'px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-500/20 text-amber-300';
            } else {
                alertBox.className = 'card-panel rounded-2xl p-5 border-l-4 border-l-emerald-500 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4';
                document.getElementById('statusBadge').className = 'px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-500/20 text-emerald-300';
            }
        }

        function updateActuatorUI(actuators) {
            const isFan    = !!actuators.fan;
            const isHum    = !!actuators.humidifier;
            const mode     = (actuators.mode || 'AUTO').toUpperCase();
            const isManual = mode === 'MANUAL';

            // ── Kipas ──
            const toggleFan = document.getElementById('toggleFan');
            const fanLabel  = document.getElementById('fanLabel');
            toggleFan.checked  = isFan;
            toggleFan.disabled = !isManual;  // dikunci di mode AUTO
            if (fanLabel) {
                fanLabel.style.opacity = isManual ? '1' : '0.4';
                fanLabel.style.cursor  = isManual ? 'pointer' : 'not-allowed';
                fanLabel.title = isManual ? 'Klik untuk ON/OFF Kipas' : 'Pindah ke mode MANUAL dulu';
            }
            document.getElementById('fanText').innerText = isFan ? 'ON (Mendinginkan)' : 'OFF';
            document.getElementById('fanStatusBadge').innerText   = isFan ? 'NYALA' : 'MATI';
            document.getElementById('fanStatusBadge').className   = isFan
                ? 'px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold'
                : 'px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-bold';
            document.getElementById('fanIcon').className = isFan
                ? 'w-4 h-4 text-emerald-400 spin-active'
                : 'w-4 h-4 text-slate-400';

            // ── Humidifier ──
            const toggleHum = document.getElementById('toggleHumidifier');
            const humLabel  = document.getElementById('humLabel');
            toggleHum.checked  = isHum;
            toggleHum.disabled = !isManual;  // dikunci di mode AUTO
            if (humLabel) {
                humLabel.style.opacity = isManual ? '1' : '0.4';
                humLabel.style.cursor  = isManual ? 'pointer' : 'not-allowed';
                humLabel.title = isManual ? 'Klik untuk ON/OFF Humidifier' : 'Pindah ke mode MANUAL dulu';
            }
            document.getElementById('humText').innerText = isHum ? 'ON (Menyemprot)' : 'OFF';
            document.getElementById('humidifierStatusBadge').innerText   = isHum ? 'NYALA' : 'MATI';
            document.getElementById('humidifierStatusBadge').className   = isHum
                ? 'px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold'
                : 'px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-bold';
            document.getElementById('humIcon').className = isHum
                ? 'w-4 h-4 text-emerald-400 mist-active'
                : 'w-4 h-4 text-slate-400';

            // ── Tombol Mode ──
            if (isManual) {
                document.getElementById('btnAuto').className   = 'px-4 py-1.5 rounded-lg font-bold text-slate-400 hover:text-white transition-all cursor-pointer';
                document.getElementById('btnManual').className = 'px-4 py-1.5 rounded-lg font-bold bg-amber-500 text-white transition-all cursor-pointer';
                document.getElementById('modeBadgeText').innerText = 'MANUAL (Kontrol Sakelar)';
            } else {
                document.getElementById('btnAuto').className   = 'px-4 py-1.5 rounded-lg font-bold bg-emerald-500 text-white transition-all cursor-pointer';
                document.getElementById('btnManual').className = 'px-4 py-1.5 rounded-lg font-bold text-slate-400 hover:text-white transition-all cursor-pointer';
                document.getElementById('modeBadgeText').innerText = 'AUTO (Otomatis Sensor)';
            }

            lucide.createIcons();
        }

        // ── Chart ─────────────────────────────────────────────────────
        function initChart() {
            const ctx = document.getElementById('historyChart').getContext('2d');
            historyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Suhu (°C)',
                            data: [],
                            borderColor: '#f43f5e',
                            backgroundColor: 'rgba(244, 63, 94, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            yAxisID: 'yTemp'
                        },
                        {
                            label: 'Kelembapan (%)',
                            data: [],
                            borderColor: '#06b6d4',
                            backgroundColor: 'rgba(6, 182, 212, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            yAxisID: 'yHum'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 400 },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } },
                        yTemp: { position: 'left',  ticks: { color: '#f43f5e' }, suggestedMin: 15, suggestedMax: 35 },
                        yHum:  { position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#06b6d4' }, suggestedMin: 50, suggestedMax: 100 }
                    }
                }
            });
        }

        function updateChart(historyObj) {
            if (!historyChart) return;
            const rawRecords = historyObj ? Object.values(historyObj) : [];
            const records = rawRecords.filter(r => (r.timestamp || 0) > 1000000000);
            if (records.length === 0) {
                historyChart.data.labels = [];
                historyChart.data.datasets[0].data = [];
                historyChart.data.datasets[1].data = [];
                historyChart.update();
                document.getElementById('avgTempText').innerText = '-';
                document.getElementById('avgHumText').innerText  = '-';
                return;
            }

            // Urutkan histori dari waktu terlama ke terbaru
            records.sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));

            // Utamakan konversi dari epoch timestamp ke waktu lokal browser (WIB)
            const labels = records.map(r => {
                if (r.timestamp) {
                    return new Date(r.timestamp * 1000).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                }
                return r.formatted_time || '-';
            });
            const temps  = records.map(r => r.temperature);
            const hums   = records.map(r => r.humidity);

            historyChart.data.labels = labels;
            historyChart.data.datasets[0].data = temps;
            historyChart.data.datasets[1].data = hums;
            historyChart.update();

            document.getElementById('avgTempText').innerText = (temps.reduce((a,b)=>a+b,0)/temps.length).toFixed(1) + '°C';
            document.getElementById('avgHumText').innerText  = (hums.reduce((a,b)=>a+b,0)/hums.length).toFixed(1) + '%';
        }

        // ── Kontrol Aktuator ─────────────────────────────────────────
        function switchMode(mode) {
            const upperMode = mode.toUpperCase();
            isWriting = true;

            const temp = parseFloat(envData.sensor?.temperature || 26.5);
            const hum  = parseFloat(envData.sensor?.humidity    || 85.0);

            let fan, humidifier, condition, message;

            if (upperMode === 'AUTO') {
                fan        = temp > 28.0;
                humidifier = hum  < 80.0;
                if (fan && humidifier) {
                    condition = 'Bahaya: Panas & Kering';
                    message   = `Suhu panas (${temp}°C > 28°C) & Kelembapan rendah (${hum}% < 80%). Kipas & Humidifier NYALA Otomatis!`;
                } else if (fan) {
                    condition = 'Waspada: Suhu Tinggi';
                    message   = `Suhu panas (${temp}°C > 28°C). Kipas NYALA Otomatis mendinginkan kumbung.`;
                } else if (humidifier) {
                    condition = 'Waspada: Kelembapan Rendah';
                    message   = `Kelembapan rendah (${hum}% < 80%). Humidifier NYALA Otomatis menyemprotkan embun.`;
                } else {
                    condition = 'Ideal';
                    message   = `Suhu (${temp}°C) & Kelembapan (${hum}%) optimal. Kipas & Humidifier MATI (Kondisi Stabil).`;
                }
            } else {
                fan        = !!envData.actuators?.fan;
                humidifier = !!envData.actuators?.humidifier;
                condition  = 'Manual Control';
                message    = `Mode MANUAL Aktif: Kipas ${fan ? 'NYALA' : 'MATI'}, Humidifier ${humidifier ? 'NYALA' : 'MATI'}.`;
            }

            // Update lokal & UI langsung
            envData.actuators = { fan, humidifier, mode: upperMode };
            envData.status    = { condition, message };
            updateActuatorUI(envData.actuators);
            updateStatusUI(envData.status);

            // Tulis ke Firebase
            db.ref('mushroom_environment').update({
                'actuators': { fan, humidifier, mode: upperMode },
                'status':    { condition, message }
            }).catch(e => console.warn('Firebase switchMode gagal:', e));

            setTimeout(() => { isWriting = false; }, 2000);
        }

        function toggleActuator(actuator, value) {
            // Hanya bisa diklik di mode MANUAL
            // (di mode AUTO toggle sudah disabled, tapi double-check di sini)
            if ((envData.actuators?.mode || 'AUTO').toUpperCase() !== 'MANUAL') {
                const el = document.getElementById(actuator === 'fan' ? 'toggleFan' : 'toggleHumidifier');
                if (el) el.checked = !!envData.actuators?.[actuator]; // kembalikan ke state sebelumnya
                return;
            }

            isWriting = true;
            const fan        = actuator === 'fan'        ? value : !!envData.actuators?.fan;
            const humidifier = actuator === 'humidifier' ? value : !!envData.actuators?.humidifier;
            const condition  = 'Manual Control';
            const message    = `Mode MANUAL Aktif: Kipas ${fan ? 'NYALA' : 'MATI'}, Humidifier ${humidifier ? 'NYALA' : 'MATI'}.`;

            // Update lokal & UI langsung (optimistic)
            envData.actuators = { fan, humidifier, mode: 'MANUAL' };
            envData.status    = { condition, message };
            updateActuatorUI(envData.actuators);
            updateStatusUI(envData.status);

            // Tulis ke Firebase
            db.ref('mushroom_environment').update({
                'actuators': { fan, humidifier, mode: 'MANUAL' },
                'status':    { condition, message }
            }).catch(e => console.warn('Firebase toggleActuator gagal:', e));

            setTimeout(() => { isWriting = false; }, 2000);
        }


        // ── Simulasi ─────────────────────────────────────────────────
        window.setSimPreset = function(temp, hum) {
            document.getElementById('simTempInput').value = temp;
            document.getElementById('simTempVal').innerText = temp.toFixed(1) + '°C';
            document.getElementById('simHumInput').value = hum;
            document.getElementById('simHumVal').innerText = hum.toFixed(1) + '%';
            window.submitSimulation();
        };

        window.submitSimulation = function() {
            const temp = parseFloat(document.getElementById('simTempInput').value);
            const hum  = parseFloat(document.getElementById('simHumInput').value);
            const now  = Math.floor(Date.now() / 1000);
            const mode = (envData.actuators?.mode || 'AUTO').toUpperCase();

            let fan = false, humidifier = false, condition = 'Ideal', message = '';
            if (mode === 'AUTO') {
                fan        = temp > 28.0;
                humidifier = hum  < 80.0;
                if (fan && humidifier) {
                    condition = 'Bahaya: Panas & Kering';
                    message   = `Suhu panas (${temp}°C > 28°C) & Kelembapan rendah (${hum}% < 80%). Kipas & Humidifier NYALA Otomatis!`;
                } else if (fan) {
                    condition = 'Waspada: Suhu Tinggi';
                    message   = `Suhu panas (${temp}°C > 28°C). Kipas NYALA Otomatis mendinginkan kumbung.`;
                } else if (humidifier) {
                    condition = 'Waspada: Kelembapan Rendah';
                    message   = `Kelembapan rendah (${hum}% < 80%). Humidifier NYALA Otomatis menyemprotkan embun.`;
                } else if (hum > 90) {
                    condition = 'Waspada: Kelembapan Tinggi';
                    message   = `Kelembapan tinggi (${hum}% > 90%). Berisiko pembusukan baglog.`;
                } else if (temp < 22) {
                    condition = 'Waspada: Suhu Rendah';
                    message   = `Suhu dingin (${temp}°C < 22°C). Pertumbuhan miselium melambat.`;
                } else {
                    condition = 'Ideal';
                    message   = `Suhu (${temp}°C) & Kelembapan (${hum}%) optimal. Kipas & Humidifier MATI (Kondisi Stabil).`;
                }
            } else {
                fan        = !!envData.actuators?.fan;
                humidifier = !!envData.actuators?.humidifier;
                condition  = 'Manual Control';
                message    = `Mode MANUAL Aktif: Kipas ${fan ? 'NYALA' : 'MATI'}, Humidifier ${humidifier ? 'NYALA' : 'MATI'}.`;
            }

            const formattedTime = new Date(now * 1000).toLocaleTimeString('id-ID');
            const updates = {};
            updates['sensor'] = { temperature: temp, humidity: hum, updated_at: now };
            if (mode === 'AUTO') {
                updates['actuators/fan']        = fan;
                updates['actuators/humidifier'] = humidifier;
            }
            updates['status'] = { condition, message };
            updates[`history/record_${now}`] = { temperature: temp, humidity: hum, fan, humidifier, timestamp: now, formatted_time: formattedTime };

            db.ref('mushroom_environment').update(updates)
                .catch(e => console.warn('Firebase simulasi gagal:', e));
        };

        window.toggleAutoStream = function(enabled) {
            const statusText = document.getElementById('autoStreamStatusText');
            if (enabled) {
                statusText.innerText = 'Live Simulation Berjalan...';
                if (autoStreamInterval) clearInterval(autoStreamInterval);
                autoStreamInterval = setInterval(() => {
                    const curTemp = parseFloat(envData.sensor?.temperature || 26.5);
                    const curHum  = parseFloat(envData.sensor?.humidity    || 85.0);
                    const newTemp = Math.min(Math.max(curTemp + (Math.random() * 0.8 - 0.4), 20.0), 35.0);
                    const newHum  = Math.min(Math.max(curHum  + (Math.random() * 2.0 - 1.0), 60.0), 95.0);
                    document.getElementById('simTempInput').value = newTemp.toFixed(1);
                    document.getElementById('simTempVal').innerText = newTemp.toFixed(1) + '°C';
                    document.getElementById('simHumInput').value = newHum.toFixed(1);
                    document.getElementById('simHumVal').innerText = newHum.toFixed(1) + '%';
                    window.submitSimulation();
                }, 4000);
            } else {
                statusText.innerText = 'Aktifkan Live Simulation';
                if (autoStreamInterval) { clearInterval(autoStreamInterval); autoStreamInterval = null; }
            }
        };
    </script>
</body>
</html>
