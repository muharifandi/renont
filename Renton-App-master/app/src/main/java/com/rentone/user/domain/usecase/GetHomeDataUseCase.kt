package com.rentone.user.domain.usecase

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.HomeData
import com.rentone.user.domain.repository.CustomerAccountRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject

class GetHomeDataUseCase @Inject constructor(
    private val customerAccountRepository: CustomerAccountRepository
) {
    operator fun invoke(): Flow<Resource<HomeData>> =
        customerAccountRepository.getHomeData()
}
