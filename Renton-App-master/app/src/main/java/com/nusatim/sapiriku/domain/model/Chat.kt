package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class Chat(
    val id: Long,
    val chatroomId: Int,
    val userType: Int,
    val accountId: Int,
    val attachmentType: Int = 0,
    val attachment: String? = null,
    val message: String? = null,
    val dateAdded: String? = null
)


@Serializable
data class Chatroom(
    val id: Int,
    val name: String? = null,
    val imgProfile: String? = null,
    val unread: Int = 0,
    val message: String? = null,
    val dateAdded: String? = null
)
