package com.pam.appasistencia.data.api

import com.pam.appasistencia.data.model.AttendanceRequest
import com.pam.appasistencia.data.model.EmployeeRequest
import com.pam.appasistencia.data.model.LoginRequest
import com.pam.appasistencia.data.model.LoginResponse
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Header
import retrofit2.http.POST

interface AsistenciaApiService {

    @POST("api/auth/login")
    suspend fun login(@Body request: LoginRequest): LoginResponse

    @POST("api/employees")
    suspend fun registerEmployee(
        @Header("Authorization") token: String,
        @Body request: EmployeeRequest
    ): retrofit2.Response<Any> // Returning raw response to handle status codes

    @GET("api/employees")
    suspend fun getEmployees(
        @Header("Authorization") token: String
    ): retrofit2.Response<Any>

    @POST("api/attendance")
    suspend fun registerAttendance(
        @Body request: AttendanceRequest
    ): retrofit2.Response<Any>

    companion object {
        private const val BASE_URL = "https://valves-latex-bosnia-buyers.trycloudflare.com/"

        fun create(): AsistenciaApiService {
            val logger = HttpLoggingInterceptor().apply { level = HttpLoggingInterceptor.Level.BODY }
            val client = OkHttpClient.Builder()
                .addInterceptor(logger)
                .build()

            return Retrofit.Builder()
                .baseUrl(BASE_URL)
                .client(client)
                .addConverterFactory(GsonConverterFactory.create())
                .build()
                .create(AsistenciaApiService::class.java)
        }
    }
}
