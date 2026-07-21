package com.rentone.user.domain.model.command

data class AddBankCommand(
    val id: Int? = null,
    val bankId: Int,
    val accountName: String,
    val accountNumber: String
)
