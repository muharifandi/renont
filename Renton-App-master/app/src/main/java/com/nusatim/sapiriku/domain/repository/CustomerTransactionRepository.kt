package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.RentVehicleDetail
import com.nusatim.sapiriku.domain.model.RentVehicleTransaction
import kotlinx.coroutines.flow.Flow

interface CustomerTransactionRepository {
    suspend fun listTransactions(page: Int, pageSize: Int, status: Int): Result<List<RentVehicleTransaction>>
    fun getTransactionDetail(id: Int): Flow<Resource<RentVehicleDetail>>
    fun updateTransactionStatus(id: Int, status: Int): Flow<Resource<OperationResult>>
    fun cancelTransaction(id: Int): Flow<Resource<OperationResult>>
    fun postReview(transactionId: Int, rating: Float, comment: String): Flow<Resource<OperationResult>>
}
