package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.ApiEnvelope
import com.nusatim.sapiriku.api.model.BookingDetailData
import com.nusatim.sapiriku.api.model.BookingListData
import com.nusatim.sapiriku.api.model.BookingReviewRequest
import com.nusatim.sapiriku.api.model.CreatePromotionRequest
import com.nusatim.sapiriku.api.model.FunctionalTypesData
import com.nusatim.sapiriku.api.model.PartnerRentConfigData
import com.nusatim.sapiriku.api.model.PartnerVehicleDetailData
import com.nusatim.sapiriku.api.model.PartnerVehicleListData
import com.nusatim.sapiriku.api.model.PromoteListData
import com.nusatim.sapiriku.api.model.PromotionCreatedData
import com.nusatim.sapiriku.api.model.PromotionInputConfigData
import com.nusatim.sapiriku.api.model.UpdateBookingStatusRequest
import com.nusatim.sapiriku.api.model.UpdatePartnerRentConfigRequest
import com.nusatim.sapiriku.api.model.UploadPhotoData
import com.nusatim.sapiriku.api.model.VehicleCreatedData
import com.nusatim.sapiriku.api.model.VehicleInputConfigData
import com.nusatim.sapiriku.api.model.VehicleModelsData
import com.nusatim.sapiriku.api.model.VehiclePayloadRequest
import okhttp3.MultipartBody
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.Multipart
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Part
import retrofit2.http.Path
import retrofit2.http.Query

/** Maps to RentonBachkEnd-main/application/modules/api/controllers/PartnerRent.php -- requires `key` header (partner role). */
interface PartnerRentService {

    @GET("partnerRent/functional_types")
    suspend fun getFunctionalTypes(): Response<ApiEnvelope<FunctionalTypesData>>

    @GET("partnerRent/vehicle_input_config")
    suspend fun getVehicleInputConfig(@Query("functional_type") functionalType: Int): Response<ApiEnvelope<VehicleInputConfigData>>

    @GET("partnerRent/vehicle_models")
    suspend fun getVehicleModels(@Query("brand_id") brandId: Int): Response<ApiEnvelope<VehicleModelsData>>

    /** Upload each photo separately first, collect the returned filenames into [VehiclePayloadRequest.photos]. */
    @Multipart
    @POST("partnerRent/vehicle_photos")
    suspend fun uploadVehiclePhoto(@Part photo: MultipartBody.Part): Response<ApiEnvelope<UploadPhotoData>>

    @DELETE("partnerRent/vehicle_photos/{id}")
    suspend fun deleteVehiclePhoto(@Path("id") id: Int): Response<ApiEnvelope<Unit?>>

    @POST("partnerRent/vehicles")
    suspend fun createVehicle(@Body request: VehiclePayloadRequest): Response<ApiEnvelope<VehicleCreatedData>>

    @PUT("partnerRent/vehicles/{id}")
    suspend fun updateVehicle(@Path("id") id: Int, @Body request: VehiclePayloadRequest): Response<ApiEnvelope<Unit?>>

    @GET("partnerRent/vehicles")
    suspend fun listVehicles(
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 10,
        @Query("sort") sort: Int? = null,
        @Query("status") status: Int? = null,
        @Query("min_passenger") minPassenger: Int? = null,
        @Query("max_passenger") maxPassenger: Int? = null,
        @Query("min_price") minPrice: Double? = null,
        @Query("max_price") maxPrice: Double? = null,
        @Query("vehicle_functional_type_selected") functionalTypeSelected: String? = null
    ): Response<ApiEnvelope<PartnerVehicleListData>>

    @GET("partnerRent/vehicles/{id}")
    suspend fun getVehicleDetail(@Path("id") id: Int): Response<ApiEnvelope<PartnerVehicleDetailData>>

    @DELETE("partnerRent/vehicles/{id}")
    suspend fun deleteVehicle(@Path("id") id: Int): Response<ApiEnvelope<Unit?>>

    @GET("partnerRent/config")
    suspend fun getConfig(): Response<ApiEnvelope<PartnerRentConfigData>>

    @PUT("partnerRent/config")
    suspend fun updateConfig(@Body request: UpdatePartnerRentConfigRequest): Response<ApiEnvelope<Unit?>>

    @GET("partnerRent/bookings")
    suspend fun listBookings(
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 10,
        @Query("status") status: Int? = null
    ): Response<ApiEnvelope<BookingListData>>

    @GET("partnerRent/bookings/{id}")
    suspend fun getBookingDetail(@Path("id") id: Int): Response<ApiEnvelope<BookingDetailData>>

    /** Was `cancelTransaction`. */
    @DELETE("partnerRent/bookings/{id}")
    suspend fun cancelBooking(@Path("id") id: Int): Response<ApiEnvelope<Unit?>>

    /** Was `updateStatusTransaction`. */
    @PUT("partnerRent/booking_status/{id}")
    suspend fun updateBookingStatus(@Path("id") id: Int, @Body request: UpdateBookingStatusRequest): Response<ApiEnvelope<Unit?>>

    /** Was `doneTransaction` -- settles payment, awards points, triggers reward/commission processing. */
    @PUT("partnerRent/booking_done/{id}")
    suspend fun completeBooking(@Path("id") id: Int): Response<ApiEnvelope<Unit?>>

    /** Was `postReview`. */
    @POST("partnerRent/booking_review/{id}")
    suspend fun postReview(@Path("id") id: Int, @Body request: BookingReviewRequest): Response<ApiEnvelope<Unit?>>

    @GET("partnerRent/promotions")
    suspend fun listPromotions(
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 10
    ): Response<ApiEnvelope<PromoteListData>>

    @GET("partnerRent/promotion_input_config")
    suspend fun getPromotionInputConfig(): Response<ApiEnvelope<PromotionInputConfigData>>

    /** Was `postPromote`. */
    @POST("partnerRent/promotions")
    suspend fun createPromotion(@Body request: CreatePromotionRequest): Response<ApiEnvelope<PromotionCreatedData>>

    /** Was `cancelPromote`. Refunds the remaining days to the caller's balance. */
    @DELETE("partnerRent/promotions/{id}")
    suspend fun cancelPromotion(@Path("id") id: Int): Response<ApiEnvelope<Unit?>>
}
