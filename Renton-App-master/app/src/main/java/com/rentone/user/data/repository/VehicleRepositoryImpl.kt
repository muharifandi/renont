package com.rentone.user.data.repository

import com.rentone.user.api.service.BasicService
import com.rentone.user.api.service.RentVehicleService
import com.rentone.user.core.common.Resource
import com.rentone.user.data.mapper.*
import com.rentone.user.domain.model.*
import com.rentone.user.domain.model.command.CheckoutCommand
import com.rentone.user.domain.repository.VehicleRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class VehicleRepositoryImpl @Inject constructor(
    private val basicService: BasicService,
    private val rentVehicleService: RentVehicleService
) : BaseRepository(), VehicleRepository {

    override fun getRecommendationVehicles(): Flow<Resource<List<Vehicle>>> {
        return safeApiCall(
            apiCall = { basicService.getRecomendationRentVehicle() },
            map = { it.vehicles }
        )
    }

    override suspend fun listVehicles(params: Map<String, String>): Result<VehicleSearchResult> {
        return try {
            val response = rentVehicleService.listVehicle(params)
            val body = response.body()
            if (response.isSuccessful && body != null) {
                Result.success(body.toSearchResult())
            } else {
                Result.failure(Exception(body?.message ?: "Failed to load vehicles"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getVehicleDetail(id: Int): Flow<Resource<VehicleDetail>> {
        return safeApiCall(
            apiCall = { rentVehicleService.getVehicleDetail(id) },
            map = { it.toVehicleDetail() }
        )
    }

    override suspend fun listVehicleReviews(vehicleId: Int, page: Int, pageSize: Int): Result<ReviewSearchResult> {
        return try {
            val param = mapOf(
                "id" to vehicleId.toString(),
                "page" to page.toString(),
                "limit" to pageSize.toString()
            )
            val response = rentVehicleService.listVehicleReview(param)
            val body = response.body()
            if (response.isSuccessful && body != null) {
                Result.success(ReviewSearchResult(body.reviews, body.reviewTotal))
            } else {
                Result.failure(Exception("Failed to load reviews"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun checkVoucher(code: String, startDate: String?): Flow<Resource<Voucher>> {
        return safeApiCall(
            apiCall = { 
                val form = mapOf("code" to code.uppercase(), "start_date" to startDate.orEmpty())
                rentVehicleService.checkVoucherCheckout(form)
            },
            map = { it.toVoucher() }
        )
    }

    override fun getCheckoutDetail(vehicleId: Int, pricePackage: Int, startDate: String?, endDate: String?): Flow<Resource<CheckoutDetail>> {
        return safeApiCall(
            apiCall = { 
                val form = mapOf(
                    "vehicle_id" to vehicleId.toString(),
                    "price_package" to pricePackage.toString(),
                    "start_date" to startDate.orEmpty(),
                    "end_date" to endDate.orEmpty()
                )
                rentVehicleService.checkoutDetail(form)
            },
            map = { it.toCheckoutDetail() }
        )
    }

    override fun postCheckout(command: CheckoutCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { 
                val form = mapOf(
                    "vehicle_id" to command.vehicleId.toString(),
                    "price_package" to command.pricePackageId.toString(),
                    "start_date" to command.startDate,
                    "end_date" to command.endDate,
                    "voucher_code" to command.voucherCode.orEmpty(),
                    "notes" to command.notes.orEmpty()
                )
                rentVehicleService.postCheckout(form)
            },
            map = { it.toOperationResult() }
        )
    }
}
