<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AttendanceApiController extends Controller
{
    /**
     * Códigos QR autorizados para registrar asistencia.
     * En producción, estos deberían estar en la base de datos (tabla 'locations').
     */
    private const AUTHORIZED_QR_CODES = [
        'HQ_MAIN_OFFICE',
        'SUCURSAL_NORTE',
        'SUCURSAL_SUR',
        'BODEGA_CENTRAL',
    ];

    // =========================================================================
    // POST /api/login
    // =========================================================================

    /**
     * Autentica al usuario y retorna un token de Sanctum.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Revocar tokens anteriores (un solo token activo por usuario)
        $user->tokens()->delete();

        $token = $user->createToken('mobile-app-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'data'    => [
                'user'  => [
                    'id'               => $user->id,
                    'name'             => $user->name,
                    'email'            => $user->email,
                    'role'             => $user->role,
                    'department'       => $user->department?->name,
                    'residential_zone' => $user->residential_zone,
                ],
                'token' => $token,
            ],
        ], 200);
    }

    // =========================================================================
    // POST /api/logout
    // =========================================================================

    /**
     * Revoca el token actual del usuario autenticado.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente.',
        ], 200);
    }

    // =========================================================================
    // GET /api/user
    // =========================================================================

    /**
     * Retorna los datos del usuario autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('department');

        return response()->json([
            'success' => true,
            'data' => [
                'id'               => $user->id,
                'name'             => $user->name,
                'email'            => $user->email,
                'role'             => $user->role,
                'department'       => $user->department?->name,
                'residential_zone' => $user->residential_zone,
            ],
        ]);
    }

    // =========================================================================
    // POST /api/attendance/register
    // =========================================================================

    /**
     * Registra una asistencia (check_in o check_out).
     *
     * Payload esperado:
     * {
     *   "qr_code":  "HQ_MAIN_OFFICE",
     *   "type":     "check_in" | "check_out",
     *   "latitude":  14.0818,
     *   "longitude": -87.2068
     * }
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code'   => 'required|string',
            'type'      => 'required|in:check_in,check_out',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        // 1. Validar QR
        if (! in_array($request->qr_code, self::AUTHORIZED_QR_CODES)) {
            return response()->json([
                'success' => false,
                'message' => 'Código QR no autorizado. Asegúrese de escanear el código del lugar correcto.',
            ], 403);
        }

        $user = $request->user();
        $today = now()->toDateString();
        $nowTime = now()->toTimeString();

        // 2. Validar lógica check_in / check_out
        $lastRecord = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->orderBy('time', 'desc')
            ->first();

        if ($request->type === 'check_in') {
            // No se puede hacer check_in si ya hay uno activo (sin check_out)
            if ($lastRecord && $lastRecord->type === 'check_in') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes una entrada registrada hoy. Primero registra tu salida.',
                ], 409);
            }
        }

        if ($request->type === 'check_out') {
            // No se puede hacer check_out sin un check_in previo
            if (! $lastRecord || $lastRecord->type === 'check_out') {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay una entrada registrada hoy. Primero registra tu entrada.',
                ], 409);
            }
        }

        // 3. Registrar asistencia
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'type'      => $request->type,
            'date'      => $today,
            'time'      => $nowTime,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'qr_code'   => $request->qr_code,
        ]);

        $typeLabel = $request->type === 'check_in' ? 'entrada' : 'salida';

        return response()->json([
            'success' => true,
            'message' => "Tu {$typeLabel} ha sido registrada correctamente.",
            'data'    => [
                'id'        => $attendance->id,
                'type'      => $attendance->type,
                'date'      => $attendance->date->format('Y-m-d'),
                'time'      => $attendance->time,
                'location'  => $attendance->google_maps_url,
                'qr_code'   => $attendance->qr_code,
            ],
        ], 201);
    }

    // =========================================================================
    // GET /api/attendance/status
    // =========================================================================

    /**
     * Retorna el estado actual del usuario (si está dentro o fuera).
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = now()->toDateString();

        $lastRecord = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->orderBy('time', 'desc')
            ->first();

        $isCheckedIn = $lastRecord && $lastRecord->type === 'check_in';

        return response()->json([
            'success' => true,
            'data'    => [
                'is_checked_in' => $isCheckedIn,
                'last_record'   => $lastRecord ? [
                    'type' => $lastRecord->type,
                    'time' => $lastRecord->time,
                    'date' => $lastRecord->date->format('Y-m-d'),
                ] : null,
            ],
        ]);
    }
}
