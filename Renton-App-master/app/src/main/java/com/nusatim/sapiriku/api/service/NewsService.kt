package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.ListNewsPreviewResponse
import com.nusatim.sapiriku.api.model.ListNewsResponse
import com.nusatim.sapiriku.api.model.NewsDetailResponse
import retrofit2.Response
import retrofit2.http.Field
import retrofit2.http.FieldMap
import retrofit2.http.FormUrlEncoded
import retrofit2.http.POST

interface NewsService {

    @FormUrlEncoded
    @POST("news/list")
    suspend fun list(@FieldMap form: Map<String, String>): Response<ListNewsResponse>

    @FormUrlEncoded
    @POST("news/detail")
    suspend fun detail(@Field("id") id: Int): Response<NewsDetailResponse>

    @FormUrlEncoded
    @POST("news/list_preview")
    suspend fun listPreview(@Field("id") id: Int): Response<ListNewsPreviewResponse>
}
