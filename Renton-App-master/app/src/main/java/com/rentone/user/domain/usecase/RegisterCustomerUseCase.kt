package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.command.RegisterCustomerCommand
import com.rentone.user.domain.repository.AuthRepository
import javax.inject.Inject

class RegisterCustomerUseCase @Inject constructor(
    private val authRepository: AuthRepository
) {
    operator fun invoke(command: RegisterCustomerCommand) =
        authRepository.register(command)
}
