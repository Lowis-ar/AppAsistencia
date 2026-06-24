package com.pam.appasistencia.ui.screen

import android.Manifest
import android.content.pm.PackageManager
import android.widget.Toast
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.layout.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.core.content.ContextCompat
import com.journeyapps.barcodescanner.ScanContract
import com.journeyapps.barcodescanner.ScanOptions
import com.pam.appasistencia.ui.viewmodel.AttendanceState
import com.pam.appasistencia.ui.viewmodel.AttendanceViewModel
import com.pam.appasistencia.util.LocationHelper
import kotlinx.coroutines.launch

@Composable
fun MainScreen(
    onNavigateToLogin: () -> Unit,
    viewModel: AttendanceViewModel = androidx.lifecycle.viewmodel.compose.viewModel()
) {
    val context = LocalContext.current
    val coroutineScope = rememberCoroutineScope()
    val attendanceState by viewModel.attendanceState.collectAsState()
    
    val locationHelper = remember { LocationHelper(context) }

    // QR Scanner Launcher
    val scanLauncher = rememberLauncherForActivityResult(ScanContract()) { result ->
        if (result.contents != null) {
            val carnet = result.contents
            
            // Get location and register
            coroutineScope.launch {
                val location = locationHelper.getCurrentLocation()
                if (location != null) {
                    viewModel.registerAttendance(carnet, location.latitude, location.longitude)
                } else {
                    Toast.makeText(context, "No se pudo obtener la ubicación GPS", Toast.LENGTH_LONG).show()
                }
            }
        }
    }

    // Permission Launchers
    val locationPermissionLauncher = rememberLauncherForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { permissions ->
        val fineLocationGranted = permissions[Manifest.permission.ACCESS_FINE_LOCATION] ?: false
        val coarseLocationGranted = permissions[Manifest.permission.ACCESS_COARSE_LOCATION] ?: false
        
        if (fineLocationGranted || coarseLocationGranted) {
            // Location granted, launch QR scanner (camera permission will be handled by ZXing, but we can pre-request it)
            val options = ScanOptions()
            options.setDesiredBarcodeFormats(ScanOptions.QR_CODE)
            options.setPrompt("Escanea tu carnet QR")
            options.setBeepEnabled(true)
            scanLauncher.launch(options)
        } else {
            Toast.makeText(context, "Se requieren permisos de ubicación", Toast.LENGTH_LONG).show()
        }
    }

    // Handle State changes
    LaunchedEffect(attendanceState) {
        when (attendanceState) {
            is AttendanceState.Success -> {
                Toast.makeText(context, (attendanceState as AttendanceState.Success).message, Toast.LENGTH_LONG).show()
                viewModel.resetState()
            }
            is AttendanceState.Error -> {
                Toast.makeText(context, (attendanceState as AttendanceState.Error).message, Toast.LENGTH_LONG).show()
                viewModel.resetState()
            }
            else -> {}
        }
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp)
    ) {
        // Main button centered
        Column(
            modifier = Modifier.align(Alignment.Center),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Text(
                text = "Control de Asistencia",
                fontSize = 28.sp,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.primary,
                modifier = Modifier.padding(bottom = 48.dp)
            )

            Button(
                onClick = {
                    // Check location permissions first
                    val hasLocationPermission = ContextCompat.checkSelfPermission(
                        context,
                        Manifest.permission.ACCESS_FINE_LOCATION
                    ) == PackageManager.PERMISSION_GRANTED
                    
                    if (hasLocationPermission) {
                        val options = ScanOptions()
                        options.setDesiredBarcodeFormats(ScanOptions.QR_CODE)
                        options.setPrompt("Escanea tu carnet QR")
                        options.setBeepEnabled(true)
                        options.setOrientationLocked(false)
                        scanLauncher.launch(options)
                    } else {
                        locationPermissionLauncher.launch(
                            arrayOf(
                                Manifest.permission.ACCESS_FINE_LOCATION,
                                Manifest.permission.ACCESS_COARSE_LOCATION
                            )
                        )
                    }
                },
                modifier = Modifier
                    .fillMaxWidth()
                    .height(80.dp),
                shape = MaterialTheme.shapes.large
            ) {
                if (attendanceState == AttendanceState.Loading) {
                    CircularProgressIndicator(color = MaterialTheme.colorScheme.onPrimary)
                } else {
                    Text("Registrar Asistencia", fontSize = 22.sp)
                }
            }
        }

        // Admin login button at bottom
        TextButton(
            onClick = onNavigateToLogin,
            modifier = Modifier.align(Alignment.BottomCenter)
        ) {
            Text("Ingreso Administrador")
        }
    }
}
