package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.NewsRepository
import javax.inject.Inject

class GetNewsDetailUseCase @Inject constructor(
    private val newsRepository: NewsRepository
) {
    operator fun invoke(id: Int) = newsRepository.getNewsDetail(id)
}
