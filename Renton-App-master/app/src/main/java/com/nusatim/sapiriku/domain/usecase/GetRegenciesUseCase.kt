package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.LookupRepository
import javax.inject.Inject

class GetRegenciesUseCase @Inject constructor(
    private val lookupRepository: LookupRepository
) {
    operator fun invoke(query: String) = lookupRepository.getRegencies(query)
}
