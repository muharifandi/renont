package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
import java.io.Serializable as JavaSerializable


@Serializable
data class DateRange(
    val startDate: String? = null,
    val endDate: String? = null
) : JavaSerializable