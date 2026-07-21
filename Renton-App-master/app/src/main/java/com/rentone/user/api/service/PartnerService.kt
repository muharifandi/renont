package com.rentone.user.api.service

import com.rentone.user.api.model.BasicResponse
import com.rentone.user.api.model.PartnerDetailResponse
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Response
import retrofit2.http.FieldMap
import retrofit2.http.FormUrlEncoded
import retrofit2.http.Multipart
import retrofit2.http.POST
import retrofit2.http.Part
import retrofit2.http.PartMap

interface PartnerService {

    @Multipart
    @POST("partner/register")
    suspend fun register(
        @PartMap form: Map<String, RequestBody>,
        @Part files: List<MultipartBody.Part>
    ): Response<BasicResponse>

    @POST("partner/detail")
    suspend fun detail(): Response<PartnerDetailResponse>

    @Multipart
    @POST("partner/upload_profile_image")
    suspend fun uploadProfileImage(@Part image: MultipartBody.Part): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partner/change_company_name")
    suspend fun changeCompanyName(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partner/change_description")
    suspend fun changeDescription(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partner/change_address")
    suspend fun changeAddress(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partner/change_regency")
    suspend fun changeRegency(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partner/change_bussiness_location")
    suspend fun changeBussinessLocation(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partner/request_feature")
    suspend fun requestFeature(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @POST("partner/resubmit_register")
    suspend fun resubmitRegister(): Response<BasicResponse>
}
