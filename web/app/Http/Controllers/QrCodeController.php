<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Muestra la lista de empleados con sus respectivos códigos QR.
     */
    public function index()
    {
        // Obtenemos solo los empleados, asumiendo que los admins no registran asistencia con QR
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        return view('qrcodes', compact('employees'));
    }
}
