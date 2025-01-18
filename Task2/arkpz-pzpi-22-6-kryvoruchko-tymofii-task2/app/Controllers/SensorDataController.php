<?php

namespace App\Controllers;
use App\Models\SensorData;
use App\Models\Sensor;
use App\Models\AccessLog;
use App\Models\Lock;
use Exception;

require_once __DIR__ . '/helpers.php';


class SensorDataController {

    public function index() {
        try {
            $sensorData = SensorData::all();
            if ($sensorData->isEmpty()) {
                jsonResponse(['message' => 'No sensor data found'], 404);
            } else {
                jsonResponse($sensorData);
            }
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function store() {
        try {
            $data = getRequestData();

            if (!isset($data['SensorID'], $data['DataType'], $data['DataValue'])) {
                jsonResponse(['error' => 'Missing required fields'], 400);
            }

            $sensor = Sensor::find($data['SensorID']);
            if (!$sensor) {
                jsonResponse(['error' => 'Sensor not found'], 404);
            }

            $sensorData = SensorData::create([
                'SensorID' => $data['SensorID'],
                'DataType' => $data['DataType'],
                'DataValue' => $data['DataValue'],
                'Timestamp' => $data['Timestamp'] ?? date('Y-m-d H:i:s'),
                
            ]);

            jsonResponse($sensorData, 201);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function update($dataId) {
        try {
            $data = getRequestData();
            
            $sensorData = SensorData::find($dataId);
    
            if (!$sensorData) {
                jsonResponse(['message' => 'Data not found'], 404);
            }
    
            $sensorData->update([
                'DataType' => $data['DataType'] ?? $sensorData->DataType,
                'DataValue' => $data['DataValue'] ?? $sensorData->DataValue,
                'Timestamp' => $data['Timestamp'] ?? $sensorData->Timestamp
            ]);
    
            jsonResponse($sensorData);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($dataId) {
        try {
            $sensorData = SensorData::find($dataId);
    
            if (!$sensorData) {
                jsonResponse(['message' => 'Data not found'], 404);
            }
    
            $sensorData->delete();
    
            jsonResponse(['message' => 'Sensor data deleted successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    

    public function getBySensorID($SensorID) {
        try {
            $sensorData = SensorData::where('SensorID', $SensorID)->get();
    
            if ($sensorData->isEmpty()) {
                jsonResponse(['message' => 'No data found for this sensor'], 404);
            }
    
            jsonResponse($sensorData);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function sensorDataAnalysis($sensorID, $startDate, $endDate) {
        try {
            $user = $_SESSION['user'];
            
            // Перевірка, чи користувач є адміністратором
            if ($user['Role'] !== 'admin') {
                return jsonResponse(['error' => 'Access denied: only admins can generate the sensor data analysis report'], 403);
            }
    
            $data = SensorData::where('SensorID', $sensorID)
                ->whereBetween('Timestamp', [$startDate, $endDate])
                ->get();
            
            if ($data->isEmpty()) {
                error_log("No data found for SensorID: $sensorID between $startDate and $endDate");
                return jsonResponse(['error' => 'No data available for the given period'], 404);
            } else {
                error_log("Data for SensorID: $sensorID between $startDate and $endDate: " . json_encode($data));
            }
    
            // Групування даних за DataType
            $groupedData = $data->groupBy('DataType');
    
            $report = [];
            foreach ($groupedData as $type => $records) {
                $values = $records->pluck('DataValue')->map(fn($value) => (float) $value)->toArray();
                
                if (empty($values)) {
                    continue; // Якщо даних немає, пропускаємо обробку
                }
    
                $average = array_sum($values) / count($values);
                $minValue = min($values);
                $maxValue = max($values);
    
                // Виявлення аномалій
                $threshold = 1.5 * ($maxValue - $minValue);
                $anomalies = array_filter($values, function ($value) use ($average, $threshold) {
                    return abs($value - $average) > $threshold;
                });
    
                $report[$type] = [
                    'average' => round($average, 2),
                    'min' => round($minValue, 2),
                    'max' => round($maxValue, 2),
                    'anomalies' => array_values($anomalies),
                ];
            }
    
            return jsonResponse([
                'message' => 'Sensor data analysis report generated successfully.',
                'sensorID' => $sensorID,
                'report' => $report,
                'recommendation' => 'Проаналізуйте аномалії та дослідіть роботу датчиків на предмет виявлених проблем.',
            ], 200);
        } catch (Exception $e) {
            return jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function exportData() {
        try {
            $user = $_SESSION['user'];
    
            // Перевірка, чи користувач є адміністратором
            if ($user['Role'] !== 'admin') {
                return jsonResponse(['error' => 'Access denied: only admins can export data'], 403);
            }
    
            // Отримання всіх замків, до яких адміністратор має доступ
            $accessLogs = AccessLog::where('UserID', $user['UserID'])
                ->where('Action', 'access_granted')
                ->get();
    
            $csvData = [];
    
            // Для кожного доступного замка
            foreach ($accessLogs as $access) {
                $lock = Lock::find($access->LockID);
                if ($lock) {
                    // Отримуємо всі сенсори для поточного замка
                    $sensors = Sensor::where('LockID', $lock->LockID)->get();
                    foreach ($sensors as $sensor) {
                        // Отримуємо всі записи даних для кожного сенсора
                        $sensorDataEntries = SensorData::where('SensorID', $sensor->SensorID)->get();
                        foreach ($sensorDataEntries as $data) {
                            $csvData[] = [
                                'LockID' => $lock->LockID,
                                'LockName' => $lock->LockName,
                                'LockLocation' => $lock->Location,
                                'LockStatus' => $lock->Status,
                                'LockCreatedAt' => $lock->CreatedAt,
                                'LockOwnerID' => $lock->OwnerID,
                                'AutoLockTime' => $lock->auto_lock_time,
                                'SensorID' => $sensor->SensorID,
                                'SensorLocation' => $sensor->Location,
                                'SensorStatus' => $sensor->Status,
                                'SensorLastUpdated' => $sensor->LastUpdated,
                                'SensorType' => $sensor->SensorType,
                                'DataID' => $data->DataID,
                                'DataType' => $data->DataType,
                                'DataValue' => $data->DataValue,
                                'DataTimestamp' => $data->Timestamp,
                            ];
                        }
                    }
                }
            }
    
            if (empty($csvData)) {
                return jsonResponse(['error' => 'No data available for export'], 404);
            }

            $exportDir = __DIR__ . '/../../exports/';
            if (!file_exists($exportDir)) {
                mkdir($exportDir, 0777, true);  // Створюємо директорію, якщо її немає
            }
            
            // Створення CSV файлу
            $filename = 'export_data_' . date('Ymd_His') . '.csv';
            $filePath = __DIR__ . '/../../exports/' . $filename;
            $file = fopen($filePath, 'w');
    
            // Заголовки CSV
            fputcsv($file, [
                'LockID', 'LockName', 'LockLocation', 'LockStatus', 'LockCreatedAt',
                'LockOwnerID', 'AutoLockTime', 'SensorID', 'SensorLocation', 'SensorStatus',
                'SensorLastUpdated', 'SensorType', 'DataID', 'DataType', 'DataValue', 'DataTimestamp'
            ]);
    
            // Запис даних у CSV
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
    
            fclose($file);
    
            // Повернення посилання на файл
            return jsonResponse([
                'message' => 'Data exported successfully.',
                'fileUrl' => "/exports/$filename",
            ], 200);
    
        } catch (Exception $e) {
            return jsonResponse(['error' => 'Failed to export data: ' . $e->getMessage()], 500);
        }
    }

    public function importData() {
        try {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
                return jsonResponse(['error' => 'No file uploaded or there was an error uploading the file'], 400);
            }
    
            $file = $_FILES['file']['tmp_name'];
    
            if (pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) !== 'csv') {
                return jsonResponse(['error' => 'Invalid file format. Please upload a CSV file.'], 400);
            }
    
            if (($handle = fopen($file, 'r')) === false) {
                return jsonResponse(['error' => 'Error opening the file'], 500);
            }
    
            $headers = fgetcsv($handle);
    
            $importedData = [];
            while (($row = fgetcsv($handle)) !== false) {
                $importedData[] = array_combine($headers, $row);
            }
    
            fclose($handle); 
    
            foreach ($importedData as $data) {
                $lock = Lock::firstOrCreate([
                    'LockID' => $data['LockID'],
                ], [
                    'LockName' => $data['LockName'],
                    'Location' => $data['LockLocation'],
                    'Status' => $data['LockStatus'],
                    'CreatedAt' => $data['LockCreatedAt'],
                    'OwnerID' => $data['LockOwnerID'],
                    'AutoLockTime' => $data['AutoLockTime'],
                ]);
    
                $sensor = Sensor::firstOrCreate([
                    'SensorID' => $data['SensorID'],
                    'LockID' => $lock->LockID,
                ], [
                    'SensorLocation' => $data['SensorLocation'],
                    'Status' => $data['SensorStatus'],
                    'LastUpdated' => $data['SensorLastUpdated'],
                    'SensorType' => $data['SensorType'],
                ]);
    
                SensorData::create([
                    'SensorID' => $sensor->SensorID,
                    'DataType' => $data['DataType'],
                    'DataValue' => $data['DataValue'],
                    'Timestamp' => $data['DataTimestamp'],
                ]);
            }
    
            return jsonResponse(['message' => 'Data imported successfully'], 200);
    
        } catch (Exception $e) {
            return jsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    

}
