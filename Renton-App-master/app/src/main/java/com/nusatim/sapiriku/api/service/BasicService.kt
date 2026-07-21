package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.ApplicationStatusResponse
import com.nusatim.sapiriku.api.model.BasicListResponse
import com.nusatim.sapiriku.api.model.CheckAgentResponse
import com.nusatim.sapiriku.api.model.CheckEmailResponse
import com.nusatim.sapiriku.api.model.CheckPhoneResponse
import com.nusatim.sapiriku.api.model.GetRegenciesResponse
import com.nusatim.sapiriku.api.model.ListVehicleResponse
import retrofit2.Response
import retrofit2.http.Field
import retrofit2.http.FormUrlEncoded
import retrofit2.http.POST

interface BasicService {

    @POST("basic/application_status")
    suspend fun applicationStatus(): Response<ApplicationStatusResponse>

    @FormUrlEncoded
    @POST("basic/check_email")
    suspend fun checkEmail(@Field("email") email: String): Response<CheckEmailResponse>

    @FormUrlEncoded
    @POST("basic/check_agent")
    suspend fun checkAgent(@Field("id") id: String): Response<CheckAgentResponse>

    @FormUrlEncoded
    @POST("basic/check_phone")
    suspend fun checkPhone(@Field("phone") phone: String): Response<CheckPhoneResponse>

    @FormUrlEncoded
    @POST("basic/get_regencies")
    suspend fun getRegencies(@Field("regency") regency: String): Response<GetRegenciesResponse>

    @POST("basic/get_active_regencies")
    suspend fun getActiveRegencies(): Response<BasicListResponse>

    @POST("basic/get_recomendation_rentvehicle")
    suspend fun getRecomendationRentVehicle(): Response<ListVehicleResponse>
}
