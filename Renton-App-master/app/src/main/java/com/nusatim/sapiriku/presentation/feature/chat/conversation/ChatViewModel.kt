package com.nusatim.sapiriku.presentation.feature.chat.conversation
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.Chat
import com.nusatim.sapiriku.domain.repository.ChatRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class ChatViewModel @Inject constructor(
    private val chatRepository: ChatRepository
) : ViewModel() {

    private val _chats = MutableStateFlow<UiState<List<Chat>>>(UiState.Idle)
    val chats = _chats.asStateFlow()

    private val _sendMessageStatus = MutableStateFlow<UiState<Unit>>(UiState.Idle)
    val sendMessageStatus = _sendMessageStatus.asStateFlow()

    private var currentPage = 1
    private var isLastPage = false

    fun fetchChats(chatroomId: Int, partnerAccountId: Int, customerAccountId: Int, isRefresh: Boolean = false) {
        if (isRefresh) {
            currentPage = 1
            isLastPage = false
        }
        
        if (isLastPage) return

        viewModelScope.launch {
            val params = mutableMapOf(
                "page" to currentPage.toString(),
                "limit" to "10"
            )
            if (chatroomId != 0) params["chatroom_id"] = chatroomId.toString()
            if (partnerAccountId != 0) params["partner_account_id"] = partnerAccountId.toString()
            if (customerAccountId != 0) params["customer_account_id"] = customerAccountId.toString()

            chatRepository.getChats(params).collect { resource ->
                when (resource) {
                    is Resource.Loading -> {
                        if (currentPage == 1) _chats.value = UiState.Loading
                    }
                    is Resource.Success -> {
                        val currentData = if (currentPage == 1) emptyList() else {
                            (_chats.value as? UiState.Success)?.data ?: emptyList()
                        }
                        val newData = resource.data
                        if (newData.isEmpty()) {
                            isLastPage = true
                        } else {
                            _chats.value = UiState.Success(currentData + newData)
                            currentPage++
                        }
                    }
                    is Resource.Error -> {
                        _chats.value = UiState.Error(resource.message)
                    }
                    else -> Unit
                }
            }
        }
    }

    fun sendMessage(chatroomId: Int, accountId: Int, userType: Int, message: String, attachmentType: Int, attachment: String?) {
        viewModelScope.launch {
            _sendMessageStatus.value = UiState.Loading
            val params = mutableMapOf(
                "account_id" to accountId.toString(),
                "user_type" to userType.toString(),
                "message" to message,
                "attachment_type" to attachmentType.toString()
            )
            if (chatroomId != 0) params["chatroom_id"] = chatroomId.toString()
            attachment?.let { params["attachment"] = it }

            val result = chatRepository.sendMessage(params)
            when (result) {
                is Resource.Success -> _sendMessageStatus.value = UiState.Success(Unit)
                is Resource.Error -> _sendMessageStatus.value = UiState.Error(result.message)
                else -> Unit
            }
        }
    }
}
