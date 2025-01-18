<?php

namespace App\Controllers;
use App\Models\AccessLog;
use App\Models\Lock;
use App\Models\User;
use App\Models\Sensor;
use App\Models\LockUsageLog;
use Exception;

require_once __DIR__ . '/helpers.php';

class LockController {
    public function index() {
        try {
            $locks = Lock::all();
            
            error_log("Fetched locks: " . json_encode($locks));
    
            if ($locks->isEmpty()) {
                error_log("No locks found");
            }
    
            jsonResponse($locks);
        } catch (\Exception $e) {

            error_log("Error in LockController index: " . $e->getMessage());
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    

    public function store() {
        try {
            $data = getRequestData();
    
            if (!isset($data['LockName']) || !isset($data['Location']) || !isset($data['Status']) || !isset($data['OwnerID'])) {
                jsonResponse(['error' => 'Missing required fields'], 400);
            }
    
            $user = User::find($data['OwnerID']);
            if (!$user) {
                jsonResponse(['error' => 'User with the specified OwnerID does not exist'], 404);
            }
    
            $lock = Lock::create([
                'LockName' => $data['LockName'],
                'Location' => $data['Location'],
                'Status' => $data['Status'],
                'OwnerID' => $data['OwnerID'],
            ]);
    
            jsonResponse($lock);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id) {
        try {
            $lock = Lock::find($id);

            if (!$lock) {
                jsonResponse(['message' => 'Lock not found'], 404); 
            }

            jsonResponse($lock); 
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500); 
        }
    }

    public function update($id) {
        try {
            $data = getRequestData();

            $lock = Lock::find($id);
            if (!$lock) {
                jsonResponse(['error' => 'Lock not found'], 404);
            }

            if (isset($data['OwnerID'])) {
                $user = User::find($data['OwnerID']);
                if (!$user) {
                    jsonResponse(['error' => 'User with the specified OwnerID does not exist'], 404);
                }
            }

            $lock->update([
                'LockName' => $data['LockName'] ?? $lock->LockName,
                'Location' => $data['Location'] ?? $lock->Location,
                'Status' => $data['Status'] ?? $lock->Status,
                'OwnerID' => $data['OwnerID'] ?? $lock->OwnerID,
            ]);

            jsonResponse($lock);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($id) {
        try {
            $lock = Lock::find($id);
    
            if (!$lock) {
                jsonResponse(['message' => 'Lock not found'], 404);
            }
    
            $lock->delete();
    
            jsonResponse(['message' => 'Lock deleted successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getByStatus($status) {
        try {
            $locks = Lock::where('Status', $status)->get();
    
            if ($locks->isEmpty()) {
                jsonResponse(['message' => 'No locks found with this status'], 404);
            }
    
            jsonResponse($locks);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    
    private $secretKey = "your_secret_key";

    public function unlock() {
        $data = getRequestData();
        
        $user = $_SESSION['user'];
        $lock_id = $data['LockID']; 
    
        $access = AccessLog::where('UserID', $user['UserID'])->where('LockID', $lock_id)->where('Action', 'access_granted')->first();
    
        if ($access) {
            $lock = Lock::find($lock_id); 
            $lock->Status = 'unlocked'; 
            $lock->save();
    
            $lockUsageLog = new LockUsageLog();
            $lockUsageLog->LockID = $lock_id;
            $lockUsageLog->Action = 'open';
            $lockUsageLog->Timestamp = date('Y-m-d H:i:s'); 
            $lockUsageLog->save();
    
            jsonResponse(['message' => 'Lock opened successfully'], 200);
    
            $this->scheduleAutoLock($lock_id);
        } else {
            jsonResponse(['message' => 'Access denied'], 403);
        }
    }
    
    public function lock() {
        $data = getRequestData();
        
        $user = $_SESSION['user'];
        $lock_id = $data['LockID'];
    
        $access = AccessLog::where('UserID', $user['UserID'])->where('LockID', $lock_id)->where('Action', 'access_granted')->first();
    
        if ($access) {
            $lock = Lock::find($lock_id);
            $lock->Status = 'locked';
            $lock->save();
    
            $lockUsageLog = new LockUsageLog();
            $lockUsageLog->LockID = $lock_id;
            $lockUsageLog->Action = 'close';
            $lockUsageLog->Timestamp = date('Y-m-d H:i:s');
            $lockUsageLog->save();
    
            jsonResponse(['message' => 'Lock closed successfully'], 200);
        } else {
            jsonResponse(['message' => 'Access denied'], 403);
        }
    }
    

     public function checkStatus() {
        $data = getRequestData();
        
        $user = $_SESSION['user'];
        $lock_id = $data['LockID'];

        $access = AccessLog::where('UserID', $user['UserID'])->where('LockID', $lock_id)->where('Action', 'access_granted')->first();

        if ($access) {
            $lock = Lock::find($lock_id);

            jsonResponse(['message' => 'Lock status retrieved successfully', 'status' => $lock->Status], 200);
        } else {
            jsonResponse(['message' => 'Access denied'], 403);
        }
    }

    private function scheduleAutoLock($lock_id) {
        $lock = Lock::find($lock_id);
    
        if (!$lock) {
            error_log('Lock not found: LockID ' . $lock_id);
            return;
        }
    
        $auto_lock_time = $lock->auto_lock_time ?? 10; // Час автоматичного блокування за замовчуванням
        $url = 'http://localhost:8000/lock/autoLock';
        $data = ['LockID' => $lock_id];
    
        // Використовуємо cURL для відправки POST-запиту
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        // Додаємо затримку перед виконанням запиту
        sleep($auto_lock_time);
    
        $response = curl_exec($ch);
    
        if ($response === false) {
            error_log('cURL error: ' . curl_error($ch));
        } else {
            error_log('Auto-lock response: ' . $response);
        }
    
        curl_close($ch);
    }

    public function autoLock() {
        $data = getRequestData();
        $lock_id = $data['LockID'];
    
        $lock = Lock::find($lock_id);
        if (!$lock) {
            jsonResponse(['error' => 'Lock not found'], 404);
            return;
        }
    
        if ($lock->Status === 'unlocked') {
            $lock->Status = 'locked';
            $lock->save();
    
            error_log('Lock automatically closed: LockID ' . $lock_id);
            jsonResponse(['message' => 'Lock automatically closed successfully'], 200);
        } else {
            jsonResponse(['message' => 'Lock already closed'], 200);
        }
    }

    public function updateAutoLockTime() {
        $data = getRequestData();
        $lock_id = $data['LockID'] ?? null;
        $new_time = $data['auto_lock_time'] ?? null;
    
        if (!$lock_id || !$new_time || !is_numeric($new_time) || $new_time <= 0) {
            jsonResponse(['error' => 'Invalid input'], 400);
            return;
        }
    
        $lock = Lock::find($lock_id);
        if (!$lock) {
            jsonResponse(['error' => 'Lock not found'], 404);
            return;
        }
    
        $lock->auto_lock_time = $new_time;
        $lock->save();
    
        jsonResponse([
            'message' => 'Auto-lock time updated successfully',
            'lock' => $lock
        ], 200);
    }

    public function userReport() {
        try {
            $user = $_SESSION['user'];
    
            if ($user['Role'] !== 'user') {
                jsonResponse(['error' => 'Access denied: only users can generate the user report'], 403);
                return;
            }
    
            $accessLogs = AccessLog::where('UserID', $user['UserID'])
                ->where('Action', 'access_granted')
                ->get();
    
            $report = [];
            foreach ($accessLogs as $access) {
                $lock = Lock::find($access->LockID);
                if ($lock) {
                    $sensors = Sensor::where('LockID', $lock->LockID)->get();
                    $sensorData = $sensors->map(function($sensor) {
                        return [
                            'SensorID' => $sensor->SensorID,
                            'Location' => $sensor->Location,
                            'Status' => $sensor->Status,
                            'LastUpdated' => $sensor->LastUpdated,
                            'SensorType' => $sensor->SensorType,
                        ];
                    });
    
                    $report[] = [
                        'LockID' => $lock->LockID,
                        'LockName' => $lock->LockName,
                        'Location' => $lock->Location,
                        'Status' => $lock->Status,
                        'CreatedAt' => $lock->CreatedAt,
                        'OwnerID' => $lock->OwnerID,
                        'AutoLockTime' => $lock->auto_lock_time,
                        'Sensors' => $sensorData,
                    ];
                }
            }
    
            jsonResponse([
                'username' => $user['Username'],
                'report' => $report
            ], 200);
    
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    
    public function sensorDataReport() {
        try {
            $user = $_SESSION['user'];
            
            if ($user['Role'] !== 'admin') {
                jsonResponse(['error' => 'Access denied: only admins can generate the sensor data report'], 403);
                return;
            }
    
            $accessLogs = AccessLog::where('UserID', $user['UserID'])
                ->where('Action', 'access_granted')
                ->pluck('LockID');
    
            $locks = Lock::whereIn('LockID', $accessLogs)
                ->with(['sensors.sensorData'])
                ->get();
    
            if ($locks->isEmpty()) {
                jsonResponse(['user' => $user['Username'], 'report' => []], 200);
                return;
            }
    
            $report = $locks->map(function ($lock) {
                $sensorData = $lock->sensors->map(function ($sensor) {
                    return [
                        'SensorID' => $sensor->SensorID,
                        'Location' => $sensor->Location,
                        'Status' => $sensor->Status,
                        'LastUpdated' => $sensor->LastUpdated,
                        'SensorType' => $sensor->SensorType,
                        'SensorData' => $sensor->sensorData->map(function ($data) {
                            return [
                                'DataID' => $data->DataID,
                                'DataType' => $data->DataType,
                                'DataValue' => $data->DataValue,
                                'Timestamp' => $data->Timestamp,
                            ];
                        }),
                    ];
                });
    
                return [
                    'LockID' => $lock->LockID,
                    'LockName' => $lock->LockName,
                    'Location' => $lock->Location,
                    'Status' => $lock->Status,
                    'CreatedAt' => $lock->CreatedAt,
                    'OwnerID' => $lock->OwnerID,
                    'AutoLockTime' => $lock->auto_lock_time,
                    'Sensors' => $sensorData,
                ];
            });
    
            jsonResponse(['user' => $user['Username'], 'report' => $report], 200);
    
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    
    
    

}
