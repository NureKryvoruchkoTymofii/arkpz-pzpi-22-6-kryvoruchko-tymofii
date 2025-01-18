<?php

namespace App\Controllers;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Lock;

require_once __DIR__ . '/helpers.php';

class SensorController {
    public function index() {
        try {
            $sensors = Sensor::all();

            if ($sensors->isEmpty()) {
                jsonResponse(['message' => 'No sensors found'], 404);
            }

            jsonResponse($sensors);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function store() {
        try {
            $data = getRequestData();
    
            if (!isset($data['UserID']) || !isset($data['LockID']) || !isset($data['SensorType'])) {
                jsonResponse(['error' => 'Missing required fields'], 400);
                return;
            }
    
            if (!User::find($data['UserID'])) {
                jsonResponse(['error' => 'User not found'], 404);
                return;
            }
    
            if (!Lock::find($data['LockID'])) {
                jsonResponse(['error' => 'Lock not found'], 404);
                return;
            }
    
            $sensor = Sensor::create([
                'UserID' => $data['UserID'],
                'LockID' => $data['LockID'],
                'SensorType' => $data['SensorType'],
                'Status' => $data['Status'] ?? 'active',
                'LastUpdated' => $data['LastUpdated'] ?? date('Y-m-d H:i:s'),
            ]);
    
            jsonResponse($sensor, 201);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    

    public function show($SensorID) {
        try {
            $sensor = Sensor::find($SensorID);
            
            if (!$sensor) {
                jsonResponse(['message' => 'Sensor not found'], 404);
            }
            
            jsonResponse($sensor);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function update($id) {
        try {
            $data = getRequestData();
    
            $sensor = Sensor::find($id);
            if (!$sensor) {
                jsonResponse(['error' => 'Sensor not found'], 404);
                return;
            }
    
            if (isset($data['UserID']) && !User::find($data['UserID'])) {
                jsonResponse(['error' => 'User not found'], 404);
                return;
            }
    
            if (isset($data['LockID']) && !Lock::find($data['LockID'])) {
                jsonResponse(['error' => 'Lock not found'], 404);
                return;
            }
    
            $sensor->update([
                'UserID' => $data['UserID'] ?? $sensor->UserID,
                'LockID' => $data['LockID'] ?? $sensor->LockID,
                'SensorType' => $data['SensorType'] ?? $sensor->SensorType,
                'Status' => $data['Status'] ?? $sensor->Status,
                'LastUpdated' => date('Y-m-d H:i:s'),
            ]);
    
            jsonResponse($sensor);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    

    public function delete($SensorID) {
        try {
            $sensor = Sensor::find($SensorID);
    
            if (!$sensor) {
                jsonResponse(['message' => 'Sensor not found'], 404);
                return;
            }
    
            $sensor->delete();
    
            jsonResponse(['message' => 'Sensor deleted successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getByType($sensorType) {
        try {
            $sensors = Sensor::where('SensorType', $sensorType)->get();
            
            if ($sensors->isEmpty()) {
                jsonResponse(['message' => 'No sensors found for this type'], 404);
            }
            
            jsonResponse($sensors);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
