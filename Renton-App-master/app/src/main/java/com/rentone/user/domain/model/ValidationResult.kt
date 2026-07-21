package com.rentone.user.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class ValidationResult(
    val isValid: Boolean,
    val message: String? = null,
    val additionalInfo: String? = null
)
