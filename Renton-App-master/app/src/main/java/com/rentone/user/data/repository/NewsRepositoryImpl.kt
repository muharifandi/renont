package com.rentone.user.data.repository

import com.rentone.user.api.service.NewsService
import com.rentone.user.core.common.Resource
import com.rentone.user.data.mapper.toDomain
import com.rentone.user.domain.model.News
import com.rentone.user.domain.model.NewsDetail
import com.rentone.user.domain.repository.NewsRepository
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
            map = { it.toDomain() }
        )
    }
}
