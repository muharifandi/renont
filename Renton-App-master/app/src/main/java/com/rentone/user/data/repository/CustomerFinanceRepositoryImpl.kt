package com.rentone.user.data.repository

import android.content.Context
import com.rentone.user.api.service.CustomerService
import com.rentone.user.core.common.Resource
import com.rentone.user.core.util.FileUtils
import com.rentone.user.data.mapper.*
import com.rentone.user.domain.model.*
import com.rentone.user.domain.model.command.*
import com.rentone.user.domain.repository.CustomerFinanceRepository
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
            apiCall = { customerService.balance() },
            map = { it.balance }
        )
    }

    override fun getPoint(): Flow<Resource<Double>> {
        return safeApiCall(
            apiCall = { customerService.point() },
            map = { it.point.toDouble() }
        )
    }

    override suspend fun listTransactionPoints(page: Int, pageSize: Int): Result<List<TransactionPoint>> {
        return try {
            val response = customerService.listTransactionPoint(mapOf("page" to page.toString(), "limit" to pageSize.toString()))
            Result.success(response.body()?.transactionPoints ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getExchangePointConfig(): Flow<Resource<ExchangePointConfig>> {
        return safeApiCall(
            apiCall = { customerService.getExchangePointConfig() },
            map = { it.toExchangePointConfig() }
        )
    }

    override fun postExchangePoint(point: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerService.postExchangePoint(mapOf("point" to point)) },
            map = { it.toOperationResult() }
        )
    }

    override fun listBanks(): Flow<Resource<List<CustomerBank>>> {
        return safeApiCall(
            apiCall = { customerService.banks() },
            map = { it.customerBanks }
        )
    }

    override fun getBankDetail(id: Int): Flow<Resource<CustomerBank?>> {
        return safeApiCall(
            apiCall = { customerService.bankDetail(id) },
            map = { it.bank }
        )
    }

    override fun getBankInputConfig(): Flow<Resource<List<Bank>>> {
        return safeApiCall(
            apiCall = { customerService.getBankInputConfig() },
            map = { it.banks }
        )
    }

    override fun postBank(command: AddBankCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { 
                val form = mutableMapOf(
                    "bank_id" to command.bankId.toString(),
                    "name" to command.accountName,
                    "bank_number" to command.accountNumber
                )
                command.id?.let { form["id"] = it.toString() }
                customerService.postBank(form)
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
            val response = customerService.listTopup(mapOf("page" to page.toString(), "limit" to pageSize.toString()))
            Result.success(response.body()?.topups ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getTopupDetail(topupId: Int): Flow<Resource<Topup?>> {
        return safeApiCall(
            apiCall = { customerService.topupDetail(topupId) },
            map = { it.detail }
        )
    }

    override fun getRequestTopupConfig(): Flow<Resource<RequestTopupConfig>> {
        return safeApiCall(
            apiCall = { customerService.getRequestTopupConfig() },
            map = { it.toRequestTopupConfig() }
        )
    }

    override fun postRequestTopup(command: TopupRequestCommand): Flow<Resource<TopupRequestResult>> {
        return safeApiCall(
            apiCall = { customerService.postRequestTopup(mapOf("company_bank_id" to command.companyBankId.toString(), "value" to command.amount)) },
            map = { it.toTopupRequestResult() }
        )
    }

    override fun postVerificationTopup(topupId: Int, command: UploadImageCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = {
                val form = mapOf("topup_id" to FileUtils.createPartFromString(topupId.toString()))
                val image = FileUtils.prepareFileImagePart(context, "img_proof", command.imagePath)
                customerService.verificationTopup(form, image)
            },
            map = { it.toOperationResult() }
        )
    }

    override suspend fun listWithdraws(page: Int, pageSize: Int): Result<List<Withdraw>> {
        return try {
            val response = customerService.listWithdraw(mapOf("page" to page.toString(), "limit" to pageSize.toString()))
            Result.success(response.body()?.withdraws ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getRequestWithdrawConfig(): Flow<Resource<RequestWithdrawConfig>> {
        return safeApiCall(
            apiCall = { customerService.getRequestWithdrawConfig() },
            map = { it.toRequestWithdrawConfig() }
        )
    }

    override fun postRequestWithdraw(command: WithdrawRequestCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerService.postRequestWithdraw(mapOf("account_bank_id" to command.accountBankId.toString(), "value" to command.amount)) },
            map = { it.toOperationResult() }
        )
    }
}
