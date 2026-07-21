package com.nusatim.sapiriku.data.repository

import android.content.Context
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
            apiCall = { partnerRentService.getFunctionalType() },
            map = { it.type }
        )
    }

    override fun getInputConfig(functionalType: Int): Flow<Resource<InputVehicleConfig>> {
        return safeApiCall(
            apiCall = { partnerRentService.getInputConfig(functionalType) },
            map = { it.toInputVehicleConfig() }
        )
    }

    override fun getInputVehicleModel(brandId: Int): Flow<Resource<List<BasicData>>> {
        return safeApiCall(
            apiCall = { partnerRentService.getInputVehicleModel(brandId) },
            map = { it.data }
        )
    }

    override fun postVehicle(form: Map<String, String>, photos: List<String>): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.postVehicle(form, photos) },
            map = { it.toOperationResult() }
        )
    }

    override suspend fun uploadVehicleImage(command: UploadImageCommand): Result<UploadImageResult> {
        return try {
            val imagePart = FileUtils.prepareFileImagePart(context, "photo", command.imagePath)
            val response = partnerRentService.uploadVehicleImage(imagePart)
            val body = response.body()
            if (response.isSuccessful && body != null) {
                Result.success(UploadImageResult(body.status, body.message, body.filename))
            } else {
                Result.failure(Exception(body?.message ?: "Upload failed"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override suspend fun listVehicles(param: Map<String, String>): Result<PartnerVehicleSearchResult> {
        return try {
            val response = partnerRentService.listVehicle(param)
            val body = response.body()
            if (response.isSuccessful && body != null) {
                Result.success(PartnerVehicleSearchResult(
                    vehicles = body.vehicles,
                    functionalType = body.functionalType,
                    priceMin = body.priceMin,
                    priceMax = body.priceMax
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
            map = { it.vehicle ?: Vehicle() }
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
            apiCall = { partnerRentService.config() },
            map = { it.rentVehicleConfig ?: RentVehicleConfig() }
        )
    }

    override fun updateConfig(form: Map<String, String>): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRentService.updateConfig(form) },
            map = { it.toOperationResult() }
        )
    }
}
