package com.nusatim.sapiriku.domain.model.command

data class WithdrawRequestCommand(
    val accountBankId: Int,
    val amount: String
)
