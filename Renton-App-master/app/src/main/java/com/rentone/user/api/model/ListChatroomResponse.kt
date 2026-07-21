package com.rentone.user.api.model

import com.rentone.user.domain.model.Chatroom
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ListChatroomResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("list") val chatrooms: List<Chatroom> = emptyList()
)
