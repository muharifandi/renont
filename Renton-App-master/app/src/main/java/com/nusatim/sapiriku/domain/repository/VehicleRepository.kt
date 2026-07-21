package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.*
import com.nusatim.sapiriku.domain.model.command.CheckoutCommand
import kotlinx.coroutines.flow.Flow

interface VehicleRepository {
    fun getRecommendationVehicles(): Flow<Resource<List<Vehicle>>>
    suspend fun listVehicles(params: Map<String, String>): Result<VehicleSearchResult>
    fun getVehicleDetail(id: Int): Flow<Resource<VehicleDetail>>
    suspend fun listVehicleReviews(vehicleId: Int, page: Int, pageSize: Int): Result<ReviewSearchResult>
    fun checkVoucher(code: String, startDate: String?): Flow<Resource<Voucher>>
    fun getCheckoutDetail(vehicleId: Int, pricePackage: Int, startDate: String?, endDate: String?): Flow<Resource<CheckoutDetail>>
    fun postCheckout(command: CheckoutCommand): Flow<Resource<OperationResult>>
}
