<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AttendanceApiController extends Controller
{
    // =========================================================================
    // POST /api/auth/login
    // =========================================================================
    public function login(Request $request): JsonResponse
    {
        // En este modelo, el Administrador usa su 'username' o 'email' para iniciar sesión
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->username)
                    ->orWhere('name', $request->username)
                    ->first();

        // Validamos que sea admin
        if (! $user || ! Hash::check($request->password, $user->password) || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas o no tienes permisos de administrador.',
            ], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'token'   => $token,
        ], 200);
    }

    // =========================================================================
    // POST /api/employees
    // =========================================================================
    public function registerEmployee(Request $request): JsonResponse
    {
        $request->validate([
            'fullName'     => 'required|string',
            'department'   => 'required|string',
            'zone'         => 'required|string',
            'residenceLat' => 'nullable|numeric',
            'residenceLng' => 'nullable|numeric',
        ]);

        // Buscamos o creamos el departamento
        $department = Department::firstOrCreate(['name' => $request->department]);

        // Creamos al empleado
        $employee = User::create([
            'name'             => $request->fullName,
            'email'            => strtolower(str_replace(' ', '.', $request->fullName)) . rand(10,99) . '@asistencia.com',
            'password'         => Hash::make('12345678'), // default pass
            'role'             => 'employee',
            'department_id'    => $department->id,
            'residential_zone' => $request->zone,
            'latitude'         => $request->residenceLat,
            'longitude'        => $request->residenceLng,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Empleado registrado exitosamente.',
            'data'    => [
                'id' => $employee->id,
            ]
        ], 201);
    }

    // =========================================================================
    // GET /api/employees
    // =========================================================================
    public function listEmployees(Request $request): JsonResponse
    {
        $employees = User::with('department')->where('role', 'employee')->get();
        return response()->json([
            'success' => true,
            'data'    => $employees
        ]);
    }

    // =========================================================================
    // POST /api/attendance
    // =========================================================================
    public function registerAttendance(Request $request): JsonResponse
    {
        // La App manda 'carnet' (que ahora es el user ID o USER_QR_<ID>), 'checkTime', 'latitude', 'longitude'
        $request->validate([
            'carnet'    => 'required|string', // QR content
            'checkTime' => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Extraer ID si tiene prefijo de la web (USER_QR_)
        $carnet = $request->carnet;
        if (str_starts_with($carnet, 'USER_QR_')) {
            $carnet = substr($carnet, strlen('USER_QR_'));
        }

        if (!is_numeric($carnet)) {
            return response()->json([
                'success' => false,
                'message' => 'El código QR escaneado no es válido (debe ser el ID numérico del empleado o formato USER_QR_<ID>).',
            ], 400);
        }

        $user = User::find($carnet);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado con el código escaneado.',
            ], 404);
        }

        $today = now()->toDateString();
        $nowTime = now()->toTimeString();

        // Determinar si es Entrada o Salida
        $lastRecord = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->orderBy('time', 'desc')
            ->first();

        // Si el último registro fue 'check_in', el siguiente es 'check_out'
        $type = ($lastRecord && $lastRecord->type === 'check_in') ? 'check_out' : 'check_in';

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'type'      => $type,
            'date'      => $today,
            'time'      => $nowTime,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'qr_code'   => $request->carnet, // el QR escaneado (ID)
        ]);

        $typeLabel = $type === 'check_in' ? 'Entrada' : 'Salida';

        return response()->json([
            'success' => true,
            'message' => "{$typeLabel} registrada con éxito — {$user->name}",
            'data'    => $attendance
        ], 201);
    }
}
