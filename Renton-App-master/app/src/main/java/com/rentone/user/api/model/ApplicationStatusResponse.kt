package com.rentone.user.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ApplicationStatusResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("maintenance") val maintenance: Int = 0,
    @SerialName("maintenance_message") val maintenanceMessage: String? = null,
    @SerialName("android_app_version_code") val androidAppVersionCode: Int = 0,
    @SerialName("android_app_version_name") val androidAppVersionName: String? = null,
    @SerialName("android_app_update_link") val androidAppUpdateLink: String? = null
)
