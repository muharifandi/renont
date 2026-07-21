package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.api.model.CreatePromotionRequest
import com.nusatim.sapiriku.api.service.PartnerRentService
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.domain.model.FilterList
import com.nusatim.sapiriku.domain.model.InputPromoteRentVehicleConfig
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.PromoteVehicle
import com.nusatim.sapiriku.domain.repository.PartnerPromotionRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PartnerPromotionRepositoryImpl @Inject constructor(
    private val partnerRentService: PartnerRentService
) : BaseRepository(), PartnerPromotionRepository {

    override suspend fun listPromoteVehicles(page: Int, pageSize: Int, sortIndex: Int, filterList: FilterList): Result<List<PromoteVehicle>> {
        return try {
            val response = partnerRentService.listPromotions(page, pageSize)
            Result.success(response.body()?.data?.promotes?.map { it.toPromoteVehicle() } ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getPromoteInputConfig(): Flow<Resource<InputPromoteRentVehicleConfig>> {
        return safeApiCall(
            apiCall = { partnerRentService.getPromotionInputConfig() },
            map = { it.data?.toInputPromoteRentVehicleConfig() ?: throw Exception("Empty data") }
        )
    }

    override fun postPromote(itemId: Int, startDate: String, endDate: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { 
                partnerRentService.createPromotion(CreatePromotionRequest(itemId, startDate, endDate))
            },
            map = { it.toOperationResult() }
        )
    }

    override fun cancelPromote(id: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.cancelPromotion(id) },
            map = { it.toOperationResult() }
        )
    }
}
