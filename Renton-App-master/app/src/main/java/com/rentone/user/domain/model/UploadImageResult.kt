package com.rentone.user.domain.model

data class UploadImageResult(
    val status: Boolean,
    val message: String? = null,
    val fileName: String? = null
)
