package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.LookupRepository
import javax.inject.Inject

class CheckPhoneUseCase @Inject constructor(
    private val lookupRepository: LookupRepository
) {
    operator fun invoke(phone: String) = lookupRepository.checkPhone(phone)
}
