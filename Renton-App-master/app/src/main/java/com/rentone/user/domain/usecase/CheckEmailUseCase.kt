package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.LookupRepository
import javax.inject.Inject

class CheckEmailUseCase @Inject constructor(
    private val lookupRepository: LookupRepository
) {
    operator fun invoke(email: String) = lookupRepository.checkEmail(email)
}
