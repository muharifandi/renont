package com.nusatim.sapiriku.data.repository

import android.content.Context
import com.nusatim.sapiriku.api.model.CreateTopupRequest
import com.nusatim.sapiriku.api.model.CreateWithdrawRequest
import com.nusatim.sapiriku.api.model.ExchangePointRequest
import com.nusatim.sapiriku.api.model.SaveBankRequest
import com.nusatim.sapiriku.api.model.UpdatePushTokenRequest
import com.nusatim.sapiriku.api.service.CustomerService
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.util.FileUtils
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.domain.model.*
import com.nusatim.sapiriku.domain.model.command.*
import com.nusatim.sapiriku.domain.repository.CustomerFinanceRepository
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class CustomerFinanceRepositoryImpl @Inject constructor(
    private val customerService: CustomerService,
    @ApplicationContext private val context: Context
) : BaseRepository(), CustomerFinanceRepository {

    override fun getBalance(): Flow<Resource<Double>> {
        return safeApiCall(
            apiCall = { customerService.getBalance() },
            map = { it.data?.balance ?: 0.0 }
        )
    }

    override fun getPoint(): Flow<Resource<Double>> {
        return safeApiCall(
            apiCall = { customerService.getPoint() },
            map = { it.data?.point?.toDouble() ?: 0.0 }
        )
    }

    override suspend fun listTransactionPoints(page: Int, pageSize: Int): Result<List<TransactionPoint>> {
        return try {
            val response = customerService.listPointTransactions(page, pageSize)
            Result.success(response.body()?.data?.transactionPoint?.map { it.toTransactionPoint() } ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getExchangePointConfig(): Flow<Resource<ExchangePointConfig>> {
        return safeApiCall(
            apiCall = { customerService.getPointExchangeConfig() },
            map = { it.data?.toExchangePointConfig() ?: throw Exception("Empty data") }
        )
    }

    override fun postExchangePoint(point: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerService.exchangePoint(ExchangePointRequest(point.toInt())) },
            map = { it.toOperationResult() }
        )
    }

    override fun listBanks(): Flow<Resource<List<CustomerBank>>> {
        return safeApiCall(
            apiCall = { customerService.getBanks() },
            map = { it.data?.banks?.map { it.toCustomerBankDomain() } ?: emptyList() }
        )
    }

    override fun getBankDetail(id: Int): Flow<Resource<CustomerBank?>> {
        return safeApiCall(
            apiCall = { customerService.getBankDetail(id) },
            map = { it.data?.bank?.toCustomerBankDomain() }
        )
    }

    override fun getBankInputConfig(): Flow<Resource<List<Bank>>> {
        return safeApiCall(
            apiCall = { customerService.getBankDetail(0) }, // Passing 0 or omitting id gets master list
            map = { it.data?.bankOptions?.map { it.toBank() } ?: emptyList() }
        )
    }

    override fun postBank(command: AddBankCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { 
                val request = SaveBankRequest(
                    id = command.id,
                    bankId = command.bankId,
                    name = command.accountName,
                    bankNumber = command.accountNumber
                )
                customerService.saveBank(request)
            },
            map = { it.toOperationResult() }
        )
    }

    override fun deleteBank(id: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerService.deleteBank(id) },
            map = { it.toOperationResult() }
        )
    }

    override suspend fun listTopups(page: Int, pageSize: Int): Result<List<Topup>> {
        return try {
            val response = customerService.listTopups(page, pageSize)
            Result.success(response.body()?.data?.topups?.map { it.toTopup() } ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getTopupDetail(topupId: Int): Flow<Resource<Topup?>> {
        return safeApiCall(
            apiCall = { customerService.getTopupDetail(topupId) },
            map = { it.data?.detail?.toTopup() }
        )
    }

    override fun getRequestTopupConfig(): Flow<Resource<RequestTopupConfig>> {
        return safeApiCall(
            apiCall = { customerService.getTopupConfig() },
            map = { it.data?.toRequestTopupConfig() ?: throw Exception("Empty data") }
        )
    }

    override fun postRequestTopup(command: TopupRequestCommand): Flow<Resource<TopupRequestResult>> {
        return safeApiCall(
            apiCall = { customerService.createTopup(CreateTopupRequest(command.amount.toDouble(), command.companyBankId)) },
            map = { response ->
                TopupRequestResult(
                    success = response.status,
                    message = response.message,
                    topupId = response.data?.id ?: 0
                )
            }
        )
    }

    override fun postVerificationTopup(topupId: Int, command: UploadImageCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = {
                val image = FileUtils.prepareFileImagePart(context, "img_proof", command.imagePath)
                customerService.uploadTopupProof(topupId, image)
            },
            map = { it.toOperationResult() }
        )
    }

    override suspend fun listWithdraws(page: Int, pageSize: Int): Result<List<Withdraw>> {
        return try {
            val response = customerService.listWithdraws(page, pageSize)
            Result.success(response.body()?.data?.withdraws?.map { it.toWithdraw() } ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getRequestWithdrawConfig(): Flow<Resource<RequestWithdrawConfig>> {
        return safeApiCall(
            apiCall = { customerService.getWithdrawConfig() },
            map = { it.data?.toRequestWithdrawConfig() ?: throw Exception("Empty data") }
        )
    }

    override fun postRequestWithdraw(command: WithdrawRequestCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerService.createWithdraw(CreateWithdrawRequest(command.amount.toDouble(), command.accountBankId)) },
            map = { it.toOperationResult() }
        )
    }
}
