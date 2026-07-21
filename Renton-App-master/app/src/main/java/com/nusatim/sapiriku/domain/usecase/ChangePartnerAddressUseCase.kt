package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class ChangePartnerAddressUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(address: String) =
        partnerProfileRepository.changeAddress(address)
}
