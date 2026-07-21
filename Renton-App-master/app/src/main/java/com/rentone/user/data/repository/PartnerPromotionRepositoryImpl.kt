package com.rentone.user.data.repository

import com.rentone.user.api.service.PartnerRentService
import com.rentone.user.core.common.Resource
import com.rentone.user.data.mapper.*
import com.rentone.user.domain.model.FilterList
import com.rentone.user.domain.model.InputPromoteRentVehicleConfig
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.PromoteVehicle
import com.rentone.user.domain.repository.PartnerPromotionRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PartnerPromotionRepositoryImpl @Inject constructor(
    private val partnerRentService: PartnerRentService
) : BaseRepository(), PartnerPromotionRepository {

    override suspend fun listPromoteVehicles(page: Int, pageSize: Int, sortIndex: Int, filterList: FilterList): Result<List<PromoteVehicle>> {
        return try {
            val params = filterList.toQueryMap().toMutableMap()
            params["page"] = page.toString()
            params["limit"] = pageSize.toString()
            params["sort"] = sortIndex.toString()
            val response = partnerRentService.listPromoteVehicle(params)
            Result.success(response.body()?.promoteVehicles ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getPromoteInputConfig(): Flow<Resource<InputPromoteRentVehicleConfig>> {
        return safeApiCall(
            apiCall = { partnerRentService.getPromoteInputConfig() },
            map = { it.toInputPromoteRentVehicleConfig() }
        )
    }

    override fun postPromote(itemId: Int, startDate: String, endDate: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { 
                partnerRentService.postPromote(mapOf(
                    "item_id" to itemId.toString(),
                    "start_date" to startDate,
                    "end_date" to endDate
                ))
            },
            map = { it.toOperationResult() }
        )
    }

    override fun cancelPromote(id: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.cancelPromote(id) },
            map = { it.toOperationResult() }
        )
    }
}
