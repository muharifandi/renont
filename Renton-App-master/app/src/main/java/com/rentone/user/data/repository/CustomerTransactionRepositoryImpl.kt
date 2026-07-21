package com.rentone.user.data.repository

import com.rentone.user.api.service.CustomerRentService
import com.rentone.user.core.common.Resource
import com.rentone.user.data.mapper.toDomain
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.RentVehicleDetail
import com.rentone.user.domain.model.RentVehicleTransaction
import com.rentone.user.domain.repository.CustomerTransactionRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class CustomerTransactionRepositoryImpl @Inject constructor(
    private val customerRentService: CustomerRentService
) : BaseRepository(), CustomerTransactionRepository {

    override suspend fun listTransactions(page: Int, pageSize: Int, status: Int): Result<List<RentVehicleTransaction>> {
        return try {
            val param = mapOf(
                "page" to page.toString(),
                "limit" to pageSize.toString(),
                "status" to status.toString()
            )
            val response = customerRentService.listTransaction(param)
            Result.success(response.body()?.rentVehicleTransactions ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getTransactionDetail(id: Int): Flow<Resource<RentVehicleDetail>> {
        return safeApiCall(
            apiCall = { customerRentService.transactionDetail(id) },
            map = { it.toDomain() }
        )
    }

    override fun updateTransactionStatus(id: Int, status: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerRentService.updateStatusTransaction(mapOf("id" to id.toString(), "status" to status.toString())) },
            map = { OperationResult(it.status, it.message ?: "") }
        )
    }

    override fun cancelTransaction(id: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerRentService.cancelTransaction(id) },
            map = { OperationResult(it.status, it.message ?: "") }
        )
    }

    override fun postReview(transactionId: Int, rating: Float, comment: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { 
                val form = mapOf(
                    "id" to transactionId.toString(),
                    "rating" to rating.toString(),
                    "comment" to comment
                )
                customerRentService.postReview(form)
            },
            map = { OperationResult(it.status, it.message ?: "") }
        )
    }
}
