package com.rentone.user.domain.repository

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.LoginResult
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.command.RegisterCustomerCommand
import kotlinx.coroutines.flow.Flow

interface AuthRepository {
    fun login(email: String, password: String): Flow<Resource<LoginResult>>
    fun register(command: RegisterCustomerCommand): Flow<Resource<OperationResult>>
}
