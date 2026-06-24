package com.pam.appasistencia.data.model

data class AttendanceRequest(
    val carnet: String,
    val checkTime: String,
    val latitude: Double,
    val longitude: Double
)
