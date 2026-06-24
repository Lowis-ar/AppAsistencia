package com.pam.appasistencia.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.pam.appasistencia.data.api.AsistenciaApiService
import com.pam.appasistencia.data.model.AttendanceRequest
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import org.json.JSONObject
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

sealed class AttendanceState {
    object Idle : AttendanceState()
    object Loading : AttendanceState()
    data class Success(val message: String) : AttendanceState()
    data class Error(val message: String) : AttendanceState()
}

class AttendanceViewModel : ViewModel() {
    private val apiService = AsistenciaApiService.create()

    private val _attendanceState = MutableStateFlow<AttendanceState>(AttendanceState.Idle)
    val attendanceState: StateFlow<AttendanceState> = _attendanceState

    fun registerAttendance(carnet: String, lat: Double, lng: Double) {
        viewModelScope.launch {
            _attendanceState.value = AttendanceState.Loading
            try {
                // ISO 8601 format for PostgreSQL
                val sdf = SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss.SSS'Z'", Locale.getDefault())
                val checkTime = sdf.format(Date())

                val request = AttendanceRequest(carnet, checkTime, lat, lng)
                val response = apiService.registerAttendance(request)
                
                if (response.isSuccessful) {
                    val responseBody = response.body()
                    _attendanceState.value = AttendanceState.Success("Asistencia registrada con éxito")
                } else {
                    val errorBody = response.errorBody()?.string()
                    val errorMsg = try {
                        JSONObject(errorBody ?: "").getString("message")
                    } catch (e: Exception) {
                        "Error al registrar asistencia"
                    }
                    _attendanceState.value = AttendanceState.Error(errorMsg)
                }
            } catch (e: Exception) {
                _attendanceState.value = AttendanceState.Error("Error de red: ${e.message}")
            }
        }
    }

    fun resetState() {
        _attendanceState.value = AttendanceState.Idle
    }
}
