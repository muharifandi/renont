package com.rentone.user.api.model

import com.rentone.user.domain.model.PartnerFeature
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class CustomerStatusResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("partner_chat_unread") val partnerChatUnread: Int = 0,
    @SerialName("customer_chat_unread") val customerChatUnread: Int = 0,
    @SerialName("notification") val notification: Int = 0,
    @SerialName("customer_status") val customerStatus: Int = 0,
    @SerialName("partner_status") val partnerStatus: Int = 0,
    @SerialName("partner_features") val partnerFeatures: List<PartnerFeature> = emptyList()
)
