<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // =========================================================
        // 1. Métricas de horas trabajadas (hoy / semana / mes)
        // =========================================================
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $hoursToday   = $this->calculateHours($today, $today);
        $hoursWeek    = $this->calculateHours($weekStart, $today);
        $hoursMonth   = $this->calculateHours($monthStart, $today);

        // Sparkline data: últimos 7 días
        $sparklineData = $this->getSparklineData(7);

        // =========================================================
        // 2. Tabla de registros con filtros
        // =========================================================
        $query = Attendance::with(['user.department'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc');

        // Filtro: búsqueda por nombre o ID
        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        // Filtro: rango de fechas
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('date', '<=', $dateTo);
        }

        // Filtro: zona residencial
        if ($zone = $request->input('zone')) {
            $query->whereHas('user', function ($q) use ($zone) {
                $q->where('residential_zone', 'like', "%{$zone}%");
            });
        }

        // Filtro: tipo (check_in / check_out)
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $attendances = $query->paginate(20)->withQueryString();

        // =========================================================
        // 3. Datos para filtros
        // =========================================================
        $zones = User::whereNotNull('residential_zone')
            ->distinct()
            ->pluck('residential_zone');

        // =========================================================
        // 4. Contadores de tarjetas
        // =========================================================
        $totalEmployees = User::where('role', 'employee')->count();
        $todayCheckins  = Attendance::where('date', $today)->where('type', 'check_in')->count();

        return view('dashboard', compact(
            'hoursToday',
            'hoursWeek',
            'hoursMonth',
            'sparklineData',
            'attendances',
            'zones',
            'totalEmployees',
            'todayCheckins'
        ));
    }

    // =========================================================
    // Calcula las horas totales trabajadas en un rango de fechas
    // sumando los pares check_in / check_out de cada usuario.
    // =========================================================
    private function calculateHours(string $from, string $to): float
    {
        // Obtenemos todos los registros del rango ordenados por usuario, fecha y hora
        $records = Attendance::whereBetween('date', [$from, $to])
            ->orderBy('user_id')
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $totalMinutes = 0;
        $pendingCheckIn = null;

        foreach ($records as $record) {
            // Si cambia el usuario o el día, resetear el check_in pendiente
            if ($pendingCheckIn &&
                ($pendingCheckIn->user_id !== $record->user_id || $pendingCheckIn->date->format('Y-m-d') !== $record->date->format('Y-m-d'))) {
                $pendingCheckIn = null;
            }

            if ($record->type === 'check_in') {
                $pendingCheckIn = $record;
            } elseif ($record->type === 'check_out' && $pendingCheckIn) {
                $checkInTime  = Carbon::parse($pendingCheckIn->date->format('Y-m-d') . ' ' . $pendingCheckIn->time);
                $checkOutTime = Carbon::parse($record->date->format('Y-m-d') . ' ' . $record->time);
                $totalMinutes += $checkInTime->diffInMinutes($checkOutTime);
                $pendingCheckIn = null;
            }
        }

        return round($totalMinutes / 60, 1);
    }

    // =========================================================
    // Devuelve datos de horas por día para el sparkline
    // =========================================================
    private function getSparklineData(int $days = 7): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $hours = $this->calculateHours($date, $date);
            $data[] = [
                'date'  => now()->subDays($i)->format('d/m'),
                'hours' => $hours,
            ];
        }
        return $data;
    }
}
