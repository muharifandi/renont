package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.api.service.ChatService
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
            apiCall = { chatService.listChatroom(params) },
            map = { it.chatrooms }
        )
    }

    override fun getChats(params: Map<String, String>): Flow<Resource<List<Chat>>> {
        return safeApiCall(
            apiCall = { chatService.listChat(params) },
            map = { it.chats }
        )
    }

    override suspend fun sendMessage(params: Map<String, String>): Resource<Unit> {
        return try {
            val response = chatService.sendMessage(params)
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
