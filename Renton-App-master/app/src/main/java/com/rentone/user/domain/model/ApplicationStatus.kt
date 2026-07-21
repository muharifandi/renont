package com.rentone.user.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class ApplicationStatus(
    val maintenance: Boolean,
    val maintenanceMessage: String?,
    val androidAppVersionCode: Int,
    val androidAppVersionName: String?,
    val androidAppUpdateLink: String?
)
