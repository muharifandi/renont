package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.BasicData
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class BasicListResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("data") val data: List<BasicData> = emptyList()
) {
    fun getArrayDataName(): Array<String> {
        return data.map { it.name }.toTypedArray()
    }
}
