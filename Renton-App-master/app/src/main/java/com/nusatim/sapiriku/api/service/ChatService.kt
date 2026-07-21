package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.ApiEnvelope
import com.nusatim.sapiriku.api.model.ChatMessagesData
import com.nusatim.sapiriku.api.model.ChatroomListData
import com.nusatim.sapiriku.api.model.ReadMessagesRequest
import com.nusatim.sapiriku.api.model.SendChatMessageData
import com.nusatim.sapiriku.api.model.SendChatMessageRequest
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Query

/** Maps to RentonBachkEnd-main/application/modules/api/controllers/Chat.php -- requires `key` header on all methods. */
interface ChatService {

    /** @param isPartner 0 = viewing as customer, 1 = viewing as partner */
    @GET("chat/chatrooms")
    suspend fun getChatrooms(@Query("is_partner") isPartner: Int): Response<ApiEnvelope<ChatroomListData>>

    /**
     * Either pass [chatroomId] (existing room), or both [partnerAccountId] and
     * [customerAccountId] (server creates/finds the room for you).
     */
    @GET("chat/messages")
    suspend fun getMessages(
        @Query("chatroom_id") chatroomId: Int? = null,
        @Query("partner_account_id") partnerAccountId: Int? = null,
        @Query("customer_account_id") customerAccountId: Int? = null,
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 20
    ): Response<ApiEnvelope<ChatMessagesData>>

    @POST("chat/messages")
    suspend fun sendMessage(@Body request: SendChatMessageRequest): Response<ApiEnvelope<SendChatMessageData>>

    @PUT("chat/messages_read")
    suspend fun markMessagesRead(@Body request: ReadMessagesRequest): Response<ApiEnvelope<Unit?>>
}
