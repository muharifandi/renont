package com.rentone.user.data.repository

import com.rentone.user.api.service.PartnerRentService
import com.rentone.user.core.common.Resource
import com.rentone.user.data.mapper.*
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.RentVehicleDetail
import com.rentone.user.domain.model.RentVehicleTransaction
import com.rentone.user.domain.repository.PartnerTransactionRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PartnerTransactionRepositoryImpl @Inject constructor(
    private val partnerRentService: PartnerRentService
) : BaseRepository(), PartnerTransactionRepository {

    override suspend fun listTransactions(page: Int, pageSize: Int, status: Int): Result<List<RentVehicleTransaction>> {
        return try {
            val response = partnerRentService.listTransaction(mapOf("page" to page.toString(), "limit" to pageSize.toString(), "status" to status.toString()))
            Result.success(response.body()?.rentVehicleTransactions ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getTransactionDetail(id: Int): Flow<Resource<RentVehicleDetail>> {
        return safeApiCall(
            apiCall = { partnerRentService.transactionDetail(id) },
            map = { it.toRentVehicleDetail() }
        )
    }

    override fun updateTransactionStatus(id: Int, status: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.updateStatusTransaction(mapOf("id" to id.toString(), "status" to status.toString())) },
            map = { it.toOperationResult() }
        )
    }

    override fun completeTransaction(id: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.doneTransaction(id) },
            map = { it.toOperationResult() }
        )
    }

    override fun cancelTransaction(id: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.cancelTransaction(id) },
            map = { it.toOperationResult() }
        )
    }

    override fun postReview(transactionId: Int, rating: Float, comment: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.postReview(mapOf("id" to transactionId.toString(), "rating" to rating.toString(), "comment" to comment)) },
            map = { it.toOperationResult() }
        )
    }
}
