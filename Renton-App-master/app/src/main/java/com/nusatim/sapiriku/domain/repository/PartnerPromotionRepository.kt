package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.FilterList
import com.nusatim.sapiriku.domain.model.InputPromoteRentVehicleConfig
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.PromoteVehicle
import kotlinx.coroutines.flow.Flow

interface PartnerPromotionRepository {
    suspend fun listPromoteVehicles(page: Int, pageSize: Int, sortIndex: Int, filterList: FilterList): Result<List<PromoteVehicle>>
    fun getPromoteInputConfig(): Flow<Resource<InputPromoteRentVehicleConfig>>
    fun postPromote(itemId: Int, startDate: String, endDate: String): Flow<Resource<OperationResult>>
    fun cancelPromote(id: Int): Flow<Resource<OperationResult>>
}
