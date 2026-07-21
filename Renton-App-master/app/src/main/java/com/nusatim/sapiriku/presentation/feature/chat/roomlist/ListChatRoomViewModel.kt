package com.nusatim.sapiriku.presentation.feature.chat.roomlist
import com.nusatim.sapiriku.core.ui.PagedListViewModel
import com.nusatim.sapiriku.domain.model.Chatroom
import com.nusatim.sapiriku.domain.usecase.ListChatroomsUseCase
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
