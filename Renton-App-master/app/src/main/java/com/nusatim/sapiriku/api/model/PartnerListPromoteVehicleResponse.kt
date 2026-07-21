package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.PromoteVehicle
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PartnerListPromoteVehicleResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("promotes") val promoteVehicles: List<PromoteVehicle> = emptyList()
)
