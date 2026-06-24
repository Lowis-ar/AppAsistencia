package com.pam.appasistencia

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import com.pam.appasistencia.ui.screen.*
import com.pam.appasistencia.ui.theme.AppAsistenciaTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContent {
            AppAsistenciaTheme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    AsistenciaApp()
                }
            }
        }
    }
}

@Composable
fun AsistenciaApp() {
    val navController = rememberNavController()

    // We hold the selected coordinates here so they can be shared between the Map and the Register screens
    var tempLat by remember { mutableStateOf<Double?>(null) }
    var tempLng by remember { mutableStateOf<Double?>(null) }
    var tempAddress by remember { mutableStateOf<String?>(null) }

    NavHost(navController = navController, startDestination = "main") {
        composable("main") {
            MainScreen(
                onNavigateToLogin = { navController.navigate("login") }
            )
        }
        
        composable("login") {
            LoginScreen(
                onLoginSuccess = { 
                    navController.navigate("admin") {
                        popUpTo("main") // Optional: depending on if you want back to go to main
                    } 
                },
                onNavigateBack = { navController.popBackStack() }
            )
        }
        
        composable("admin") {
            AdminDashboardScreen(
                onNavigateToRegisterEmployee = { 
                    // reset temp coords when navigating to register
                    tempLat = null
                    tempLng = null
                    tempAddress = null
                    navController.navigate("register") 
                },
                onLogout = { 
                    navController.navigate("main") {
                        popUpTo(0) // Clear back stack
                    }
                }
            )
        }
        
        composable("register") {
            RegisterEmployeeScreen(
                onNavigateBack = { navController.popBackStack() },
                onNavigateToMapPicker = { navController.navigate("map") },
                selectedLat = tempLat,
                selectedLng = tempLng,
                selectedAddress = tempAddress
            )
        }

        composable("map") {
            MapPickerScreen(
                onNavigateBack = { navController.popBackStack() },
                onLocationSelected = { lat, lng, address ->
                    tempLat = lat
                    tempLng = lng
                    tempAddress = address
                    navController.popBackStack() // Go back to register screen
                }
            )
        }
    }
}