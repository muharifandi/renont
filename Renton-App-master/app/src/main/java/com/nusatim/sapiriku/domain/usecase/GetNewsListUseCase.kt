package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.News
import com.nusatim.sapiriku.domain.repository.NewsRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject

class GetNewsListUseCase @Inject constructor(
    private val newsRepository: NewsRepository
) {
    operator fun invoke(params: Map<String, String>): Flow<Resource<List<News>>> {
        return newsRepository.getNewsList(params)
    }
}
