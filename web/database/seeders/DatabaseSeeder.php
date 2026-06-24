<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Departamentos
        $departments = [
            ['name' => 'Informática'],
            ['name' => 'Contabilidad'],
            ['name' => 'Recursos Humanos'],
            ['name' => 'Ventas'],
            ['name' => 'Administración'],
            ['name' => 'Producción'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['name' => $dept['name']]);
        }

        $infoDept = Department::where('name', 'Informática')->first();
        $contDept = Department::where('name', 'Contabilidad')->first();
        $rrhDept  = Department::where('name', 'Recursos Humanos')->first();
        $venDept  = Department::where('name', 'Ventas')->first();
        $admDept  = Department::where('name', 'Administración')->first();
        $prodDept = Department::where('name', 'Producción')->first();

        // 2. Usuario Administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@asistencia.com'],
            [
                'name'             => 'Administrador Principal',
                'password'         => Hash::make('admin123'),
                'role'             => 'admin',
                'department_id'    => $infoDept->id,
                'residential_zone' => 'Zona Central',
            ]
        );

        // 3. Empleados de ejemplo
        $employees = [
            ['name' => 'Carlos Martínez',  'email' => 'carlos@asistencia.com',  'dept' => $infoDept,  'zone' => 'Zona Norte'],
            ['name' => 'Ana López',         'email' => 'ana@asistencia.com',     'dept' => $rrhDept,  'zone' => 'Zona Sur'],
            ['name' => 'Roberto Fuentes',   'email' => 'roberto@asistencia.com', 'dept' => $venDept,  'zone' => 'Zona Este'],
            ['name' => 'María Hernández',   'email' => 'maria@asistencia.com',   'dept' => $prodDept,  'zone' => 'Zona Oeste'],
            ['name' => 'Luis García',       'email' => 'luis@asistencia.com',    'dept' => $infoDept,  'zone' => 'Zona Central'],
            ['name' => 'Sofía Ramírez',     'email' => 'sofia@asistencia.com',   'dept' => $venDept,  'zone' => 'Zona Norte'],
            ['name' => 'Diego Torres',      'email' => 'diego@asistencia.com',   'dept' => $rrhDept,  'zone' => 'Zona Sur'],
            ['name' => 'Valentina Reyes',   'email' => 'valentina@asistencia.com','dept' => $admDept, 'zone' => 'Zona Este'],
        ];

        $createdEmployees = [];
        foreach ($employees as $emp) {
            $user = User::firstOrCreate(
                ['email' => $emp['email']],
                [
                    'name'             => $emp['name'],
                    'password'         => Hash::make('password'),
                    'role'             => 'employee',
                    'department_id'    => $emp['dept']->id,
                    'residential_zone' => $emp['zone'],
                ]
            );
            $createdEmployees[] = $user;
        }

        // 4. Registros de asistencia para los últimos 15 días
        $qrCodes = ['HQ_MAIN_OFFICE', 'SUCURSAL_NORTE', 'SUCURSAL_SUR'];
        $latBase = 14.0818;
        $lngBase = -87.2068;

        foreach ($createdEmployees as $employee) {
            for ($dayOffset = 14; $dayOffset >= 0; $dayOffset--) {
                $date = now()->subDays($dayOffset)->toDateString();

                // No registrar en fines de semana
                $dayOfWeek = now()->subDays($dayOffset)->dayOfWeek;
                if ($dayOfWeek === 0 || $dayOfWeek === 6) continue;

                // Variar ligeramente las horas de entrada (7:50 - 8:15 AM)
                $checkInHour   = 8;
                $checkInMinute = rand(0, 15) - rand(0, 10);
                if ($checkInMinute < 0) { $checkInHour = 7; $checkInMinute += 60; }

                // Hora de salida (5:00 - 5:30 PM)
                $checkOutHour   = 17;
                $checkOutMinute = rand(0, 30);

                $qrCode = $qrCodes[array_rand($qrCodes)];
                $lat    = $latBase + (rand(-50, 50) / 10000);
                $lng    = $lngBase + (rand(-50, 50) / 10000);

                // Check In
                Attendance::firstOrCreate(
                    [
                        'user_id' => $employee->id,
                        'date'    => $date,
                        'type'    => 'check_in',
                    ],
                    [
                        'time'      => sprintf('%02d:%02d:00', $checkInHour, abs($checkInMinute)),
                        'latitude'  => $lat,
                        'longitude' => $lng,
                        'qr_code'   => $qrCode,
                    ]
                );

                // Check Out (solo si no es hoy)
                if ($dayOffset > 0) {
                    Attendance::firstOrCreate(
                        [
                            'user_id' => $employee->id,
                            'date'    => $date,
                            'type'    => 'check_out',
                        ],
                        [
                            'time'      => sprintf('%02d:%02d:00', $checkOutHour, $checkOutMinute),
                            'latitude'  => $lat,
                            'longitude' => $lng,
                            'qr_code'   => $qrCode,
                        ]
                    );
                }
            }
        }

        $this->command->info('✅ Seeder completado:');
        $this->command->info('   👤 Admin: admin@asistencia.com / password');
        $this->command->info('   👥 Empleados: [nombre]@asistencia.com / password');
        $this->command->info('   📅 ' . count($createdEmployees) . ' empleados con registros de los últimos 15 días.');
    }
}
