package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.OperationResult
import kotlinx.coroutines.flow.Flow

interface PartnerFeatureRepository {
    fun requestFeature(featureId: Int): Flow<Resource<OperationResult>>
}
