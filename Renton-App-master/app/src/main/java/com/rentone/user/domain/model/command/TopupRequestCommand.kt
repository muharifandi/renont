package com.rentone.user.domain.model.command

data class TopupRequestCommand(
    val companyBankId: Int,
    val amount: String
)
