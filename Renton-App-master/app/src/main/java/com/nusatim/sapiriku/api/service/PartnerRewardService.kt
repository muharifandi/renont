package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.ApiEnvelope
import com.nusatim.sapiriku.api.model.ClaimRewardRequest
import com.nusatim.sapiriku.api.model.PartnerRewardDetailData
import com.nusatim.sapiriku.api.model.RewardScopesData
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Query

/** Maps to RentonBachkEnd-main/application/modules/api/controllers/PartnerReward.php -- requires `key` header (except scopes). */
interface PartnerRewardService {

    @GET("partnerReward/scopes")
    suspend fun listScopes(): Response<ApiEnvelope<RewardScopesData>>

    /** [rewardScope] optional -- server defaults to the first available scope if omitted. */
    @GET("partnerReward/detail")
    suspend fun detail(@Query("reward_scope") rewardScope: Int? = null): Response<ApiEnvelope<PartnerRewardDetailData>>

    @POST("partnerReward/claims")
    suspend fun claimReward(@Body request: ClaimRewardRequest): Response<ApiEnvelope<Unit?>>
}
