package com.rentone.user.domain.model

data class ValidationResult(
    val isValid: Boolean,
    val message: String? = null,
    val additionalInfo: String? = null
)
