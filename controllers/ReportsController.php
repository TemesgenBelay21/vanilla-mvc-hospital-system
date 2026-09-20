<?php

/**
 * ReportsController — admin analytics dashboard (Chart.js):
 * revenue, patient activity, doctor performance and bed occupancy,
 * all with a from/to date-range filter.
 */

declare(strict_types=1);

class ReportsController
{
    public function index(): void
    {
        require_role('admin');

        $type = trim($_GET['report'] ?? 'revenue');
        $from = trim($_GET['from'] ?? '') ?: date('Y-m-01');
        $to   = trim($_GET['to'] ?? '')   ?: date('Y-m-d');
        $from = $this->normalizeDate($from);
        $to   = $this->normalizeDate($to);

        $data = [
            'type'       => $type,
            'from'       => $from,
            'to'         => $to,
            'revenue'    => $type === 'revenue' ? $this->revenueData($from, $to) : null,
            'patients'   => $type === 'patients' ? $this->patientData($from, $to) : null,
            'doctors'    => $type === 'doctors' ? $this->doctorData($from, $to) : null,
            'beds'       => $type === 'beds' ? $this->bedData() : null,
            'kpis'       => [
                'revenue'     => Payment::sumBetween($from, $to),
                'patients'    => $this->newPatientsBetween($from, $to),
                'appointments'=> $this->appointmentsBetween($from, $to),
                'labComplete' => $this->completedLabsBetween($from, $to),
            ],
        ];

        view('admin/reports', $data);
    }

    private function revenueData(string $from, string $to): array
    {
        $daily = [];
        $byMethod = ['cash' => 0.0, 'telebirr' => 0.0];
        $period    = new DatePeriod(date_create($from), new DateInterval('P1D'), date_create($to . ' 23:59:59'));

        foreach ($period as $day) {
            $daily[$day->format('Y-m-d')] = 0.0;
        }

        $stmt = db()->prepare(
            'SELECT DATE(p.transaction_date) AS day, p.method, SUM(p.amount) AS total
             FROM payments p
             WHERE p.status = "success" AND p.transaction_date >= ? AND p.transaction_date <= ?
             GROUP BY DATE(p.transaction_date), p.method'
        );
        $stmt->execute([$from . ' 00:00:00', $to . ' 23:59:59']);
        foreach ($stmt->fetchAll() as $row) {
            $daily[$row['day']] = round((float) ($daily[$row['day']] ?? 0) + (float) $row['total'], 2);
            if (isset($byMethod[$row['method']])) {
                $byMethod[$row['method']] = round($byMethod[$row['method']] + (float) $row['total'], 2);
            }
        }

        return [
            'labels' => array_keys($daily),
            'values' => array_map('floatval', array_values($daily)),
            'byMethod' => $byMethod,
            'total'  => Payment::sumBetween($from, $to),
        ];
    }

    private function patientData(string $from, string $to): array
    {
        $daily = [];
        $period = new DatePeriod(date_create($from), new DateInterval('P1D'), date_create($to . ' 23:59:59'));
        foreach ($period as $day) {
            $daily[$day->format('Y-m-d')] = 0;
        }

        $stmt = db()->prepare(
            'SELECT DATE(created_at) AS day, COUNT(*) AS c
             FROM users WHERE role = "patient" AND created_at >= ? AND created_at <= ?
             GROUP BY DATE(created_at)'
        );
        $stmt->execute([$from . ' 00:00:00', $to . ' 23:59:59']);
        foreach ($stmt->fetchAll() as $row) {
            $daily[$row['day']] = (int) $row['c'];
        }

        $appts = db()->query(
            'SELECT dep.name, COUNT(*) AS c
             FROM appointments a JOIN departments dep ON dep.id = a.department_id
             GROUP BY dep.id, dep.name ORDER BY c DESC'
        )->fetchAll();

        return [
            'labels'      => array_keys($daily),
            'values'      => array_map('intval', array_values($daily)),
            'byDepartment'=> [
                'labels' => array_column($appts, 'name'),
                'values' => array_map('intval', array_column($appts, 'c')),
            ],
        ];
    }

    private function doctorData(string $from, string $to): array
    {
        $stmt = db()->prepare(
            'SELECT u.name AS doctor_name, dep.name AS department_name,
                    COUNT(a.id) AS total,
                    SUM(CASE WHEN a.status = "completed" THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN a.status = "approved" THEN 1 ELSE 0 END) AS upcoming
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN departments dep ON dep.id = d.department_id
             LEFT JOIN appointments a ON a.doctor_id = d.id
                  AND a.appointment_date >= ? AND a.appointment_date <= ?
             GROUP BY d.id, u.name, dep.name
             ORDER BY total DESC'
        );
        $stmt->execute([$from, $to]);
        $rows = $stmt->fetchAll();

        return [
            'names'   => array_column($rows, 'doctor_name'),
            'total'   => array_map('intval', array_column($rows, 'total')),
            'completed' => array_map('intval', array_column($rows, 'completed')),
            'upcoming'  => array_map('intval', array_column($rows, 'upcoming')),
            'months'  => $this->appointmentsByMonth($from, $to),
        ];
    }

    private function bedData(): array
    {
        $wards = db()->query(
            'SELECT w.name,
                    COUNT(b.id) AS total_beds,
                    COALESCE(SUM(CASE WHEN b.status = "occupied" THEN 1 ELSE 0 END), 0) AS occupied
             FROM wards w
             LEFT JOIN beds b ON b.ward_id = w.id
             GROUP BY w.id, w.name ORDER BY w.name'
        )->fetchAll();

        return [
            'names'    => array_column($wards, 'name'),
            'occupied' => array_map('intval', array_column($wards, 'occupied')),
            'free'     => array_map(static function ($w) {
                return (int) $w['total_beds'] - (int) $w['occupied'];
            }, $wards),
        ];
    }

    private function appointmentsByMonth(string $from, string $to): array
    {
        $stmt = db()->prepare(
            'SELECT DATE_FORMAT(appointment_date, "%Y-%m") AS month, COUNT(*) AS c
             FROM appointments
             WHERE appointment_date >= ? AND appointment_date <= ?
             GROUP BY DATE_FORMAT(appointment_date, "%Y-%m") ORDER BY month'
        );
        $stmt->execute([$from, $to]);
        $rows = $stmt->fetchAll();
        return [
            'labels' => array_map(static function ($r) {
                return date('M Y', strtotime($r['month'] . '-01'));
            }, $rows),
            'values' => array_map('intval', array_column($rows, 'c')),
        ];
    }

    private function newPatientsBetween(string $from, string $to): int
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM users WHERE role = "patient" AND created_at >= ? AND created_at <= ?'
        );
        $stmt->execute([$from . ' 00:00:00', $to . ' 23:59:59']);
        return (int) $stmt->fetchColumn();
    }

    private function appointmentsBetween(string $from, string $to): int
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM appointments WHERE appointment_date >= ? AND appointment_date <= ?'
        );
        $stmt->execute([$from, $to]);
        return (int) $stmt->fetchColumn();
    }

    private function completedLabsBetween(string $from, string $to): int
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM lab_requests WHERE status = "completed" AND completed_at >= ? AND completed_at <= ?'
        );
        $stmt->execute([$from . ' 00:00:00', $to . ' 23:59:59']);
        return (int) $stmt->fetchColumn();
    }

    private function normalizeDate(string $date): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            return date('Y-m-d');
        }
        return $date;
    }
}