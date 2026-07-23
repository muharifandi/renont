package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.api.model.CreateBookingRequest
import com.nusatim.sapiriku.api.model.QuoteRequest
import com.nusatim.sapiriku.api.model.VoucherCheckRequest
import com.nusatim.sapiriku.api.service.RentVehicleService
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.domain.model.*
import com.nusatim.sapiriku.domain.model.command.CheckoutCommand
import com.nusatim.sapiriku.domain.repository.VehicleRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class VehicleRepositoryImpl @Inject constructor(
    private val rentVehicleService: RentVehicleService
) : BaseRepository(), VehicleRepository {

    override fun getRecommendationVehicles(): Flow<Resource<List<Vehicle>>> {
        return safeApiCall(
            apiCall = { rentVehicleService.listVehicle(page = 1, limit = 10) },
            map = { response -> response.data?.vehicles?.map { it.toVehicle() } ?: emptyList() }
        )
    }

    override suspend fun listVehicles(params: Map<String, String>): Result<VehicleSearchResult> {
        return try {
            val response = rentVehicleService.listVehicle(
                functionalType = params["functional_type"]?.toInt(),
                startDate = params["start_date"],
                endDate = params["end_date"],
                page = params["page"]?.toInt() ?: 1,
                limit = params["limit"]?.toInt() ?: 10,
                sort = params["sort"]?.toInt(),
                status = params["status"]?.toInt(),
                minPassenger = params["min_passenger"]?.toInt(),
                maxPassenger = params["max_passenger"]?.toInt(),
                minPrice = params["min_price"]?.toDouble(),
                maxPrice = params["max_price"]?.toDouble(),
                functionalTypeSelected = params["vehicle_functional_type_selected"],
                withDriver = params["with_driver"]?.toInt(),
                regency = params["regency"]
            )
            val body = response.body()
            if (response.isSuccessful && body != null) {
                Result.success(VehicleSearchResult(
                    vehicles = body.data?.vehicles?.map { it.toVehicle() } ?: emptyList(),
                    priceMin = body.data?.priceMin ?: 0.0,
                    priceMax = body.data?.priceMax ?: 0.0,
                    regencies = body.data?.regency,
                    functionalType = body.data?.functionalType?.map { it.toBasicData() } ?: emptyList()
                ))
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
            map = { response ->
                VehicleDetail(
                    vehicle = response.data!!.vehicle.toVehicleDomain(),
                    vehicleBooked = emptyList(), 
                    partner = response.data.partner?.toPartnerDetailDomain(),
                    reviews = response.data.review.map { Review(it.id, it.name, it.imgProfile, it.comment, it.rating ?: 0, it.dateModified) },
                    reviewTotal = response.data.reviewTotal,
                    forceWithDriver = response.data.forceWithDriver
                )
            }
        )
    }

    override suspend fun listVehicleReviews(vehicleId: Int, page: Int, pageSize: Int): Result<ReviewSearchResult> {
        return try {
            val response = rentVehicleService.listVehicleReviews(vehicleId, page, pageSize)
            val body = response.body()
            if (response.isSuccessful && body != null) {
                Result.success(ReviewSearchResult(
                    reviews = body.data?.review?.map { Review(it.id, it.name, it.imgProfile, it.comment, it.rating ?: 0, it.dateModified) } ?: emptyList(),
                    reviewTotal = body.data?.reviewTotal ?: 0
                ))
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
                rentVehicleService.checkVoucher(VoucherCheckRequest(code.uppercase(), startDate))
            },
            map = { response -> response.data?.voucher?.toVoucher() ?: throw Exception("Empty data") }
        )
    }

    override fun getCheckoutDetail(vehicleId: Int, pricePackage: Int, startDate: String?, endDate: String?): Flow<Resource<CheckoutDetail>> {
        return safeApiCall(
            apiCall = { 
                rentVehicleService.quote(QuoteRequest(vehicleId, pricePackage, startDate.orEmpty(), endDate.orEmpty()))
            },
            map = { response -> response.data?.toCheckoutDetail() ?: throw Exception("Empty data") }
        )
    }

    override fun postCheckout(command: CheckoutCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { 
                val request = CreateBookingRequest(
                    itemId = command.vehicleId,
                    pricePackage = command.pricePackageId,
                    startDate = command.startDate,
                    endDate = command.endDate,
                    voucherId = null, // command.voucherId not in domain CheckoutCommand?
                    description = command.notes
                )
                rentVehicleService.createBooking(request)
            },
            map = { it.toOperationResult() }
        )
    }
}
