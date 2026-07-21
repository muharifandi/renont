package com.rentone.user.api.service

import com.rentone.user.api.model.BasicListResponse
import com.rentone.user.api.model.BasicResponse
import com.rentone.user.api.model.PartnerRewardDetailResponse
import retrofit2.Response
import retrofit2.http.Field
import retrofit2.http.FormUrlEncoded
import retrofit2.http.POST

interface PartnerRewardService {

    @FormUrlEncoded
    @POST("partnerReward/detail")
    suspend fun detail(@Field("reward_scope") rewardScope: Int): Response<PartnerRewardDetailResponse>

    @POST("partnerReward/list_scope")
    suspend fun listScope(): Response<BasicListResponse>

    @FormUrlEncoded
    @POST("partnerReward/claim_item_reward")
    suspend fun claimReward(@Field("reward_id") rewardId: Int): Response<BasicResponse>
}
