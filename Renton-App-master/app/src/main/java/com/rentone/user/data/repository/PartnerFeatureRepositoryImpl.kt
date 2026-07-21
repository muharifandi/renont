package com.rentone.user.data.repository

import com.rentone.user.api.service.PartnerService
import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.repository.PartnerFeatureRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PartnerFeatureRepositoryImpl @Inject constructor(
    private val partnerService: PartnerService
) : BaseRepository(), PartnerFeatureRepository {

    override fun requestFeature(featureId: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerService.requestFeature(mapOf("feature_id" to featureId.toString())) },
            map = { OperationResult(it.status, it.message ?: "") }
        )
    }
}
