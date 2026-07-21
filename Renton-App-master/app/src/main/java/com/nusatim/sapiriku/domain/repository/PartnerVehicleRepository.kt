package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.*
import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import kotlinx.coroutines.flow.Flow

interface PartnerVehicleRepository {
    fun getFunctionalType(): Flow<Resource<List<BasicData>>>
    fun getInputConfig(functionalType: Int): Flow<Resource<InputVehicleConfig>>
    fun getInputVehicleModel(brandId: Int): Flow<Resource<List<BasicData>>>
    fun postVehicle(form: Map<String, String>, photos: List<String>): Flow<Resource<OperationResult>>
    suspend fun uploadVehicleImage(command: UploadImageCommand): Result<UploadImageResult>
    suspend fun listVehicles(param: Map<String, String>): Result<PartnerVehicleSearchResult>
    fun getVehicleDetail(id: Int): Flow<Resource<Vehicle>>
    suspend fun deleteVehiclePhoto(id: Int): Result<Unit>
    fun deleteVehicle(id: Int): Flow<Resource<OperationResult>>
    fun getConfig(): Flow<Resource<RentVehicleConfig>>
    fun updateConfig(form: Map<String, String>): Flow<Resource<OperationResult>>
}
