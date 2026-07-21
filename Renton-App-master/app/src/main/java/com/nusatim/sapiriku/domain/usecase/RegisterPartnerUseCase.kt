package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.command.RegisterPartnerCommand
import com.nusatim.sapiriku.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class RegisterPartnerUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(command: RegisterPartnerCommand) =
        partnerProfileRepository.register(command)
}
