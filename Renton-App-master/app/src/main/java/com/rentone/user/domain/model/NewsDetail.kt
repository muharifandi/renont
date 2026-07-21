package com.rentone.user.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class NewsDetail(
    val news: News,
    val voucher: Voucher?
)
