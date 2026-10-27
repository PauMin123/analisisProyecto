<?php
/**
 * ============================================================================
 * MODELO: Horario (MVC: Capa del Modelo)
 * Gestiona slots de disponibilidad, bloqueo pesimista y control de concurrencia
 * ============================================================================
 */

require_once __DIR__ . '/../api/config.php';

class Horario {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? DatabaseConnection::getInstance()->getConnection();
    }

    /**
     * Retorna fechas con slots disponibles para un profesor
     */
    public function getAvailableDates(int $profesorId): array {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT fecha
            FROM horarios_disponibles
            WHERE profesor_id = ? AND fecha >= CURDATE()
            ORDER BY fecha ASC
            LIMIT 14
        ");
        $stmt->execute([$profesorId]);
        $rows = $stmt->fetchAll() ?: [];

        $diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $dates = [];
        foreach ($rows as $r) {
            $f = $r['fecha'];
            $timestamp = strtotime($f);
            $numDiaSemana = (int)date('w', $timestamp);
            $diaMes = (int)date('j', $timestamp);
            $numMes = (int)date('n', $timestamp) - 1;

            $dates[] = [
                'fecha' => $f,
                'dia_semana' => $diasSemana[$numDiaSemana],
                'dia_numero' => $diaMes,
                'mes_nombre' => $meses[$numMes],
                'es_hoy' => ($f === date('Y-m-d'))
            ];
        }

        // Si no hay fechas futuras generadas en la base de datos, construir los próximos 7 días
        if (empty($dates)) {
            for ($i = 0; $i < 7; $i++) {
                $time = strtotime("+{$i} days");
                $f = date('Y-m-d', $time);
                $w = (int)date('w', $time);
                $m = (int)date('n', $time) - 1;
                $dates[] = [
                    'fecha' => $f,
                    'dia_semana' => $diasSemana[$w],
                    'dia_numero' => (int)date('j', $time),
                    'mes_nombre' => $meses[$m],
                    'es_hoy' => ($i === 0)
                ];
            }
        }

        return $dates;
    }

    /**
     * Retorna slots horarios categorizados por turno (mañana, tarde, noche)
     */
    public function getSlotsByDate(int $profesorId, string $fecha): array {
        $stmt = $this->pdo->prepare("
            SELECT id, profesor_id, fecha, hora_inicio, hora_fin, estado, version
            FROM horarios_disponibles
            WHERE profesor_id = ? AND fecha = ?
            ORDER BY hora_inicio ASC
        ");
        $stmt->execute([$profesorId, $fecha]);
        $rawSlots = $stmt->fetchAll() ?: [];

        return array_map(function($s) {
            $hInicio = substr($s['hora_inicio'], 0, 5);
            $hFin = substr($s['hora_fin'], 0, 5);
            $horaInt = (int)substr($s['hora_inicio'], 0, 2);

            $turno = 'morning';
            if ($horaInt >= 12 && $horaInt < 18) {
                $turno = 'afternoon';
            } elseif ($horaInt >= 18) {
                $turno = 'evening';
            }

            return [
                'id' => (int)$s['id'],
                'hora_inicio' => $hInicio,
                'hora_fin' => $hFin,
                'hora_rango' => "{$hInicio} - {$hFin}",
                'turno' => $turno,
                'estado' => $s['estado'],
                'disponible' => ($s['estado'] === 'DISPONIBLE'),
                'version' => (int)$s['version']
            ];
        }, $rawSlots);
    }

    /**
     * Bloqueo pesimista atómico con SELECT ... FOR UPDATE (M01-18)
     */
    public function lockSlotForUpdate(int $horarioId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, profesor_id, fecha, hora_inicio, hora_fin, estado, version 
            FROM horarios_disponibles 
            WHERE id = ? 
            FOR UPDATE
        ");
        $stmt->execute([$horarioId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Marca el horario como RESERVADO de forma atómica
     */
    public function reserveSlot(int $horarioId): bool {
        $stmt = $this->pdo->prepare("
            UPDATE horarios_disponibles 
            SET estado = 'RESERVADO', version = version + 1, bloqueado_hasta = NULL, bloqueado_por_estudiante_id = NULL
            WHERE id = ? AND estado = 'DISPONIBLE'
        ");
        $stmt->execute([$horarioId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Libera el horario devolviéndolo a estado DISPONIBLE
     */
    public function releaseSlot(int $horarioId): bool {
        $stmt = $this->pdo->prepare("
            UPDATE horarios_disponibles
            SET estado = 'DISPONIBLE', version = version + 1, bloqueado_hasta = NULL, bloqueado_por_estudiante_id = NULL
            WHERE id = ?
        ");
        return $stmt->execute([$horarioId]);
    }
}
