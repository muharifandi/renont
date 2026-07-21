package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.NewsRepository
import javax.inject.Inject

class GetNewsDetailUseCase @Inject constructor(
    private val newsRepository: NewsRepository
) {
    operator fun invoke(id: Int) = newsRepository.getNewsDetail(id)
}
