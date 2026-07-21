package com.rentone.user.domain.repository

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.News
import com.rentone.user.domain.model.NewsDetail
import kotlinx.coroutines.flow.Flow

interface NewsRepository {
    fun getNewsList(params: Map<String, String>): Flow<Resource<List<News>>>
    fun getNewsDetail(id: Int): Flow<Resource<NewsDetail>>
}
