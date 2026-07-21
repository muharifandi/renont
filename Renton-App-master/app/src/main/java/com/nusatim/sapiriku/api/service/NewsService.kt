package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.ApiEnvelope
import com.nusatim.sapiriku.api.model.NewsDetailData
import com.nusatim.sapiriku.api.model.NewsListData
import retrofit2.Response
import retrofit2.http.GET
import retrofit2.http.Path
import retrofit2.http.Query

/** Maps to RentonBachkEnd-main/application/modules/api/controllers/News.php */
interface NewsService {

    /** Requires `key` header (personalized to whether the account is a valid partner). */
    @GET("news")
    suspend fun list(
        @Query("page") page: Int = 1,
        @Query("limit") limit: Int = 10
    ): Response<ApiEnvelope<NewsListData>>

    /** Public, no `key` header required. */
    @GET("news/{id}")
    suspend fun detail(@Path("id") id: Int): Response<ApiEnvelope<NewsDetailData>>

    // NOTE: the old `listPreview()` (POST news/list_preview -> News_m::list_preview()) has
    // no route in the new News.php controller -- the model method still exists but nothing
    // calls it. If the app still needs a lightweight preview list (e.g. home screen
    // carousel), that endpoint needs to be added back to News.php; flagging rather than
    // guessing at a replacement.
}
