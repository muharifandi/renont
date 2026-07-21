package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.LookupRepository
import javax.inject.Inject

class CheckPhoneUseCase @Inject constructor(
    private val lookupRepository: LookupRepository
) {
    operator fun invoke(phone: String) = lookupRepository.checkPhone(phone)
}
