package com.rentone.user.domain.model

import java.io.Serializable as JavaSerializable


data class DateRange(
    val startDate: String? = null,
    val endDate: String? = null
) : JavaSerializable