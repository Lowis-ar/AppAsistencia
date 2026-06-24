package com.pam.appasistencia.data.model

data class EmployeeRequest(
    val fullName: String,
    val carnet: String,
    val department: String,
    val residenceLat: Double?,
    val residenceLng: Double?
)
