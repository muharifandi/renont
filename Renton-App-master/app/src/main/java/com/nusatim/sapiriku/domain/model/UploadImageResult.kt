package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class UploadImageResult(
    val status: Boolean,
    val message: String? = null,
    val fileName: String? = null
)
