package com.rentone.user.data.mapper

import com.rentone.user.api.model.BasicResponse
import com.rentone.user.domain.model.OperationResult

fun BasicResponse.toDomain(): OperationResult = OperationResult(success = status, message = message)
