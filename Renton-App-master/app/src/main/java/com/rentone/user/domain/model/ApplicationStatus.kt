package com.rentone.user.domain.model

data class ApplicationStatus(
    val maintenance: Boolean,
    val maintenanceMessage: String?,
    val androidAppVersionCode: Int,
    val androidAppVersionName: String?,
    val androidAppUpdateLink: String?
)
