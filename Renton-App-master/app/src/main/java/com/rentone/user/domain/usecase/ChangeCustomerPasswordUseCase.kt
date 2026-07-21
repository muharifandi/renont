package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.CustomerAccountRepository
import javax.inject.Inject

class ChangeCustomerPasswordUseCase @Inject constructor(
    private val customerAccountRepository: CustomerAccountRepository
) {
    operator fun invoke(oldPassword: String, newPassword: String) =
        customerAccountRepository.changePassword(oldPassword, newPassword)
}
