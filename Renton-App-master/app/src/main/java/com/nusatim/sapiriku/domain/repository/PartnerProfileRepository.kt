package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.PartnerAccountDetail
import com.nusatim.sapiriku.domain.model.command.RegisterPartnerCommand
import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import kotlinx.coroutines.flow.Flow

interface PartnerProfileRepository {
    fun register(command: RegisterPartnerCommand): Flow<Resource<OperationResult>>
    fun getDetail(): Flow<Resource<PartnerAccountDetail>>
    fun changeCompanyName(companyName: String): Flow<Resource<OperationResult>>
    fun changeDescription(description: String): Flow<Resource<OperationResult>>
    fun changeAddress(address: String): Flow<Resource<OperationResult>>
    fun changeRegency(regenciesId: Int): Flow<Resource<OperationResult>>
    fun changeBusinessLocation(latitude: Double, longitude: Double): Flow<Resource<OperationResult>>
    fun uploadProfileImage(command: UploadImageCommand): Flow<Resource<OperationResult>>
}
