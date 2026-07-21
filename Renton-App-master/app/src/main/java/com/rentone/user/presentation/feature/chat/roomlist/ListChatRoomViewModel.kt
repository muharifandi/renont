package com.rentone.user.presentation.feature.chat.roomlist
import com.rentone.user.core.ui.PagedListViewModel
import com.rentone.user.domain.model.Chatroom
import com.rentone.user.domain.usecase.ListChatroomsUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import javax.inject.Inject

@HiltViewModel
class ListChatRoomViewModel @Inject constructor(
    private val listChatroomsUseCase: ListChatroomsUseCase
) : PagedListViewModel<Chatroom>() {

    var isPartner: Boolean = false

    override suspend fun fetchPage(page: Int, pageSize: Int): Result<List<Chatroom>> =
        listChatroomsUseCase(page, pageSize, isPartner)
}
