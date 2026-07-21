package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ChatroomListItem(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("img_profile") val imgProfile: String? = null,
    @SerialName("message") val message: String? = null,
    @SerialName("date_added") val dateAdded: String? = null,
    @SerialName("unread") val unread: Int = 0
)

@Serializable
data class ChatroomListData(
    @SerialName("list") val list: List<ChatroomListItem> = emptyList()
)

@Serializable
data class ChatMessage(
    @SerialName("id") val id: Long,
    @SerialName("chatroom_id") val chatroomId: Int,
    @SerialName("user_type") val userType: Int,
    @SerialName("account_id") val accountId: Int,
    @SerialName("attachment_type") val attachmentType: Int = 0,
    @SerialName("attachment") val attachment: String? = null,
    @SerialName("message") val message: String? = null,
    @SerialName("unread") val unread: Int = 1,
    @SerialName("date_added") val dateAdded: String? = null
)

@Serializable
data class ChatMessagesData(
    @SerialName("chats") val chats: List<ChatMessage> = emptyList(),
    @SerialName("chatroom_id") val chatroomId: Int
)

@Serializable
data class SendChatMessageData(
    @SerialName("chat") val chat: ChatMessage
)

/** POST api/chat/messages body -- chatroomId is null the first time (server creates the room). */
@Serializable
data class SendChatMessageRequest(
    @SerialName("chatroom_id") val chatroomId: Int? = null,
    @SerialName("account_id") val accountId: Int,
    @SerialName("user_type") val userType: Int,
    @SerialName("message") val message: String,
    @SerialName("attachment_type") val attachmentType: Int? = null,
    @SerialName("attachment") val attachment: String? = null
)

@Serializable
data class ReadMessagesRequest(
    @SerialName("chatroom_id") val chatroomId: Int
)
