package com.nusatim.sapiriku.data.repository

import android.content.Context
import com.nusatim.sapiriku.api.model.UpdatePartnerRentConfigRequest
import com.nusatim.sapiriku.api.model.VehiclePayloadRequest
import com.nusatim.sapiriku.api.service.PartnerRentService
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.util.FileUtils
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.domain.model.*
import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import com.nusatim.sapiriku.domain.repository.PartnerVehicleRepository
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PartnerVehicleRepositoryImpl @Inject constructor(
    private val partnerRentService: PartnerRentService,
    @ApplicationContext private val context: Context
) : BaseRepository(), PartnerVehicleRepository {

    override fun getFunctionalType(): Flow<Resource<List<BasicData>>> {
        return safeApiCall(
            apiCall = { partnerRentService.getFunctionalTypes() },
            map = { response -> response.data?.type?.map { it.toBasicData() } ?: emptyList() }
        )
    }

    override fun getInputConfig(functionalType: Int): Flow<Resource<InputVehicleConfig>> {
        return safeApiCall(
            apiCall = { partnerRentService.getVehicleInputConfig(functionalType) },
            map = { it.data?.toInputVehicleConfig() ?: throw Exception("Empty data") }
        )
    }

    override fun getInputVehicleModel(brandId: Int): Flow<Resource<List<BasicData>>> {
        return safeApiCall(
            apiCall = { partnerRentService.getVehicleModels(brandId) },
            map = { response -> response.data?.data?.map { it.toBasicData() } ?: emptyList() }
        )
    }

    override fun postVehicle(form: Map<String, String>, photos: List<String>): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { 
                val request = VehiclePayloadRequest(
                    title = form["title"].orEmpty(),
                    vehicleType = form["vehicle_type"]?.toInt() ?: 0,
                    brandId = form["brand_id"]?.toInt() ?: 0,
                    vehicleModel = form["vehicle_model"]?.toInt() ?: 0,
                    maxPassenger = form["max_passenger"]?.toInt() ?: 0,
                    year = form["year"]?.toInt() ?: 0,
                    colorId = form["color_id"]?.toInt() ?: 0,
                    transmitionType = form["transmition_type"]?.toInt() ?: 0,
                    drivenType = form["driven_type"]?.toInt() ?: 0,
                    fuelType = form["fuel_type"]?.toInt() ?: 0,
                    price = form["price"]?.toDouble() ?: 0.0,
                    functionalType = form["functional_type"]?.toInt() ?: 0,
                    photos = photos
                )
                val id = form["id"]?.toInt()
                if (id != null && id != 0) {
                    partnerRentService.updateVehicle(id, request)
                } else {
                    partnerRentService.createVehicle(request)
                }
            },
            map = { it.toOperationResult() }
        )
    }

    override suspend fun uploadVehicleImage(command: UploadImageCommand): Result<UploadImageResult> {
        return try {
            val imagePart = FileUtils.prepareFileImagePart(context, "photo", command.imagePath)
            val response = partnerRentService.uploadVehiclePhoto(imagePart)
            val body = response.body()
            if (response.isSuccessful && body != null) {
                Result.success(UploadImageResult(body.status, body.message, body.data?.filename))
            } else {
                Result.failure(Exception(body?.message ?: "Upload failed"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override suspend fun listVehicles(param: Map<String, String>): Result<PartnerVehicleSearchResult> {
        return try {
            val response = partnerRentService.listVehicles(
                page = param["page"]?.toInt() ?: 1,
                limit = param["limit"]?.toInt() ?: 10,
                sort = param["sort"]?.toInt(),
                status = param["status"]?.toInt()
            )
            val body = response.body()
            if (response.isSuccessful && body != null) {
                Result.success(PartnerVehicleSearchResult(
                    vehicles = body.data?.vehicles?.map { it.toVehicle() } ?: emptyList(),
                    functionalType = body.data?.functionalType?.map { it.toBasicData() } ?: emptyList(),
                    priceMin = body.data?.priceMin ?: 0.0,
                    priceMax = body.data?.priceMax ?: 0.0
                ))
            } else {
                Result.failure(Exception("Failed to load vehicles"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getVehicleDetail(id: Int): Flow<Resource<Vehicle>> {
        return safeApiCall(
            apiCall = { partnerRentService.getVehicleDetail(id) },
            map = { it.data?.vehicle?.toVehicleDomain() ?: Vehicle() }
        )
    }

    override suspend fun deleteVehiclePhoto(id: Int): Result<Unit> {
        return try {
            partnerRentService.deleteVehiclePhoto(id)
            Result.success(Unit)
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun deleteVehicle(id: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.deleteVehicle(id) },
            map = { it.toOperationResult() }
        )
    }

    override fun getConfig(): Flow<Resource<RentVehicleConfig>> {
        return safeApiCall(
            apiCall = { partnerRentService.getConfig() },
            map = { it.data?.rentConfig?.toRentVehicleConfig() ?: RentVehicleConfig() }
        )
    }

    override fun updateConfig(form: Map<String, String>): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { 
                val request = UpdatePartnerRentConfigRequest(
                    forceWithDriver = form["force_with_driver"]?.toInt(),
                    forceDisableDelivery = form["force_disable_delivery"]?.toInt(),
                    forceDisablePickoff = form["force_disable_pickoff"]?.toInt(),
                    deliveryFee = form["delivery_fee"]?.toDouble(),
                    pickoffFee = form["pickoff_fee"]?.toDouble(),
                    maxDayCod = form["max_day_cod"]?.toInt(),
                    overtimeFee = form["overtime_fee"]?.toDouble()
                )
                partnerRentService.updateConfig(request)
            },
            map = { it.toOperationResult() }
        )
    }
}
