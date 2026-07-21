package com.rentone.user.domain.model.command

data class UploadImageCommand(
    val imagePath: String,
    val description: String? = null
)
