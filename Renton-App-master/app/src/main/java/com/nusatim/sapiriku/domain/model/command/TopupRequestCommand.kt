package com.nusatim.sapiriku.domain.model.command

data class TopupRequestCommand(
    val companyBankId: Int,
    val amount: String
)
