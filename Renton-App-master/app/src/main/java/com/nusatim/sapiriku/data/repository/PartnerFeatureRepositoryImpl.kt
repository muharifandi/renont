package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.api.model.FeatureRequestRequest
import com.nusatim.sapiriku.api.service.PartnerService
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.repository.PartnerFeatureRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PartnerFeatureRepositoryImpl @Inject constructor(
    private val partnerService: PartnerService
) : BaseRepository(), PartnerFeatureRepository {

    override fun requestFeature(featureId: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerService.requestFeature(FeatureRequestRequest(featureId)) },
            map = { it.toOperationResult() }
        )
    }
}
