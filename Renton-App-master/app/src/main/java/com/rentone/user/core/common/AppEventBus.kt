package com.rentone.user.core.common

import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.SharedFlow
import javax.inject.Inject
import javax.inject.Singleton

sealed class AppEvent {
    data class NewChatMessage(
        val chatroomId: Int,
        val userType: Int,
        val accountId: Int,
        val attachmentType: Int,
        val attachment: String?,
        val message: String?,
        val dateAdded: String?
    ) : AppEvent()

    data class PartnerRentVehicleTransactionUpdated(val id: Int) : AppEvent()

    data class CustomerRentVehicleTransactionUpdated(val id: Int) : AppEvent()
}

/**
 * In-process replacement for LocalBroadcastManager: lets services (e.g. FCM) notify
 * currently active screens without a system broadcast.
 */
@Singleton
class AppEventBus @Inject constructor() {
    private val _events = MutableSharedFlow<AppEvent>(extraBufferCapacity = 8)
    val events: SharedFlow<AppEvent> = _events

    suspend fun emit(event: AppEvent) {
        _events.emit(event)
    }

    fun tryEmit(event: AppEvent) {
        _events.tryEmit(event)
    }
}
