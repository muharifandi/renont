package com.rentone.user.domain.repository

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.BasicData
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.PartnerReward
import kotlinx.coroutines.flow.Flow

interface PartnerRewardRepository {
    fun listRewardScopes(): Flow<Resource<List<BasicData>>>
    fun getRewardDetail(rewardScope: Int): Flow<Resource<List<PartnerReward>>>
    fun claimReward(rewardId: Int): Flow<Resource<OperationResult>>
}
