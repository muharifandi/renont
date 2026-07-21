package com.nusatim.sapiriku.domain.model.command

data class RegisterCustomerCommand(
    val firstName: String,
    val lastName: String,
    val email: String,
    val phone: String,
    val identityNumber: String,
    val password: String,
    val profileImagePath: String?,
    val identityImagePath: String?
)
