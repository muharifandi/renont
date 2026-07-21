package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.CustomerAccountRepository
import javax.inject.Inject

class ChangeCustomerNameUseCase @Inject constructor(
    private val customerAccountRepository: CustomerAccountRepository
) {
    operator fun invoke(firstName: String, lastName: String) =
        customerAccountRepository.changeName(firstName, lastName)
}
