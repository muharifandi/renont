package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.Chat
import com.nusatim.sapiriku.domain.model.Chatroom
import kotlinx.coroutines.flow.Flow

interface ChatRepository {
    fun getChatrooms(params: Map<String, String>): Flow<Resource<List<Chatroom>>>
    fun getChats(params: Map<String, String>): Flow<Resource<List<Chat>>>
    suspend fun sendMessage(params: Map<String, String>): Resource<Unit>
}
