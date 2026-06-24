package com.pam.appasistencia.data.model

data class EmployeeRequest(
    val fullName: String,
    val department: String,
    val zone: String,
    val residenceLat: Double?,
    val residenceLng: Double?
)
