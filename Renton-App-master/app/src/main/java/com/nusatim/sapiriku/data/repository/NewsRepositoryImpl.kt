package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.api.service.NewsService
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.domain.model.News
import com.nusatim.sapiriku.domain.model.NewsDetail
import com.nusatim.sapiriku.domain.repository.NewsRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class NewsRepositoryImpl @Inject constructor(
    private val newsService: NewsService
) : BaseRepository(), NewsRepository {

    override fun getNewsList(params: Map<String, String>): Flow<Resource<List<News>>> {
        return safeApiCall(
            apiCall = { newsService.list(params) },
            map = { it.news }
        )
    }

    override fun getNewsDetail(id: Int): Flow<Resource<NewsDetail>> {
        return safeApiCall(
            apiCall = { newsService.detail(id) },
            map = { it.toNewsDetail() }
        )
    }
}
