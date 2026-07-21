package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerAccountRepository
import javax.inject.Inject

class ChangeCustomerNameUseCase @Inject constructor(
    private val customerAccountRepository: CustomerAccountRepository
) {
    operator fun invoke(firstName: String, lastName: String) =
        customerAccountRepository.changeName(firstName, lastName)
}
