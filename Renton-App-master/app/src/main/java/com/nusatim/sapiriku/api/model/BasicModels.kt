package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ApplicationStatusData(
    @SerialName("maintenance") val maintenance: Int = 0,
    @SerialName("maintenance_message") val maintenanceMessage: String? = null,
    @SerialName("android_app_version_code") val androidAppVersionCode: Int = 0,
    @SerialName("android_app_version_name") val androidAppVersionName: String? = null,
    @SerialName("android_app_update_link") val androidAppUpdateLink: String? = null
)

@Serializable
data class AvailabilityData(
    @SerialName("available") val available: Boolean
)

@Serializable
data class CheckAgentData(
    @SerialName("valid") val valid: Boolean,
    @SerialName("name") val name: String
)

@Serializable
data class RegencyItem(
    @SerialName("id") val id: String,
    @SerialName("province_id") val provinceId: String? = null,
    @SerialName("name") val name: String
)

@Serializable
data class RegenciesData(
    @SerialName("regencies") val regencies: List<RegencyItem> = emptyList()
)
