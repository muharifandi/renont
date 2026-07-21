package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.api.service.CustomerService
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.util.FileUtils
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.domain.model.LoginResult
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.command.RegisterCustomerCommand
import com.nusatim.sapiriku.domain.repository.AuthRepository
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
            map = { it.toOperationResult() }
        )
    }
}
