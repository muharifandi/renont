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
            map = { it.regencies }
        )
    }

    override fun getActiveRegencies(): Flow<Resource<List<BasicData>>> {
        return safeApiCall(
            apiCall = { basicService.getActiveRegencies() },
            map = { it.data }
        )
    }

    override fun checkEmail(email: String): Flow<Resource<ValidationResult>> {
        return safeApiCall(
            apiCall = { basicService.checkEmail(email) },
            map = { it.toValidationResult() }
        )
    }

    override fun checkPhone(phone: String): Flow<Resource<ValidationResult>> {
        return safeApiCall(
            apiCall = { basicService.checkPhone(phone) },
            map = { it.toValidationResult() }
        )
    }

    override fun checkAgent(agentId: String): Flow<Resource<ValidationResult>> {
        return safeApiCall(
            apiCall = { basicService.checkAgent(agentId) },
            map = { it.toValidationResult() }
        )
    }

    override fun checkApplicationStatus(): Flow<Resource<ApplicationStatus>> {
        return safeApiCall(
            apiCall = { basicService.applicationStatus() },
            map = { it.toApplicationStatus() }
        )
    }
}
