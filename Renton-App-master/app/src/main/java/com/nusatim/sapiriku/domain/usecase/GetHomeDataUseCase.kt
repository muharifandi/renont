package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.HomeData
import com.nusatim.sapiriku.domain.repository.CustomerAccountRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject

class GetHomeDataUseCase @Inject constructor(
    private val customerAccountRepository: CustomerAccountRepository
) {
    operator fun invoke(): Flow<Resource<HomeData>> =
        customerAccountRepository.getHomeData()
}
