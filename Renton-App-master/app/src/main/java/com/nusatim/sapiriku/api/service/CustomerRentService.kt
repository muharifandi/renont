package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.ApiEnvelope
import com.nusatim.sapiriku.api.model.BookingDetailData
import com.nusatim.sapiriku.api.model.BookingListData
import com.nusatim.sapiriku.api.model.BookingReviewRequest
import com.nusatim.sapiriku.api.model.UpdateBookingStatusRequest
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Path
import retrofit2.http.Query

/** Maps to RentonBachkEnd-main/application/modules/api/controllers/CustomerRent.php -- requires `key` header (customer role). */
interface CustomerRentService {

    @GET("customerRent/bookings")
    suspend fun listBookings(
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 10,
        @Query("status") status: Int? = null
    ): Response<ApiEnvelope<BookingListData>>

    @GET("customerRent/bookings/{id}")
    suspend fun getBookingDetail(@Path("id") id: Int): Response<ApiEnvelope<BookingDetailData>>

    /** Was `cancelTransaction`. */
    @DELETE("customerRent/bookings/{id}")
    suspend fun cancelBooking(@Path("id") id: Int): Response<ApiEnvelope<Unit?>>

    /** Was `updateStatusTransaction`. */
    @PUT("customerRent/booking_status/{id}")
    suspend fun updateBookingStatus(@Path("id") id: Int, @Body request: UpdateBookingStatusRequest): Response<ApiEnvelope<Unit?>>

    /** Was `postReview`. */
    @POST("customerRent/booking_review/{id}")
    suspend fun postReview(@Path("id") id: Int, @Body request: BookingReviewRequest): Response<ApiEnvelope<Unit?>>
}
