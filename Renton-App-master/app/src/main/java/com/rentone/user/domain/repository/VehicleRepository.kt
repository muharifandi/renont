package com.rentone.user.domain.repository

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.*
import com.rentone.user.domain.model.command.CheckoutCommand
import kotlinx.coroutines.flow.Flow

interface VehicleRepository {
    fun getRecommendationVehicles(): Flow<Resource<List<Vehicle>>>
    suspend fun listVehicles(params: Map<String, String>): Result<VehicleSearchResult>
    fun getVehicleDetail(id: Int): Flow<Resource<VehicleDetail>>
    suspend fun listVehicleReviews(vehicleId: Int, page: Int, pageSize: Int): Result<List<Review>>
    fun checkVoucher(code: String, startDate: String?): Flow<Resource<Voucher>>
    fun getCheckoutDetail(vehicleId: Int, pricePackage: Int, startDate: String?, endDate: String?): Flow<Resource<CheckoutDetail>>
    fun postCheckout(command: CheckoutCommand): Flow<Resource<OperationResult>>
}
