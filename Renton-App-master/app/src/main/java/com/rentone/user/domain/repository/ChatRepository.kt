package com.rentone.user.domain.repository

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.Chat
import com.rentone.user.domain.model.Chatroom
import kotlinx.coroutines.flow.Flow

interface ChatRepository {
    fun getChatrooms(params: Map<String, String>): Flow<Resource<List<Chatroom>>>
    fun getChats(params: Map<String, String>): Flow<Resource<List<Chat>>>
    suspend fun sendMessage(params: Map<String, String>): Resource<Unit>
}
