package com.rentone.user.domain.repository

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.OperationResult
import kotlinx.coroutines.flow.Flow

interface PartnerFeatureRepository {
    fun requestFeature(featureId: Int): Flow<Resource<OperationResult>>
}
