package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.BasicListResponse
import com.nusatim.sapiriku.api.model.BasicResponse
import com.nusatim.sapiriku.api.model.FunctionalTypeResponse
import com.nusatim.sapiriku.api.model.InputPromoteRentVehicleConfigResponse
import com.nusatim.sapiriku.api.model.InputVehicleConfigResponse
import com.nusatim.sapiriku.api.model.ListRentVehicleTransactionResponse
import com.nusatim.sapiriku.api.model.PartnerListPromoteVehicleResponse
import com.nusatim.sapiriku.api.model.PartnerListVehicleResponse
import com.nusatim.sapiriku.api.model.PartnerRentVehicleConfigResponse
import com.nusatim.sapiriku.api.model.PatnerVehicleDetailResponse
import com.nusatim.sapiriku.api.model.RentVehicleDetailResponse
import com.nusatim.sapiriku.api.model.UploadImageResponse
import okhttp3.MultipartBody
import retrofit2.Response
import retrofit2.http.Field
import retrofit2.http.FieldMap
import retrofit2.http.FormUrlEncoded
import retrofit2.http.Multipart
import retrofit2.http.POST
import retrofit2.http.Part

interface PartnerRentService {

    @POST("partnerRent/get_functional_type")
    suspend fun getFunctionalType(): Response<FunctionalTypeResponse>

    @FormUrlEncoded
    @POST("partnerRent/get_input_config")
    suspend fun getInputConfig(@Field("functional_type") functionalType: Int): Response<InputVehicleConfigResponse>

    @FormUrlEncoded
    @POST("partnerRent/get_input_vehicle_model")
    suspend fun getInputVehicleModel(@Field("brand_id") brandId: Int): Response<BasicListResponse>

    @FormUrlEncoded
    @POST("partnerRent/post_vehicle")
    suspend fun postVehicle(
        @FieldMap param: Map<String, String>,
        @Field("photos[]") photos: List<String>
    ): Response<BasicResponse>

    @Multipart
    @POST("partnerRent/upload_vehicle_image")
    suspend fun uploadVehicleImage(@Part photo: MultipartBody.Part): Response<UploadImageResponse>

    @FormUrlEncoded
    @POST("partnerRent/list_vehicle")
    suspend fun listVehicle(@FieldMap param: Map<String, String>): Response<PartnerListVehicleResponse>

    @FormUrlEncoded
    @POST("partnerRent/vehicle_detail")
    suspend fun getVehicleDetail(@Field("id") id: Int): Response<PatnerVehicleDetailResponse>

    @FormUrlEncoded
    @POST("partnerRent/delete_vehicle_photo")
    suspend fun deleteVehiclePhoto(@Field("id") id: Int): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partnerRent/delete_vehicle")
    suspend fun deleteVehicle(@Field("id") id: Int): Response<BasicResponse>

    @POST("partnerRent/config")
    suspend fun config(): Response<PartnerRentVehicleConfigResponse>

    @FormUrlEncoded
    @POST("partnerRent/update_config")
    suspend fun updateConfig(@FieldMap param: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partnerRent/list_promote_vehicle")
    suspend fun listPromoteVehicle(@FieldMap param: Map<String, String>): Response<PartnerListPromoteVehicleResponse>

    @POST("partnerRent/get_input_promote_config")
    suspend fun getPromoteInputConfig(): Response<InputPromoteRentVehicleConfigResponse>

    @FormUrlEncoded
    @POST("partnerRent/post_promote")
    suspend fun postPromote(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partnerRent/cancel_promote")
    suspend fun cancelPromote(@Field("id") id: Int): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partnerRent/list_transaction")
    suspend fun listTransaction(@FieldMap form: Map<String, String>): Response<ListRentVehicleTransactionResponse>

    @FormUrlEncoded
    @POST("partnerRent/transaction_detail")
    suspend fun transactionDetail(@Field("id") id: Int): Response<RentVehicleDetailResponse>

    @FormUrlEncoded
    @POST("partnerRent/cancel_transaction")
    suspend fun cancelTransaction(@Field("id") id: Int): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partnerRent/update_status_transaction")
    suspend fun updateStatusTransaction(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partnerRent/done_transaction")
    suspend fun doneTransaction(@Field("id") id: Int): Response<BasicResponse>

    @FormUrlEncoded
    @POST("partnerRent/post_review")
    suspend fun postReview(@FieldMap form: Map<String, String>): Response<BasicResponse>
}
