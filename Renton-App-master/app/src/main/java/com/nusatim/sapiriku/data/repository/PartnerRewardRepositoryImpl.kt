package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.api.model.ClaimRewardRequest
import com.nusatim.sapiriku.api.service.PartnerRewardService
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.BasicData
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.PartnerReward
import com.nusatim.sapiriku.domain.repository.PartnerRewardRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PartnerRewardRepositoryImpl @Inject constructor(
    private val partnerRewardService: PartnerRewardService
) : BaseRepository(), PartnerRewardRepository {

    override fun listRewardScopes(): Flow<Resource<List<BasicData>>> {
        return safeApiCall(
            apiCall = { partnerRewardService.listScopes() },
            map = { response -> response.data?.data?.map { it.toBasicData() } ?: emptyList() }
        )
    }

    override fun getRewardDetail(rewardScope: Int): Flow<Resource<List<PartnerReward>>> {
        return safeApiCall(
            apiCall = { partnerRewardService.detail(rewardScope) },
            map = { response -> response.data?.data?.map { it.toPartnerReward() } ?: emptyList() }
        )
    }

    override fun claimReward(rewardId: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerRewardService.claimReward(ClaimRewardRequest(rewardId)) },
            map = { it.toOperationResult() }
        )
    }
}
