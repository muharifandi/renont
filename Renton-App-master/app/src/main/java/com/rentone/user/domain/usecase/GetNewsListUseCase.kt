package com.rentone.user.domain.usecase

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.News
import com.rentone.user.domain.repository.NewsRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject

class GetNewsListUseCase @Inject constructor(
    private val newsRepository: NewsRepository
) {
    operator fun invoke(params: Map<String, String>): Flow<Resource<List<News>>> {
        return newsRepository.getNewsList(params)
    }
}
