package com.rentone.user.data.repository

import android.content.Context
import com.rentone.user.api.service.CustomerService
import com.rentone.user.core.common.Resource
import com.rentone.user.core.util.FileUtils
import com.rentone.user.data.mapper.*
import com.rentone.user.domain.model.CustomerAccountDetail
import com.rentone.user.domain.model.HomeData
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.command.UploadImageCommand
import com.rentone.user.domain.repository.CustomerAccountRepository
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class CustomerAccountRepositoryImpl @Inject constructor(
    private val customerService: CustomerService,
    @ApplicationContext private val context: Context
) : BaseRepository(), CustomerAccountRepository {

    override fun getDetail(): Flow<Resource<CustomerAccountDetail>> {
        return safeApiCall(
            apiCall = { customerService.detail() },
            map = { it.toCustomerAccountDetail() }
        )
    }

    override fun changeName(firstName: String, lastName: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerService.changeName(mapOf("first_name" to firstName, "last_name" to lastName)) },
            map = { it.toOperationResult() }
        )
    }

    override fun changePassword(oldPassword: String, newPassword: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerService.changePassword(mapOf("old_password" to oldPassword, "new_password" to newPassword)) },
            map = { it.toOperationResult() }
        )
    }

    override fun uploadProfileImage(command: UploadImageCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = {
                val imagePart = FileUtils.prepareFileImagePart(context, "img_profile", command.imagePath)
                customerService.uploadProfileImage(imagePart)
            },
            map = { it.toOperationResult() }
        )
    }

    override fun getHomeData(): Flow<Resource<HomeData>> {
        return safeApiCall(
            apiCall = { customerService.home() },
            map = { it.toHomeData() }
        )
    }
}
