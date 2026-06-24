package com.pam.appasistencia.data

/**
 * Simple in-memory token holder for admin authentication.
 * Persists the JWT token between navigation destinations.
 */
object TokenManager {
    var token: String? = null
}
