package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.LookupRepository
import javax.inject.Inject

class CheckEmailUseCase @Inject constructor(
    private val lookupRepository: LookupRepository
) {
    operator fun invoke(email: String) = lookupRepository.checkEmail(email)
}
