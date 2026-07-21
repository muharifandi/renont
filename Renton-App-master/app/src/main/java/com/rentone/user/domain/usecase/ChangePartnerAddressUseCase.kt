package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class ChangePartnerAddressUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(address: String) =
        partnerProfileRepository.changeAddress(address)
}
