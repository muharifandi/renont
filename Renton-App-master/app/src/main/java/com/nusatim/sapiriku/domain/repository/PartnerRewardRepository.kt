package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.BasicData
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.PartnerReward
import kotlinx.coroutines.flow.Flow

interface PartnerRewardRepository {
    fun listRewardScopes(): Flow<Resource<List<BasicData>>>
    fun getRewardDetail(rewardScope: Int): Flow<Resource<List<PartnerReward>>>
    fun claimReward(rewardId: Int): Flow<Resource<OperationResult>>
}
