package com.rentone.user.api.model

import com.rentone.user.domain.model.Regencies
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class GetRegenciesResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("regencies") val regencies: List<Regencies> = emptyList()
) {
    fun getArrayDataName(): Array<String> = regencies.map { it.name }.toTypedArray()
}
