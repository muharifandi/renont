package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.BasicResponse
import com.nusatim.sapiriku.api.model.ListRentVehicleTransactionResponse
import com.nusatim.sapiriku.api.model.RentVehicleDetailResponse
import retrofit2.Response
import retrofit2.http.Field
import retrofit2.http.FieldMap
import retrofit2.http.FormUrlEncoded
import retrofit2.http.POST

interface CustomerRentService {

    @FormUrlEncoded
    @POST("customerRent/list_transaction")
    suspend fun listTransaction(@FieldMap form: Map<String, String>): Response<ListRentVehicleTransactionResponse>

    @FormUrlEncoded
    @POST("customerRent/transaction_detail")
    suspend fun transactionDetail(@Field("id") id: Int): Response<RentVehicleDetailResponse>

    @FormUrlEncoded
    @POST("customerRent/cancel_transaction")
    suspend fun cancelTransaction(@Field("id") id: Int): Response<BasicResponse>

    @FormUrlEncoded
    @POST("customerRent/update_status_transaction")
    suspend fun updateStatusTransaction(@FieldMap form: Map<String, String>): Response<BasicResponse>

    @FormUrlEncoded
    @POST("customerRent/post_review")
    suspend fun postReview(@FieldMap form: Map<String, String>): Response<BasicResponse>
}
