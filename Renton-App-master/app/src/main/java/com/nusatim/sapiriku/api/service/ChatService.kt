package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.BasicResponse
import com.nusatim.sapiriku.api.model.ChatResponse
import com.nusatim.sapiriku.api.model.ListChatResponse
import com.nusatim.sapiriku.api.model.ListChatroomResponse
import retrofit2.Response
import retrofit2.http.Field
import retrofit2.http.FieldMap
import retrofit2.http.FormUrlEncoded
import retrofit2.http.POST

interface ChatService {

    @FormUrlEncoded
    @POST("chat/list_chat")
    suspend fun listChat(@FieldMap form: Map<String, String>): Response<ListChatResponse>

    @FormUrlEncoded
    @POST("chat/send_message")
    suspend fun sendMessage(@FieldMap form: Map<String, String>): Response<ChatResponse>

    @FormUrlEncoded
    @POST("chat/list_chatroom")
    suspend fun listChatroom(@FieldMap form: Map<String, String>): Response<ListChatroomResponse>

    @FormUrlEncoded
    @POST("chat/read_message")
    suspend fun setMessageRead(@Field("chatroom_id") chatroomId: Int): Response<BasicResponse>
}
