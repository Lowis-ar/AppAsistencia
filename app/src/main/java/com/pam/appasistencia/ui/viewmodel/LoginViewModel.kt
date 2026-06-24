package com.pam.appasistencia.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.pam.appasistencia.data.TokenManager
import com.pam.appasistencia.data.api.AsistenciaApiService
import com.pam.appasistencia.data.model.LoginRequest
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

sealed class LoginState {
    object Idle : LoginState()
    object Loading : LoginState()
    object Success : LoginState()
    data class Error(val message: String) : LoginState()
}

class LoginViewModel : ViewModel() {
    private val apiService = AsistenciaApiService.create()

    private val _loginState = MutableStateFlow<LoginState>(LoginState.Idle)
    val loginState: StateFlow<LoginState> = _loginState

    fun login(username: String, pass: String) {
        viewModelScope.launch {
            _loginState.value = LoginState.Loading
            try {
                val response = apiService.login(LoginRequest(username, pass))
                if (response.success && response.token != null) {
                    TokenManager.token = response.token
                    _loginState.value = LoginState.Success
                } else {
                    _loginState.value = LoginState.Error(response.message ?: "Error desconocido")
                }
            } catch (e: Exception) {
                _loginState.value = LoginState.Error("Error de conexión: ${e.message}")
            }
        }
    }
    
    fun resetState() {
        _loginState.value = LoginState.Idle
    }
}
