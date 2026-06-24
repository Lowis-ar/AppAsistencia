package com.pam.appasistencia.ui.screen

import android.location.Geocoder
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Check
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import com.google.android.gms.maps.model.CameraPosition
import com.google.android.gms.maps.model.LatLng
import com.google.maps.android.compose.*
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.util.Locale

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MapPickerScreen(
    onLocationSelected: (Double, Double, String) -> Unit,
    onNavigateBack: () -> Unit
) {
    // Default location (e.g. city center)
    val defaultLocation = LatLng(-13.6929, -89.2182) // San Salvador, El Salvador
    val cameraPositionState = rememberCameraPositionState {
        position = CameraPosition.fromLatLngZoom(defaultLocation, 12f)
    }

    var selectedLocation by remember { mutableStateOf<LatLng?>(null) }
    val coroutineScope = rememberCoroutineScope()
    val context = LocalContext.current

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Seleccionar Residencia") },
                navigationIcon = {
                    IconButton(onClick = onNavigateBack) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Volver")
                    }
                },
                actions = {
                    if (selectedLocation != null) {
                        IconButton(onClick = { 
                            val lat = selectedLocation!!.latitude
                            val lng = selectedLocation!!.longitude
                            coroutineScope.launch {
                                val addressText = withContext(Dispatchers.IO) {
                                    try {
                                        val geocoder = Geocoder(context, Locale.getDefault())
                                        val addresses = geocoder.getFromLocation(lat, lng, 1)
                                        if (!addresses.isNullOrEmpty()) {
                                            val address = addresses[0]
                                            val street = address.thoroughfare ?: ""
                                            val neighborhood = address.subLocality ?: address.subAdminArea ?: ""
                                            val city = address.locality ?: ""
                                            
                                            val sb = StringBuilder()
                                            if (street.isNotEmpty()) sb.append(street)
                                            if (neighborhood.isNotEmpty()) {
                                                if (sb.isNotEmpty()) sb.append(", ")
                                                sb.append(neighborhood)
                                            }
                                            if (city.isNotEmpty()) {
                                                if (sb.isNotEmpty()) sb.append(", ")
                                                sb.append(city)
                                            }
                                            if (sb.isNotEmpty()) sb.toString() else address.getAddressLine(0) ?: "Lat: $lat, Lng: $lng"
                                        } else {
                                            "Lat: $lat, Lng: $lng"
                                        }
                                    } catch (e: Exception) {
                                        "Lat: $lat, Lng: $lng"
                                    }
                                }
                                onLocationSelected(lat, lng, addressText)
                            }
                        }) {
                            Icon(Icons.Default.Check, contentDescription = "Confirmar")
                        }
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.primary,
                    titleContentColor = MaterialTheme.colorScheme.onPrimary,
                    navigationIconContentColor = MaterialTheme.colorScheme.onPrimary,
                    actionIconContentColor = MaterialTheme.colorScheme.onPrimary
                )
            )
        }
    ) { padding ->
        Box(modifier = Modifier.fillMaxSize().padding(padding)) {
            GoogleMap(
                modifier = Modifier.fillMaxSize(),
                cameraPositionState = cameraPositionState,
                onMapClick = { latLng ->
                    selectedLocation = latLng
                },
                uiSettings = MapUiSettings(zoomControlsEnabled = true)
            ) {
                selectedLocation?.let {
                    Marker(
                        state = MarkerState(position = it),
                        title = "Residencia Seleccionada",
                        snippet = "Haz clic en el check para confirmar"
                    )
                }
            }
        }
    }
}
