package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.command.RegisterPartnerCommand
import com.rentone.user.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class RegisterPartnerUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(command: RegisterPartnerCommand) =
        partnerProfileRepository.register(command)
}
