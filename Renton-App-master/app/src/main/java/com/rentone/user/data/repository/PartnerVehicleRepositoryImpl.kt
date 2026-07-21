package com.rentone.user.data.repository

import android.content.Context
import com.rentone.user.api.service.PartnerRentService
import com.rentone.user.core.common.Resource
import com.rentone.user.core.util.FileUtils
import com.rentone.user.data.mapper.toDomain
import com.rentone.user.domain.model.*
import com.rentone.user.domain.model.command.UploadImageCommand
import com.rentone.user.domain.repository.PartnerVehicleRepository
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
            map = { it.toDomain() }
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
            map = { OperationResult(it.status, it.message ?: "") }
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

    override suspend fun listVehicles(param: Map<String, String>): Result<List<Vehicle>> {
        return try {
            val response = partnerRentService.listVehicle(param)
            Result.success(response.body()?.vehicles ?: emptyList())
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    override fun getVehicleDetail(id: Int): Flow<Resource<Vehicle>> {
        return safeApiCall(
            apiCall = { partnerRentService.getVehicleDetail(id) },
            map = { it.vehicle }
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
            map = { OperationResult(it.status, it.message ?: "") }
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
            map = { OperationResult(it.status, it.message ?: "") }
        )
    }
}
