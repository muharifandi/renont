package com.rentone.user.api.service

import com.rentone.user.api.model.BasicResponse
import com.rentone.user.api.model.CheckVoucherResponse
import com.rentone.user.api.model.CheckoutDetailResponse
import com.rentone.user.api.model.ListVehicleResponse
import com.rentone.user.api.model.ListVehicleReviewResponse
import com.rentone.user.api.model.VehicleDetailResponse
import retrofit2.Response
import retrofit2.http.Field
import retrofit2.http.FieldMap
import retrofit2.http.FormUrlEncoded
import retrofit2.http.POST

interface RentVehicleService {

    @FormUrlEncoded
    @POST("rentVehicle/list")
    suspend fun listVehicle(@FieldMap param: Map<String, String>): Response<ListVehicleResponse>

    @FormUrlEncoded
    @POST("rentVehicle/list_vehicle_review")
    suspend fun listVehicleReview(@FieldMap param: Map<String, String>): Response<ListVehicleReviewResponse>

    @FormUrlEncoded
    @POST("rentVehicle/detail")
    suspend fun getVehicleDetail(@Field("id") id: Int): Response<VehicleDetailResponse>

    @FormUrlEncoded
    @POST("rentVehicle/checkout_detail")
    suspend fun checkoutDetail(@FieldMap param: Map<String, String>): Response<CheckoutDetailResponse>

    @FormUrlEncoded
    @POST("rentVehicle/check_voucher_checkout")
    suspend fun checkVoucherCheckout(@FieldMap param: Map<String, String>): Response<CheckVoucherResponse>

    @FormUrlEncoded
    @POST("rentVehicle/post_checkout")
    suspend fun postCheckout(@FieldMap param: Map<String, String>): Response<BasicResponse>
}
