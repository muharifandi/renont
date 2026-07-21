package com.nusatim.sapiriku.domain.model.command

data class RegisterPartnerCommand(
    val companyName: String,
    val description: String,
    val address: String,
    val regencyId: Int,
    val taxNumber: String?,
    val profileImagePath: String?,
    val identityImagePath: String?
)
