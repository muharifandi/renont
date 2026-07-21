package com.nusatim.sapiriku.data.repository

import android.content.Context
import com.nusatim.sapiriku.api.model.ChangePasswordRequest
import com.nusatim.sapiriku.api.model.UpdateNameRequest
import com.nusatim.sapiriku.api.service.CustomerService
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.util.FileUtils
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.domain.model.CustomerAccountDetail
import com.nusatim.sapiriku.domain.model.HomeData
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import com.nusatim.sapiriku.domain.repository.CustomerAccountRepository
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
            apiCall = { customerService.getDetail() },
            map = { response -> response.data?.toCustomerAccountDetail() ?: throw Exception("Empty data") }
        )
    }

    override fun changeName(firstName: String, lastName: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerService.updateName(UpdateNameRequest(firstName, lastName)) },
            map = { it.toOperationResult() }
        )
    }

    override fun changePassword(oldPassword: String, newPassword: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { customerService.changePassword(ChangePasswordRequest(oldPassword, newPassword)) },
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
            apiCall = { customerService.getHome() },
            map = { response -> response.data?.toHomeDataDomain() ?: throw Exception("Empty data") }
        )
    }
}
