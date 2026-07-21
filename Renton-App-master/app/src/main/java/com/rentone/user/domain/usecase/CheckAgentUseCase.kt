package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.LookupRepository
import javax.inject.Inject

class CheckAgentUseCase @Inject constructor(
    private val lookupRepository: LookupRepository
) {
    operator fun invoke(agentId: String) = lookupRepository.checkAgent(agentId)
}
