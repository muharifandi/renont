package com.rentone.user.domain.repository

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.CustomerAccountDetail
import com.rentone.user.domain.model.HomeData
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.command.UploadImageCommand
import kotlinx.coroutines.flow.Flow

interface CustomerAccountRepository {
    fun getDetail(): Flow<Resource<CustomerAccountDetail>>
    fun changeName(firstName: String, lastName: String): Flow<Resource<OperationResult>>
    fun changePassword(oldPassword: String, newPassword: String): Flow<Resource<OperationResult>>
    fun uploadProfileImage(command: UploadImageCommand): Flow<Resource<OperationResult>>
    fun getHomeData(): Flow<Resource<HomeData>>
}
