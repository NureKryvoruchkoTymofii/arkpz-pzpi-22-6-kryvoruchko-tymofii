<?php

namespace App\Controllers;
use App\Models\LockUsageLog;

require_once __DIR__ . '/helpers.php';

class LockUsageLogController {
    public function lockActivityReport($lockID) {
        $logs = LockUsageLog::where('LockID', $lockID)->orderBy('Timestamp', 'asc')->get();
    
        if ($logs->isEmpty()) {
            return jsonResponse(['error' => 'No activity found for this lock'], 404);
        }
    
        $usageCount = $logs->count();
    
        // Аналіз активності по годинах
        $hourlyActivity = array_fill(0, 24, 0);
        foreach ($logs as $log) {
            $hour = (int)date('H', strtotime($log->Timestamp));
            $hourlyActivity[$hour]++;
        }
        $peakHour = array_search(max($hourlyActivity), $hourlyActivity);
    
        // Розрахунок середнього часу між подіями
        $intervals = [];
        $lastOpenTime = null;
    
        foreach ($logs as $log) {
            if ($log->Action === 'open') {
                $lastOpenTime = strtotime($log->Timestamp);
            } elseif ($log->Action === 'close' && $lastOpenTime) {
                $intervals[] = strtotime($log->Timestamp) - $lastOpenTime;
                $lastOpenTime = null;
            }
        }
    
        $averageInterval = $intervals ? array_sum($intervals) / count($intervals) : 0;
    
        return jsonResponse([
            'LockID' => $lockID,
            'usageCount' => $usageCount,
            'peakHour' => $peakHour,
            'averageIntervalSeconds' => round($averageInterval, 2),
            'message' => 'Звіт про активність надає огляд використання замка.',
            'details' => [
                'usageCount' => 'Загальна кількість разів, коли замок був відкритий або закритий.',
                'peakHour' => 'Година, коли замок мав найбільшу активність. Це може допомогти визначити години пік використання.',
                'averageIntervalSeconds' => 'Середній час (в секундах) між відкриттям і закриттям замка. Це може допомогти зрозуміти поведінку користувачів і ефективність використання.'
            ]
        ], 200);
    }
}
