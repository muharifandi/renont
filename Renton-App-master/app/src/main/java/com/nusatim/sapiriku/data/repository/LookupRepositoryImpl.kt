package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.api.service.BasicService
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.domain.model.*
import com.nusatim.sapiriku.domain.repository.LookupRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class LookupRepositoryImpl @Inject constructor(
    private val basicService: BasicService
) : BaseRepository(), LookupRepository {

    override fun getRegencies(query: String): Flow<Resource<List<Regencies>>> {
        return safeApiCall(
            apiCall = { basicService.getRegencies(query) },
            map = { response -> response.data?.regencies?.map { it.toRegencies() } ?: emptyList() }
        )
    }

    override fun getActiveRegencies(): Flow<Resource<List<BasicData>>> {
        return safeApiCall(
            apiCall = { basicService.getActiveRegencies() },
            map = { response -> response.data?.regencies?.map { it.toBasicData() } ?: emptyList() }
        )
    }

    override fun checkEmail(email: String): Flow<Resource<ValidationResult>> {
        return safeApiCall(
            apiCall = { basicService.checkEmail(email) },
            map = { response -> response.data?.toValidationResult(response.message) ?: throw Exception("Empty data") }
        )
    }

    override fun checkPhone(phone: String): Flow<Resource<ValidationResult>> {
        return safeApiCall(
            apiCall = { basicService.checkPhone(phone) },
            map = { response -> response.data?.toValidationResult(response.message) ?: throw Exception("Empty data") }
        )
    }

    override fun checkAgent(agentId: String): Flow<Resource<ValidationResult>> {
        return safeApiCall(
            apiCall = { basicService.checkAgent(agentId) },
            map = { response -> response.data?.toValidationResult() ?: throw Exception("Empty data") }
        )
    }

    override fun checkApplicationStatus(): Flow<Resource<ApplicationStatus>> {
        return safeApiCall(
            apiCall = { basicService.applicationStatus() },
            map = { response -> response.data?.toApplicationStatus() ?: throw Exception("Empty data") }
        )
    }
}
