package com.rentone.user.data.mapper

import com.rentone.user.api.model.CheckAgentResponse
import com.rentone.user.api.model.CheckEmailResponse
import com.rentone.user.api.model.CheckPhoneResponse
import com.rentone.user.domain.model.ValidationResult

fun CheckEmailResponse.toDomain(): ValidationResult {
    return ValidationResult(
        isValid = status,
        message = message,
        additionalInfo = additionalInfo
    )
}

fun CheckPhoneResponse.toDomain(): ValidationResult {
    return ValidationResult(
        isValid = status,
        message = message,
        additionalInfo = additionalInfo
    )
}

fun CheckAgentResponse.toDomain(): ValidationResult {
    return ValidationResult(
        isValid = status && valid,
        message = message,
        additionalInfo = additionalInfo
    )
}
