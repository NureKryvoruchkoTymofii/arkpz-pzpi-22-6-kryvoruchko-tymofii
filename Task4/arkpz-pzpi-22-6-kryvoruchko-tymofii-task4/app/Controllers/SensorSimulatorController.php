<?php
namespace App\Controllers;

use App\Models\Sensor;
use App\Models\SensorData;
use Exception;

class SensorSimulatorController
{
    public function simulateSelectedSensors(array $sensorIds)
    {
        set_time_limit(10);

        try {
            // Отримуємо список сенсорів за їх SensorID
            $sensors = Sensor::whereIn('SensorID', $sensorIds)->get();

            if ($sensors->isEmpty()) {
                echo "No sensors found for the provided IDs.\n";
                return;
            }

            $normalCount = 0;

            while (true) {
                foreach ($sensors as $sensor) {
                    // Генерація аномальних даних кожні три рази
                    if ($normalCount == 3) {
                        $data = $this->generateAnomalousData($sensor->SensorType);
                        $normalCount = 0; // Скидання лічильника
                    } else {
                        $data = $this->generateSensorData($sensor->SensorType);
                        $normalCount++;
                    }

                    $sensorData = new SensorData();
                    $sensorData->SensorID = $sensor->SensorID;
                    $sensorData->DataType = $data['type'];
                    $sensorData->DataValue = $data['value'];
                    $sensorData->Timestamp = date('Y-m-d H:i:s');
                    $sensorData->save();

                    echo "Data saved for SensorID {$sensor->SensorID}: {$data['type']} = {$data['value']}\n";
                }

                // Затримка на 5 секунд
                sleep(2);
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    private function generateSensorData($sensorType)
    {
        switch ($sensorType) {
            case 'temperature':
                return ['type' => 'temperature', 'value' => rand(-10, 40)]; // Випадкова температура
            case 'magnetic_field':
                return ['type' => 'magnetic_field', 'value' => rand(10, 100)]; // Випадкове магнітне поле
            case 'pressure':
                return ['type' => 'pressure', 'value' => rand(950, 1050)]; // Випадковий тиск
            case 'motion':
                return ['type' => 'motion', 'value' => rand(0, 1)]; // 0 або 1 для руху
            default:
                return ['type' => 'unknown', 'value' => 0];
        }
    }

    private function generateAnomalousData($sensorType)
    {
        switch ($sensorType) {
            case 'temperature':
                return ['type' => 'temperature', 'value' => rand(41, 50)]; // Аномальна температура
            case 'magnetic_field':
                return ['type' => 'magnetic_field', 'value' => rand(101, 150)]; // Аномальне магнітне поле
            case 'pressure':
                return ['type' => 'pressure', 'value' => rand(1051, 1100)]; // Аномальний тиск
            case 'motion':
                return ['type' => 'motion', 'value' => rand(0, 1)];
            default:
                return ['type' => 'unknown', 'value' => 0];
        }
    }
}
