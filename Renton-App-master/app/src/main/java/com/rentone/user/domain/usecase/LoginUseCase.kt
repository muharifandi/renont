package com.rentone.user.domain.usecase

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.repository.AuthRepository
import com.rentone.user.domain.repository.UserRepository
import kotlinx.coroutines.flow.flow
import javax.inject.Inject

class LoginUseCase @Inject constructor(
    private val authRepository: AuthRepository,
    private val userRepository: UserRepository
) {
    operator fun invoke(auth: Map<String, String>) = flow {
        val email = auth["email"] ?: ""
        val password = auth["password"] ?: ""
        authRepository.login(email, password).collect { resource ->
            when (resource) {
                is Resource.Success -> {
                    userRepository.saveUser(resource.data.id, resource.data.key)
                    emit(Resource.Success(Unit))
                }
                is Resource.Loading -> emit(Resource.Loading)
                is Resource.Error -> emit(resource)
                is Resource.Empty -> emit(Resource.Empty)
            }
        }
    }
}
