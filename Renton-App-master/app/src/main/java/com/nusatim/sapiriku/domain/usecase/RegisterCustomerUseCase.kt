package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.command.RegisterCustomerCommand
import com.nusatim.sapiriku.domain.repository.AuthRepository
import javax.inject.Inject

class RegisterCustomerUseCase @Inject constructor(
    private val authRepository: AuthRepository
) {
    operator fun invoke(command: RegisterCustomerCommand) =
        authRepository.register(command)
}
