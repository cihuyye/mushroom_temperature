<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

class MushroomController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    private function getMushroomData()
    {
        $reference = $this->database->getReference('mushroom_environment');
        $data = $reference->getValue() ?: [];

        if (!isset($data['sensor'])) {
            $data['sensor'] = ['temperature' => 26.5, 'humidity' => 85.0, 'updated_at' => time()];
        }
        if (!isset($data['actuators'])) {
            $data['actuators'] = ['fan' => false, 'humidifier' => false, 'mode' => 'AUTO'];
        }
        if (!isset($data['status'])) {
            $data['status'] = ['condition' => 'Ideal', 'message' => 'Kondisi stabil.'];
        }
        if (!isset($data['history'])) {
            $data['history'] = [];
        }

        // Ambil data real dari node 'records' yang dikirim oleh Wokwi ESP32
        try {
            $records = $this->database->getReference('records')->getValue();
            if ($records && is_array($records)) {
                $validRecords = array_filter($records, function ($r) {
                    return isset($r['timestamp']) && $r['timestamp'] > 1000000000;
                });

                if (!empty($validRecords)) {
                    uasort($validRecords, fn($a, $b) => ($a['timestamp'] ?? 0) <=> ($b['timestamp'] ?? 0));
                    $data['history'] = array_slice($validRecords, -20, 20, true);
                    $latest = end($validRecords);
                    if ($latest) {
                        $data['sensor']['temperature'] = floatval($latest['temperature'] ?? $data['sensor']['temperature']);
                        $data['sensor']['humidity']    = floatval($latest['humidity'] ?? $data['sensor']['humidity']);
                        $data['sensor']['updated_at']   = intval($latest['timestamp'] ?? time());

                        $mode = strtoupper($data['actuators']['mode'] ?? 'AUTO');
                        if ($mode === 'AUTO') {
                            $data['actuators']['fan']        = !empty($latest['fan']);
                            $data['actuators']['humidifier'] = !empty($latest['humidifier']);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently continue
        }

        $this->evaluateStatusAndActuators($data);
        return ['mushroom_environment' => $data];
    }

    private function saveMushroomData($data)
    {
        $this->database->getReference('mushroom_environment')->set($data['mushroom_environment']);
    }

    private function evaluateStatusAndActuators(&$env)
    {
        $temp = floatval($env['sensor']['temperature'] ?? 26.5);
        $hum = floatval($env['sensor']['humidity'] ?? 85.0);
        $mode = strtoupper($env['actuators']['mode'] ?? 'AUTO');

        $fan = filter_var($env['actuators']['fan'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $humidifier = filter_var($env['actuators']['humidifier'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($mode === 'AUTO') {
            // Mode AUTO: Otomatisasi berbasis batas sensor
            $fan = ($temp > 28.0);
            $humidifier = ($hum < 80.0);

            if ($temp > 28.0 && $hum < 80.0) {
                $condition = 'Bahaya: Panas & Kering';
                $message = "Suhu panas ({$temp}°C > 28°C) & Kelembapan rendah ({$hum}% < 80%). Kipas & Humidifier NYALA Otomatis!";
            } elseif ($temp > 28.0) {
                $condition = 'Waspada: Suhu Tinggi';
                $message = "Suhu panas ({$temp}°C > 28°C). Kipas NYALA Otomatis mendinginkan kumbung.";
            } elseif ($hum < 80.0) {
                $condition = 'Waspada: Kelembapan Rendah';
                $message = "Kelembapan rendah ({$hum}% < 80%). Humidifier NYALA Otomatis menyemprotkan embun.";
            } elseif ($hum > 90.0) {
                $condition = 'Waspada: Kelembapan Tinggi';
                $message = "Kelembapan tinggi ({$hum}% > 90%). Berisiko pembusukan baglog.";
            } elseif ($temp < 22.0) {
                $condition = 'Waspada: Suhu Rendah';
                $message = "Suhu dingin ({$temp}°C < 22°C). Pertumbuhan miselium melambat.";
            } else {
                $condition = 'Ideal';
                $message = "Suhu ({$temp}°C) & Kelembapan ({$hum}%) optimal. Kipas & Humidifier MATI (Kondisi Stabil).";
            }
        } else {
            // Mode MANUAL: Ikuti penuh sakelar pilihan pengguna
            $condition = 'Manual Control';
            $fanText = $fan ? 'Kipas NYALA' : 'Kipas MATI';
            $humText = $humidifier ? 'Humidifier NYALA' : 'Humidifier MATI';
            $message = "Mode MANUAL Aktif: {$fanText}, {$humText} dikontrol secara manual oleh pengguna.";
        }

        $env['actuators']['fan'] = (bool)$fan;
        $env['actuators']['humidifier'] = (bool)$humidifier;
        $env['actuators']['mode'] = $mode;
        $env['status']['condition'] = $condition;
        $env['status']['message'] = $message;
    }

    public function index()
    {
        $data = $this->getMushroomData();
        $firebaseConfig = [
            'apiKey'            => env('FIREBASE_API_KEY', 'AIzaSyCkF_T6BXW0deHfe3YK1WF-jn7U-HvvODg'),
            'authDomain'        => env('FIREBASE_AUTH_DOMAIN', 'mushroom-monitoring-831ed.firebaseapp.com'),
            'databaseURL'       => rtrim(env('FIREBASE_DATABASE_URL', 'https://mushroom-monitoring-831ed-default-rtdb.asia-southeast1.firebasedatabase.app/'), '/'),
            'projectId'         => env('FIREBASE_PROJECT_ID', 'mushroom-monitoring-831ed'),
            'storageBucket'     => env('FIREBASE_STORAGE_BUCKET', 'mushroom-monitoring-831ed.firebasestorage.app'),
            'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID', '780912167030'),
            'appId'             => env('FIREBASE_APP_ID', '1:780912167030:web:e946682a3d3e2911f2d757'),
        ];

        return view('dashboard', [
            'initialData'    => $data['mushroom_environment'],
            'firebaseConfig' => $firebaseConfig
        ]);
    }

    public function getData()
    {
        $data = $this->getMushroomData();
        return response()->json($data['mushroom_environment']);
    }

    public function updateActuators(Request $request)
    {
        $data = $this->getMushroomData();
        $env = &$data['mushroom_environment'];

        if ($request->has('fan')) {
            $env['actuators']['fan'] = filter_var($request->input('fan'), FILTER_VALIDATE_BOOLEAN);
            $env['actuators']['mode'] = 'MANUAL';
        }

        if ($request->has('humidifier')) {
            $env['actuators']['humidifier'] = filter_var($request->input('humidifier'), FILTER_VALIDATE_BOOLEAN);
            $env['actuators']['mode'] = 'MANUAL';
        }

        if ($request->has('mode')) {
            $env['actuators']['mode'] = strtoupper($request->input('mode'));
        }

        $this->evaluateStatusAndActuators($env);
        $this->saveMushroomData($data);

        return response()->json([
            'success' => true,
            'environment' => $env
        ]);
    }

    public function simulate(Request $request)
    {
        $request->validate([
            'temperature' => 'required|numeric|min:10|max:50',
            'humidity' => 'required|numeric|min:20|max:100',
        ]);

        $data = $this->getMushroomData();
        $env = &$data['mushroom_environment'];

        $temp = floatval($request->input('temperature'));
        $hum = floatval($request->input('humidity'));
        $now = time();

        $env['sensor']['temperature'] = $temp;
        $env['sensor']['humidity'] = $hum;
        $env['sensor']['updated_at'] = $now;

        $this->evaluateStatusAndActuators($env);

        if (!isset($env['history']) || !is_array($env['history'])) {
            $env['history'] = [];
        }

        $historyKey = 'record_' . $now;
        $env['history'][$historyKey] = [
            'temperature' => $temp,
            'humidity' => $hum,
            'fan' => $env['actuators']['fan'],
            'humidifier' => $env['actuators']['humidifier'],
            'timestamp' => $now,
            'formatted_time' => date('H:i:s', $now)
        ];

        // Batasi histori maksimal 50 record terakhir
        if (count($env['history']) > 50) {
            $env['history'] = array_slice($env['history'], -50, 50, true);
        }

        $this->saveMushroomData($data);

        return response()->json([
            'success' => true,
            'environment' => $env
        ]);
    }

    public function pushSensorData(Request $request)
    {
        $temp = $request->input('temperature', $request->input('temp'));
        $hum = $request->input('humidity', $request->input('hum'));

        if ($temp !== null && $hum !== null) {
            $simReq = new Request([
                'temperature' => $temp,
                'humidity' => $hum
            ]);
            return $this->simulate($simReq);
        }

        return response()->json(['error' => 'Invalid parameters'], 400);
    }
}