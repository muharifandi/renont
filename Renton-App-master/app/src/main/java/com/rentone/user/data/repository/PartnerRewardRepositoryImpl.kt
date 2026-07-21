package com.rentone.user.data.repository

import com.rentone.user.api.service.PartnerRewardService
import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.BasicData
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.PartnerReward
import com.rentone.user.domain.repository.PartnerRewardRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PartnerRewardRepositoryImpl @Inject constructor(
    private val partnerRewardService: PartnerRewardService
) : BaseRepository(), PartnerRewardRepository {

    override fun listRewardScopes(): Flow<Resource<List<BasicData>>> {
        return safeApiCall(
            apiCall = { partnerRewardService.listScope() },
            map = { it.data }
        )
    }

    override fun getRewardDetail(rewardScope: Int): Flow<Resource<List<PartnerReward>>> {
        return safeApiCall(
            apiCall = { partnerRewardService.detail(rewardScope) },
            map = { it.partnerRewards }
        )
    }

    override fun claimReward(rewardId: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRewardService.claimReward(rewardId) },
            map = { OperationResult(it.status, it.message ?: "") }
        )
    }
}
