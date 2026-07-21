package com.rentone.user.api.model

import com.rentone.user.domain.model.PromoteVehicle
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PartnerListPromoteVehicleResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("promotes") val promoteVehicles: List<PromoteVehicle> = emptyList()
)
