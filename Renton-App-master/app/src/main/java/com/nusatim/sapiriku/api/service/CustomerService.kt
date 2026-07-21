package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.AccountStatusData
import com.nusatim.sapiriku.api.model.ApiEnvelope
import com.nusatim.sapiriku.api.model.BalanceOnlyData
import com.nusatim.sapiriku.api.model.ChangePasswordRequest
import com.nusatim.sapiriku.api.model.CreateTopupRequest
import com.nusatim.sapiriku.api.model.CreateWithdrawRequest
import com.nusatim.sapiriku.api.model.CustomerBankDetailData
import com.nusatim.sapiriku.api.model.CustomerBanksData
import com.nusatim.sapiriku.api.model.CustomerDetailData
import com.nusatim.sapiriku.api.model.ExchangePointRequest
import com.nusatim.sapiriku.api.model.HomeData
import com.nusatim.sapiriku.api.model.LoginData
import com.nusatim.sapiriku.api.model.LoginRequest
import com.nusatim.sapiriku.api.model.PointExchangeConfigData
import com.nusatim.sapiriku.api.model.PointOnlyData
import com.nusatim.sapiriku.api.model.PointTransactionsData
import com.nusatim.sapiriku.api.model.ProfileImageData
import com.nusatim.sapiriku.api.model.RegisterCustomerData
import com.nusatim.sapiriku.api.model.SaveBankRequest
import com.nusatim.sapiriku.api.model.SavedBankIdData
import com.nusatim.sapiriku.api.model.TopupConfigData
import com.nusatim.sapiriku.api.model.TopupCreatedData
import com.nusatim.sapiriku.api.model.TopupDetailData
import com.nusatim.sapiriku.api.model.TopupListData
import com.nusatim.sapiriku.api.model.UpdateLocationRequest
import com.nusatim.sapiriku.api.model.UpdateNameRequest
import com.nusatim.sapiriku.api.model.UpdatePushTokenRequest
import com.nusatim.sapiriku.api.model.WithdrawConfigData
import com.nusatim.sapiriku.api.model.WithdrawCreatedData
import com.nusatim.sapiriku.api.model.WithdrawListData
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
import retrofit2.http.Path
import retrofit2.http.Query

/** Maps to RentonBachkEnd-main/application/modules/api/controllers/Customer.php */
interface CustomerService {

    /**
     * Registers a new customer account. [form] text fields: email, password, first_name,
     * last_name, phone, identity_number, referal (optional). [files] parts named:
     * img_profile, img_identity (jpg/jpeg/png, optional).
     */
    @Multipart
    @POST("customer")
    suspend fun register(
        @PartMap form: Map<String, RequestBody>,
        @Part files: List<MultipartBody.Part>
    ): Response<ApiEnvelope<RegisterCustomerData>>

    @POST("customer/login")
    suspend fun login(@Body request: LoginRequest): Response<ApiEnvelope<LoginData>>

    @GET("customer/detail")
    suspend fun getDetail(): Response<ApiEnvelope<CustomerDetailData>>

    @GET("customer/banks")
    suspend fun getBanks(): Response<ApiEnvelope<CustomerBanksData>>

    /** Omit [request.id] to create a new saved bank account; include it to update an existing one. */
    @POST("customer/banks")
    suspend fun saveBank(@Body request: SaveBankRequest): Response<ApiEnvelope<SavedBankIdData?>>

    @DELETE("customer/bank/{id}")
    suspend fun deleteBank(@Path("id") id: Int): Response<ApiEnvelope<Unit?>>

    /** Pass null [id] to get the master bank-institution dropdown list instead of one saved account. */
    @GET("customer/bank/{id}")
    suspend fun getBankDetail(@Path("id") id: Int): Response<ApiEnvelope<CustomerBankDetailData>>

    @Multipart
    @POST("customer/profile_image")
    suspend fun uploadProfileImage(@Part imgProfile: MultipartBody.Part): Response<ApiEnvelope<ProfileImageData>>

    @PUT("customer/name")
    suspend fun updateName(@Body request: UpdateNameRequest): Response<ApiEnvelope<Unit?>>

    @PUT("customer/password")
    suspend fun changePassword(@Body request: ChangePasswordRequest): Response<ApiEnvelope<Unit?>>

    /** Public, no `key` required -- personalized (balance/referal_code) only if the interceptor attaches one. */
    @GET("customer/home")
    suspend fun getHome(): Response<ApiEnvelope<HomeData>>

    /** Public, no `key` required. Follow the emailed activation link's id+code. */
    @GET("customer/activate")
    suspend fun activate(@Query("id") id: Int, @Query("code") code: String): Response<ApiEnvelope<Unit?>>

    @GET("customer/status")
    suspend fun getStatus(): Response<ApiEnvelope<AccountStatusData>>

    @GET("customer/balance")
    suspend fun getBalance(): Response<ApiEnvelope<BalanceOnlyData>>

    /** Public, no `key` required. */
    @GET("customer/topup_config")
    suspend fun getTopupConfig(): Response<ApiEnvelope<TopupConfigData>>

    @POST("customer/topups")
    suspend fun createTopup(@Body request: CreateTopupRequest): Response<ApiEnvelope<TopupCreatedData>>

    @GET("customer/topups")
    suspend fun listTopups(
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 10
    ): Response<ApiEnvelope<TopupListData>>

    @GET("customer/topups/{id}")
    suspend fun getTopupDetail(@Path("id") id: Int): Response<ApiEnvelope<TopupDetailData>>

    @Multipart
    @POST("customer/topup_proof/{id}")
    suspend fun uploadTopupProof(@Path("id") id: Int, @Part imgProof: MultipartBody.Part): Response<ApiEnvelope<Unit?>>

    @GET("customer/withdraws")
    suspend fun listWithdraws(
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 10
    ): Response<ApiEnvelope<WithdrawListData>>

    @GET("customer/withdraw_config")
    suspend fun getWithdrawConfig(): Response<ApiEnvelope<WithdrawConfigData>>

    @POST("customer/withdraws")
    suspend fun createWithdraw(@Body request: CreateWithdrawRequest): Response<ApiEnvelope<WithdrawCreatedData>>

    @GET("customer/point")
    suspend fun getPoint(): Response<ApiEnvelope<PointOnlyData>>

    @GET("customer/point_exchange_config")
    suspend fun getPointExchangeConfig(): Response<ApiEnvelope<PointExchangeConfigData>>

    @POST("customer/point/exchange")
    suspend fun exchangePoint(@Body request: ExchangePointRequest): Response<ApiEnvelope<Unit?>>

    @GET("customer/point_transactions")
    suspend fun listPointTransactions(
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 10
    ): Response<ApiEnvelope<PointTransactionsData>>

    @PUT("customer/location")
    suspend fun updateLocation(@Body request: UpdateLocationRequest): Response<ApiEnvelope<Unit?>>

    @PUT("customer/push_token")
    suspend fun updatePushToken(@Body request: UpdatePushTokenRequest): Response<ApiEnvelope<Unit?>>
}
