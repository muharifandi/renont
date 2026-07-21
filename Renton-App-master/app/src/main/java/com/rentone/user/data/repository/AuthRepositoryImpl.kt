package com.rentone.user.data.repository

import com.rentone.user.api.service.CustomerService
import com.rentone.user.core.common.Resource
import com.rentone.user.core.util.FileUtils
import com.rentone.user.domain.model.LoginResult
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.command.RegisterCustomerCommand
import com.rentone.user.domain.repository.AuthRepository
import android.content.Context
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AuthRepositoryImpl @Inject constructor(
    private val customerService: CustomerService,
    @ApplicationContext private val context: Context
) : BaseRepository(), AuthRepository {

    override fun login(email: String, password: String): Flow<Resource<LoginResult>> {
        return safeApiCall(
            apiCall = { customerService.login(mapOf("email" to email, "password" to password)) },
            map = { response -> 
                LoginResult(
                    id = response.id,
                    key = response.key,
                    message = response.message
                )
            }
        )
    }

    override fun register(command: RegisterCustomerCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = {
                val form = mutableMapOf(
                    "first_name" to FileUtils.createPartFromString(command.firstName),
                    "last_name" to FileUtils.createPartFromString(command.lastName),
                    "email" to FileUtils.createPartFromString(command.email),
                    "phone" to FileUtils.createPartFromString(command.phone),
                    "identity_number" to FileUtils.createPartFromString(command.identityNumber),
                    "password" to FileUtils.createPartFromString(command.password)
                )
                val files = mutableListOf<okhttp3.MultipartBody.Part>()
                command.profileImagePath?.let {
                    files.add(FileUtils.prepareFileImagePart(context, "img_profile", it))
                }
                command.identityImagePath?.let {
                    files.add(FileUtils.prepareFileImagePart(context, "img_identity", it))
                }
                customerService.register(form, files)
            },
            map = { response -> OperationResult(response.status, response.message ?: "") }
        )
    }
}
