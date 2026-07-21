package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class NewsItem(
    @SerialName("id") val id: Int,
    @SerialName("user_type") val userType: Int? = null,
    @SerialName("title") val title: String? = null,
    @SerialName("img") val img: String? = null,
    @SerialName("content") val content: String? = null,
    @SerialName("is_voucher") val isVoucher: Int = 0,
    @SerialName("voucher_id") val voucherId: Int? = null,
    @SerialName("status") val status: Int = 0,
    @SerialName("date_added") val dateAdded: String? = null
)

@Serializable
data class NewsListData(
    @SerialName("news") val news: List<NewsItem> = emptyList()
)

@Serializable
data class NewsDetailData(
    @SerialName("detail") val detail: NewsItem,
    @SerialName("voucher") val voucher: VoucherItem? = null
)
