package com.rentone.user.domain.repository

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.ApplicationStatus
import com.rentone.user.domain.model.BasicData
import com.rentone.user.domain.model.Regencies
import com.rentone.user.domain.model.ValidationResult
import kotlinx.coroutines.flow.Flow

interface LookupRepository {
    fun getRegencies(query: String): Flow<Resource<List<Regencies>>>
    fun getActiveRegencies(): Flow<Resource<List<BasicData>>>
    fun checkEmail(email: String): Flow<Resource<ValidationResult>>
    fun checkPhone(phone: String): Flow<Resource<ValidationResult>>
    fun checkAgent(agentId: String): Flow<Resource<ValidationResult>>
    fun checkApplicationStatus(): Flow<Resource<ApplicationStatus>>
}
