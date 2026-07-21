package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.Chatroom
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ListChatroomResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("list") val chatrooms: List<Chatroom> = emptyList()
)
