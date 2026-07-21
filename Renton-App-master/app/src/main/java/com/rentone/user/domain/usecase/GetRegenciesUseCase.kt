package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.LookupRepository
import javax.inject.Inject

class GetRegenciesUseCase @Inject constructor(
    private val lookupRepository: LookupRepository
) {
    operator fun invoke(query: String) = lookupRepository.getRegencies(query)
}
