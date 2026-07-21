package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.api.model.BookingReviewRequest
import com.nusatim.sapiriku.api.model.UpdateBookingStatusRequest
import com.nusatim.sapiriku.api.service.PartnerRentService
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.RentVehicleDetail
import com.nusatim.sapiriku.domain.model.RentVehicleTransaction
import com.nusatim.sapiriku.domain.repository.PartnerTransactionRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PartnerTransactionRepositoryImpl @Inject constructor(
    private val partnerRentService: PartnerRentService
) : BaseRepository(), PartnerTransactionRepository {

    override suspend fun listTransactions(page: Int, pageSize: Int, status: Int): Result<List<RentVehicleTransaction>> {
        return try {
            val response = partnerRentService.listBookings(
                page = page,
                limit = pageSize,
                status = if (status == -1) null else status
            )
            Result.success(response.body()?.data?.transactions?.map { it.toRentVehicleTransaction() } ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getTransactionDetail(id: Int): Flow<Resource<RentVehicleDetail>> {
        return safeApiCall(
            apiCall = { partnerRentService.getBookingDetail(id) },
            map = { response -> response.data?.toRentVehicleDetail() ?: throw Exception("Empty data") }
        )
    }

    override fun updateTransactionStatus(id: Int, status: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.updateBookingStatus(id, UpdateBookingStatusRequest(status)) },
            map = { it.toOperationResult() }
        )
    }

    override fun completeTransaction(id: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.completeBooking(id) },
            map = { it.toOperationResult() }
        )
    }

    override fun cancelTransaction(id: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.cancelBooking(id) },
            map = { it.toOperationResult() }
        )
    }

    override fun postReview(transactionId: Int, rating: Float, comment: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { 
                partnerRentService.postReview(transactionId, BookingReviewRequest(rating.toInt(), comment))
            },
            map = { it.toOperationResult() }
        )
    }
}
