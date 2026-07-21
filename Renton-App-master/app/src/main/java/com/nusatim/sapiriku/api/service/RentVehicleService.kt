package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.ApiEnvelope
import com.nusatim.sapiriku.api.model.BookingCreatedData
import com.nusatim.sapiriku.api.model.CreateBookingRequest
import com.nusatim.sapiriku.api.model.QuoteData
import com.nusatim.sapiriku.api.model.QuoteRequest
import com.nusatim.sapiriku.api.model.VehicleDetailData
import com.nusatim.sapiriku.api.model.VehicleListData
import com.nusatim.sapiriku.api.model.VehicleReviewsData
import com.nusatim.sapiriku.api.model.VoucherCheckData
import com.nusatim.sapiriku.api.model.VoucherCheckRequest
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path
import retrofit2.http.Query

/** Maps to RentonBachkEnd-main/application/modules/api/controllers/RentVehicle.php */
interface RentVehicleService {

    /** Public; `key` header optional (enables distance sort + personalization if present -- interceptor already adds it when logged in). */
    @GET("rentvehicle/list")
    suspend fun listVehicle(
        @Query("functional_type") functionalType: Int? = null,
        @Query("start_date") startDate: String? = null,
        @Query("end_date") endDate: String? = null,
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 10,
        @Query("sort") sort: Int? = null,
        @Query("status") status: Int? = null,
        @Query("min_passenger") minPassenger: Int? = null,
        @Query("max_passenger") maxPassenger: Int? = null,
        @Query("min_price") minPrice: Double? = null,
        @Query("max_price") maxPrice: Double? = null,
        @Query("vehicle_functional_type_selected") functionalTypeSelected: String? = null,
        @Query("with_driver") withDriver: Int? = null,
        @Query("regency") regency: String? = null
    ): Response<ApiEnvelope<VehicleListData>>

    /** Public, no `key` header required. */
    @GET("rentvehicle/detail/{id}")
    suspend fun getVehicleDetail(@Path("id") id: Int): Response<ApiEnvelope<VehicleDetailData>>

    /** Public, no `key` header required. */
    @GET("rentvehicle/reviews/{id}")
    suspend fun listVehicleReviews(
        @Path("id") id: Int,
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 10
    ): Response<ApiEnvelope<VehicleReviewsData>>

    /** Requires `key` header. */
    @POST("rentvehicle/quote")
    suspend fun quote(@Body request: QuoteRequest): Response<ApiEnvelope<QuoteData>>

    /** Requires `key` header. */
    @POST("rentvehicle/voucher")
    suspend fun checkVoucher(@Body request: VoucherCheckRequest): Response<ApiEnvelope<VoucherCheckData>>

    /** Requires `key` header. Was `postCheckout` -- creates the booking (was post_checkout). */
    @POST("rentvehicle/bookings")
    suspend fun createBooking(@Body request: CreateBookingRequest): Response<ApiEnvelope<BookingCreatedData>>
}
