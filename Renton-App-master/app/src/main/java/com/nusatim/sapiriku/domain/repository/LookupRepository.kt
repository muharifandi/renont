package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.ApplicationStatus
import com.nusatim.sapiriku.domain.model.BasicData
import com.nusatim.sapiriku.domain.model.Regencies
import com.nusatim.sapiriku.domain.model.ValidationResult
import kotlinx.coroutines.flow.Flow

interface LookupRepository {
    fun getRegencies(query: String): Flow<Resource<List<Regencies>>>
    fun getActiveRegencies(): Flow<Resource<List<BasicData>>>
    fun checkEmail(email: String): Flow<Resource<ValidationResult>>
    fun checkPhone(phone: String): Flow<Resource<ValidationResult>>
    fun checkAgent(agentId: String): Flow<Resource<ValidationResult>>
    fun checkApplicationStatus(): Flow<Resource<ApplicationStatus>>
}
