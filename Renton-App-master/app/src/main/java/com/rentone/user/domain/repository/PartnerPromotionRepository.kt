package com.rentone.user.domain.repository

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.FilterList
import com.rentone.user.domain.model.InputPromoteRentVehicleConfig
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.PromoteVehicle
import kotlinx.coroutines.flow.Flow

interface PartnerPromotionRepository {
    suspend fun listPromoteVehicles(page: Int, pageSize: Int, sortIndex: Int, filterList: FilterList): Result<List<PromoteVehicle>>
    fun getPromoteInputConfig(): Flow<Resource<InputPromoteRentVehicleConfig>>
    fun postPromote(itemId: Int, startDate: String, endDate: String): Flow<Resource<OperationResult>>
    fun cancelPromote(id: Int): Flow<Resource<OperationResult>>
}
