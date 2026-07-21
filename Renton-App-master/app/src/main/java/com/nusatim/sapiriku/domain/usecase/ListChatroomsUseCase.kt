package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.Chatroom
import com.nusatim.sapiriku.domain.repository.ChatRepository
import javax.inject.Inject

class ListChatroomsUseCase @Inject constructor(
    private val chatRepository: ChatRepository
) {
    suspend operator fun invoke(page: Int, pageSize: Int, isPartner: Boolean): Result<List<Chatroom>> {
        val params = mapOf(
            "page" to page.toString(),
            "limit" to pageSize.toString(),
            "is_partner" to if (isPartner) "1" else "0"
        )
        var result: Result<List<Chatroom>> = Result.success(emptyList())
        chatRepository.getChatrooms(params).collect { resource ->
            when (resource) {
                is Resource.Success -> result = Result.success(resource.data)
                is Resource.Error -> result = Result.failure(Exception(resource.message))
                else -> Unit
            }
        }
        return result
    }
}
