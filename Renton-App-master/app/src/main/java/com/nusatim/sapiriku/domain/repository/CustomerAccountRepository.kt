package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.CustomerAccountDetail
import com.nusatim.sapiriku.domain.model.HomeData
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import kotlinx.coroutines.flow.Flow

interface CustomerAccountRepository {
    fun getDetail(): Flow<Resource<CustomerAccountDetail>>
    fun changeName(firstName: String, lastName: String): Flow<Resource<OperationResult>>
    fun changePassword(oldPassword: String, newPassword: String): Flow<Resource<OperationResult>>
    fun uploadProfileImage(command: UploadImageCommand): Flow<Resource<OperationResult>>
    fun getHomeData(): Flow<Resource<HomeData>>
}
