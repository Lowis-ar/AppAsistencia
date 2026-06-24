package com.pam.appasistencia.ui.screen

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.List
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AdminDashboardScreen(
    onNavigateToRegisterEmployee: () -> Unit,
    onLogout: () -> Unit
) {
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Panel de Administración") },
                actions = {
                    TextButton(onClick = onLogout) {
                        Text("Cerrar Sesión", color = MaterialTheme.colorScheme.onPrimary)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.primary,
                    titleContentColor = MaterialTheme.colorScheme.onPrimary
                )
            )
        }
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .padding(24.dp),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            ElevatedButton(
                onClick = onNavigateToRegisterEmployee,
                modifier = Modifier.fillMaxWidth().height(80.dp),
                shape = MaterialTheme.shapes.medium
            ) {
                Icon(Icons.Default.Add, contentDescription = null, modifier = Modifier.padding(end = 8.dp))
                Text("Registrar Nuevo Empleado")
            }

            Spacer(modifier = Modifier.height(24.dp))

            // En el futuro: botón para ver reportes
            ElevatedButton(
                onClick = { /* TODO: Pantalla de reportes */ },
                modifier = Modifier.fillMaxWidth().height(80.dp),
                shape = MaterialTheme.shapes.medium,
                enabled = false // Disabled for now, as requested focusing on core mobile app functionality
            ) {
                Icon(Icons.Default.List, contentDescription = null, modifier = Modifier.padding(end = 8.dp))
                Text("Ver Reporte de Asistencia (Web)")
            }
        }
    }
}
