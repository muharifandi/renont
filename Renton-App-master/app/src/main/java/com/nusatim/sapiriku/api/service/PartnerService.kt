package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.ApiEnvelope
import com.nusatim.sapiriku.api.model.FeatureRequestRequest
import com.nusatim.sapiriku.api.model.PartnerDetailData
import com.nusatim.sapiriku.api.model.PartnerProfileImageData
import com.nusatim.sapiriku.api.model.PartnerStatusData
import com.nusatim.sapiriku.api.model.UpdatePartnerProfileRequest
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.Multipart
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Part
import retrofit2.http.PartMap

/** Maps to RentonBachkEnd-main/application/modules/api/controllers/Partner.php -- requires `key` header (customer account being promoted). */
interface PartnerService {

    /**
     * Promotes the logged-in customer account to partner.
     * [form] text fields: ownership_id, company_name, regencies_id, address, latitude,
     * longitude (required); description, tax_number, agent_id, referal (optional).
     * [files] parts named: img_profile, img_identity, img_driver_licence,
     * img_bussiness_licence, img_bussiness_registration (jpg/jpeg/png/pdf, optional but
     * effectively required for the registration to be approved).
     */
    @Multipart
    @POST("partner")
    suspend fun register(
        @PartMap form: Map<String, RequestBody>,
        @Part files: List<MultipartBody.Part>
    ): Response<ApiEnvelope<Unit?>>

    /** Discards a rejected registration to allow resubmission. Was `resubmitRegister()`. */
    @DELETE("partner")
    suspend fun deleteRegistration(): Response<ApiEnvelope<Unit?>>

    @GET("partner/status")
    suspend fun getStatus(): Response<ApiEnvelope<PartnerStatusData>>

    @GET("partner/detail")
    suspend fun getDetail(): Response<ApiEnvelope<PartnerDetailData>>

    @Multipart
    @POST("partner/profile_image")
    suspend fun uploadProfileImage(@Part imgProfile: MultipartBody.Part): Response<ApiEnvelope<PartnerProfileImageData>>

    /**
     * Replaces the old changeCompanyName/changeDescription/changeAddress/changeRegency/
     * changeBussinessLocation endpoints -- send only the field(s) you want to change, any
     * combination of: company_name, description, address, regencies_id, latitude, longitude.
     */
    @PUT("partner/profile")
    suspend fun updateProfile(@Body request: UpdatePartnerProfileRequest): Response<ApiEnvelope<Unit?>>

    @POST("partner/feature_requests")
    suspend fun requestFeature(@Body request: FeatureRequestRequest): Response<ApiEnvelope<Unit?>>
}
