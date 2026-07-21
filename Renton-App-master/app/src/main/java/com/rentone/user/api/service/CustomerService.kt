package com.rentone.user.api.service

import com.rentone.user.api.model.*
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Response
import retrofit2.http.*

interface CustomerService {

    @FormUrlEncoded
    @POST("customer/login")
    suspend fun login(@FieldMap auth: Map<String, String>): Response<GetLoginResponse>

    @FormUrlEncoded
    @POST("customer/update_token")
    suspend fun updateToken(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("customer/update_location")
    suspend fun updateCustomerLocation(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @Multipart
    @POST("customer/register")
    suspend fun register(
        @PartMap form: Map<String, RequestBody>,
        @Part files: List<MultipartBody.Part>
    ): Response<BasicResponse>

    @POST("customer/status")
    suspend fun status(): Response<CustomerStatusResponse>

    @POST("customer/home")
    suspend fun home(): Response<HomeResponse>

    @POST("customer/detail")
    suspend fun detail(): Response<CustomerDetailResponse>

    @Multipart
    @POST("customer/upload_profile_image")
    suspend fun uploadProfileImage(@Part image: MultipartBody.Part): Response<BasicResponse>

    @FormUrlEncoded
    @POST("customer/change_name")
    suspend fun changeName(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("customer/change_password")
    suspend fun changePassword(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @POST("customer/balance")
    suspend fun balance(): Response<BalanceResponse>

    @POST("customer/point")
    suspend fun point(): Response<PointResponse>

    @FormUrlEncoded
    @POST("customer/list_topup")
    suspend fun listTopup(@FieldMap form: Map<String, String>): Response<ListTopupResponse>

    @FormUrlEncoded
    @POST("customer/list_withdraw")
    suspend fun listWithdraw(@FieldMap form: Map<String, String>): Response<ListWithdrawResponse>

    @FormUrlEncoded
    @POST("customer/list_transaction_point")
    suspend fun listTransactionPoint(@FieldMap form: Map<String, String>): Response<ListTransactionPointResponse>

    @POST("customer/exchange_point_config")
    suspend fun getExchangePointConfig(): Response<ExchangePointConfigResponse>

    @FormUrlEncoded
    @POST("customer/exchange_point")
    suspend fun postExchangePoint(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @POST("customer/request_topup_config")
    suspend fun getRequestTopupConfig(): Response<RequestTopupConfigResponse>

    @FormUrlEncoded
    @POST("customer/request_topup")
    suspend fun postRequestTopup(@FieldMap form: Map<String, String>): Response<RequestTopupResponse>

    @FormUrlEncoded
    @POST("customer/topup_detail")
    suspend fun topupDetail(@Field("topup_id") id: Int): Response<TopupDetailResponse>

    @Multipart
    @POST("customer/verification_topup")
    suspend fun verificationTopup(
        @PartMap form: Map<String, RequestBody>,
        @Part image: MultipartBody.Part
    ): Response<BasicResponse>

    @POST("customer/request_withdraw_config")
    suspend fun getRequestWithdrawConfig(): Response<RequestWithdrawConfigResponse>

    @FormUrlEncoded
    @POST("customer/request_withdraw")
    suspend fun postRequestWithdraw(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @POST("customer/bank_input_config")
    suspend fun getBankInputConfig(): Response<InputBankConfigResponse>

    @FormUrlEncoded
    @POST("customer/bank_detail")
    suspend fun bankDetail(@Field("id") id: Int): Response<CustomerBankDetailResponse>

    @FormUrlEncoded
    @POST("customer/post_bank")
    suspend fun postBank(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @POST("customer/banks")
    suspend fun banks(): Response<ListCustomerBankResponse>

    @FormUrlEncoded
    @POST("customer/delete_bank")
    suspend fun deleteBank(@Field("id") id: Int): Response<BasicResponse>
}
