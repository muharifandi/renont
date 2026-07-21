package com.rentone.user.api.model

import com.rentone.user.domain.model.Chat
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ChatResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("chat") val chat: Chat? = null
)

@Serializable
data class ListChatResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("chatroom_id") val chatroomId: Int = 0,
    @SerialName("chats") val chats: List<Chat> = emptyList()
)
