package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.News
import com.nusatim.sapiriku.domain.model.NewsDetail
import kotlinx.coroutines.flow.Flow

interface NewsRepository {
    fun getNewsList(params: Map<String, String>): Flow<Resource<List<News>>>
    fun getNewsDetail(id: Int): Flow<Resource<NewsDetail>>
}
