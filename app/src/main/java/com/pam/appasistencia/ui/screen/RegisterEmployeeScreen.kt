package com.pam.appasistencia.ui.screen

import android.widget.Toast
import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import com.pam.appasistencia.ui.viewmodel.AdminState
import com.pam.appasistencia.ui.viewmodel.AdminViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun RegisterEmployeeScreen(
    onNavigateBack: () -> Unit,
    onNavigateToMapPicker: () -> Unit,
    selectedLat: Double?,
    selectedLng: Double?,
    viewModel: AdminViewModel = androidx.lifecycle.viewmodel.compose.viewModel()
) {
    var fullName by remember { mutableStateOf("") }
    var zone by remember { mutableStateOf("") }
    var department by remember { mutableStateOf("") }

    val adminState by viewModel.adminState.collectAsState()
    val context = LocalContext.current

    LaunchedEffect(adminState) {
        when (adminState) {
            is AdminState.Success -> {
                Toast.makeText(context, (adminState as AdminState.Success).message, Toast.LENGTH_LONG).show()
                viewModel.resetState()
                onNavigateBack() // Go back after success
            }
            is AdminState.Error -> {
                Toast.makeText(context, (adminState as AdminState.Error).message, Toast.LENGTH_LONG).show()
                viewModel.resetState()
            }
            else -> {}
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Registrar Empleado") },
                navigationIcon = {
                    IconButton(onClick = onNavigateBack) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Volver")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.primary,
                    titleContentColor = MaterialTheme.colorScheme.onPrimary,
                    navigationIconContentColor = MaterialTheme.colorScheme.onPrimary
                )
            )
        }
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .padding(24.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            OutlinedTextField(
                value = fullName,
                onValueChange = { fullName = it },
                label = { Text("Nombre Completo") },
                modifier = Modifier.fillMaxWidth().padding(bottom = 16.dp),
                singleLine = true
            )

            OutlinedTextField(
                value = zone,
                onValueChange = { zone = it },
                label = { Text("Zona de Residencia") },
                modifier = Modifier.fillMaxWidth().padding(bottom = 16.dp),
                singleLine = true
            )

            OutlinedTextField(
                value = department,
                onValueChange = { department = it },
                label = { Text("Departamento / Área") },
                modifier = Modifier.fillMaxWidth().padding(bottom = 24.dp),
                singleLine = true
            )

            // Location picker
            Card(
                modifier = Modifier.fillMaxWidth().padding(bottom = 24.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant)
            ) {
                Row(
                    modifier = Modifier.padding(16.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(Icons.Default.LocationOn, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
                    Spacer(modifier = Modifier.width(16.dp))
                    Column(modifier = Modifier.weight(1f)) {
                        Text("Residencia (Opcional)", style = MaterialTheme.typography.labelLarge)
                        if (selectedLat != null && selectedLng != null) {
                            Text("Lat: ${"%.4f".format(selectedLat)}\nLng: ${"%.4f".format(selectedLng)}", style = MaterialTheme.typography.bodyMedium)
                        } else {
                            Text("No seleccionada", style = MaterialTheme.typography.bodyMedium)
                        }
                    }
                    Button(onClick = onNavigateToMapPicker) {
                        Text(if (selectedLat != null) "Cambiar" else "Seleccionar")
                    }
                }
            }

            Spacer(modifier = Modifier.weight(1f))

            Button(
                onClick = { viewModel.registerEmployee(fullName, department, zone, selectedLat, selectedLng) },
                modifier = Modifier.fillMaxWidth().height(50.dp),
                enabled = adminState != AdminState.Loading && fullName.isNotBlank() && zone.isNotBlank() && department.isNotBlank()
            ) {
                if (adminState == AdminState.Loading) {
                    CircularProgressIndicator(modifier = Modifier.size(24.dp), color = MaterialTheme.colorScheme.onPrimary)
                } else {
                    Text("Guardar Empleado")
                }
            }
        }
    }
}
