package com.rentone.user.data.mapper

import com.rentone.user.api.model.ApplicationStatusResponse
import com.rentone.user.domain.model.ApplicationStatus

fun ApplicationStatusResponse.toDomain(): ApplicationStatus = ApplicationStatus(
    maintenance = maintenance == 1,
    maintenanceMessage = maintenanceMessage,
    androidAppVersionCode = androidAppVersionCode,
    androidAppVersionName = androidAppVersionName,
    androidAppUpdateLink = androidAppUpdateLink
)
