package com.rentone.user.domain.model.command

data class WithdrawRequestCommand(
    val accountBankId: Int,
    val amount: String
)
