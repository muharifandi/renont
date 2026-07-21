package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.*
import com.nusatim.sapiriku.domain.model.command.*
import kotlinx.coroutines.flow.Flow

interface CustomerFinanceRepository {
    fun getBalance(): Flow<Resource<Double>>
    fun getPoint(): Flow<Resource<Double>>
    suspend fun listTransactionPoints(page: Int, pageSize: Int): Result<List<TransactionPoint>>
    fun getExchangePointConfig(): Flow<Resource<ExchangePointConfig>>
    fun postExchangePoint(point: String): Flow<Resource<OperationResult>>
    fun listBanks(): Flow<Resource<List<CustomerBank>>>
    fun getBankDetail(id: Int): Flow<Resource<CustomerBank?>>
    fun getBankInputConfig(): Flow<Resource<List<Bank>>>
    fun postBank(command: AddBankCommand): Flow<Resource<OperationResult>>
    fun deleteBank(id: Int): Flow<Resource<OperationResult>>
    suspend fun listTopups(page: Int, pageSize: Int): Result<List<Topup>>
    fun getTopupDetail(topupId: Int): Flow<Resource<Topup?>>
    fun getRequestTopupConfig(): Flow<Resource<RequestTopupConfig>>
    fun postRequestTopup(command: TopupRequestCommand): Flow<Resource<TopupRequestResult>>
    fun postVerificationTopup(topupId: Int, command: UploadImageCommand): Flow<Resource<OperationResult>>
    suspend fun listWithdraws(page: Int, pageSize: Int): Result<List<Withdraw>>
    fun getRequestWithdrawConfig(): Flow<Resource<RequestWithdrawConfig>>
    fun postRequestWithdraw(command: WithdrawRequestCommand): Flow<Resource<OperationResult>>
}
