package com.rentone.user.domain.model



data class Chat(
    val id: Int,
    val chatroomId: Int,
    val userType: Int,
    val accountId: Int,
    val attachmentType: Int = 0,
    val attachment: String? = null,
    val message: String? = null,
    val dateAdded: String? = null
)


data class Chatroom(
    val id: Int,
    val name: String? = null,
    val imgProfile: String? = null,
    val unread: Int = 0,
    val message: String? = null,
    val dateAdded: String? = null
)
