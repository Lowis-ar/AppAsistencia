package com.pam.appasistencia.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.pam.appasistencia.data.TokenManager
import com.pam.appasistencia.data.api.AsistenciaApiService
import com.pam.appasistencia.data.model.EmployeeRequest
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import org.json.JSONObject

sealed class AdminState {
    object Idle : AdminState()
    object Loading : AdminState()
    data class Success(val message: String) : AdminState()
    data class Error(val message: String) : AdminState()
}

class AdminViewModel : ViewModel() {
    private val apiService = AsistenciaApiService.create()

    private val _adminState = MutableStateFlow<AdminState>(AdminState.Idle)
    val adminState: StateFlow<AdminState> = _adminState

    fun registerEmployee(fullName: String, department: String, zone: String, lat: Double?, lng: Double?) {
        val token = TokenManager.token
        if (token == null) {
            _adminState.value = AdminState.Error("No estás autenticado")
            return
        }

        viewModelScope.launch {
            _adminState.value = AdminState.Loading
            try {
                val request = EmployeeRequest(fullName, department, zone, lat, lng)
                val response = apiService.registerEmployee("Bearer $token", request)
                
                if (response.isSuccessful) {
                    val body = response.body()?.toString()
                    _adminState.value = AdminState.Success("Empleado registrado exitosamente.")
                } else {
                    val errorBody = response.errorBody()?.string()
                    val errorMsg = try {
                        JSONObject(errorBody ?: "").getString("message")
                    } catch (e: Exception) {
                        "Error al registrar empleado"
                    }
                    _adminState.value = AdminState.Error(errorMsg)
                }
            } catch (e: Exception) {
                _adminState.value = AdminState.Error("Error de red: ${e.message}")
            }
        }
    }

    fun resetState() {
        _adminState.value = AdminState.Idle
    }
}
