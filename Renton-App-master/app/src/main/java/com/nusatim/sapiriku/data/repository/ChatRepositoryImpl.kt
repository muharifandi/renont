package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.api.model.SendChatMessageRequest
import com.nusatim.sapiriku.api.service.ChatService
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.Chat
import com.nusatim.sapiriku.domain.model.Chatroom
import com.nusatim.sapiriku.domain.repository.ChatRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class ChatRepositoryImpl @Inject constructor(
    private val chatService: ChatService
) : BaseRepository(), ChatRepository {

    override fun getChatrooms(params: Map<String, String>): Flow<Resource<List<Chatroom>>> {
        return safeApiCall(
            apiCall = { chatService.getChatrooms(params["is_partner"]?.toInt() ?: 0) },
            map = { response -> response.data?.list?.map { it.toChatroom() } ?: emptyList() }
        )
    }

    override fun getChats(params: Map<String, String>): Flow<Resource<List<Chat>>> {
        return safeApiCall(
            apiCall = {
                chatService.getMessages(
                    chatroomId = params["chatroom_id"]?.toInt(),
                    partnerAccountId = params["partner_account_id"]?.toInt(),
                    customerAccountId = params["customer_account_id"]?.toInt(),
                    page = params["page"]?.toInt() ?: 1,
                    limit = params["limit"]?.toInt() ?: 20
                )
            },
            map = { response -> response.data?.chats?.map { it.toChat() } ?: emptyList() }
        )
    }

    override suspend fun sendMessage(params: Map<String, String>): Resource<Unit> {
        return try {
            val request = SendChatMessageRequest(
                chatroomId = params["chatroom_id"]?.toInt(),
                accountId = params["account_id"]?.toInt() ?: 0,
                userType = params["user_type"]?.toInt() ?: 0,
                message = params["message"].orEmpty(),
                attachmentType = params["attachment_type"]?.toInt(),
                attachment = params["attachment"]
            )
            val response = chatService.sendMessage(request)
            if (response.isSuccessful && response.body()?.status == true) {
                Resource.Success(Unit)
            } else {
                Resource.Error(response.body()?.message ?: "Failed to send message")
            }
        } catch (e: Exception) {
            Resource.Error(e.localizedMessage ?: "Unknown error")
        }
    }
}
