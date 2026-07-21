package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class NewsDetail(
    val news: News,
    val voucher: Voucher?
)
