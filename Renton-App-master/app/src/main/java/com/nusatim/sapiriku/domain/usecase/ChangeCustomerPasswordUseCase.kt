package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerAccountRepository
import javax.inject.Inject

class ChangeCustomerPasswordUseCase @Inject constructor(
    private val customerAccountRepository: CustomerAccountRepository
) {
    operator fun invoke(oldPassword: String, newPassword: String) =
        customerAccountRepository.changePassword(oldPassword, newPassword)
}
