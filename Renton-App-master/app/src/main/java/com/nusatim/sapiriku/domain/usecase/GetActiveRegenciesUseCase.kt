package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.LookupRepository
import javax.inject.Inject

class GetActiveRegenciesUseCase @Inject constructor(
    private val lookupRepository: LookupRepository
) {
    operator fun invoke() = lookupRepository.getActiveRegencies()
}
