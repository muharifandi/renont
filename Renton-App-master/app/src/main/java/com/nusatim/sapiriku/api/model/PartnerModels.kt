package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PartnerDetail(
    @SerialName("id") val id: Int,
    @SerialName("account_id") val accountId: Int,
    @SerialName("ownership_id") val ownershipId: Int? = null,
    @SerialName("company_name") val companyName: String? = null,
    @SerialName("tax_number") val taxNumber: String? = null,
    @SerialName("img_profile") val imgProfile: String? = null,
    @SerialName("regencies_id") val regenciesId: Int? = null,
    @SerialName("address") val address: String? = null,
    @SerialName("latitude") val latitude: Double? = null,
    @SerialName("longitude") val longitude: Double? = null,
    @SerialName("description") val description: String? = null,
    @SerialName("referal_id") val referalId: Int? = null,
    @SerialName("agent_id") val agentId: Int? = null,
    @SerialName("status") val status: Int = 0,
    @SerialName("regencies_name") val regenciesName: String? = null,
    @SerialName("ownership_name") val ownershipName: String? = null
)

@Serializable
data class PartnerFeaturePair(
    @SerialName("status") val status: Int? = null,
    @SerialName("feature_id") val featureId: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("status_name") val statusName: String? = null,
    @SerialName("icon") val icon: String? = null
)

@Serializable
data class PartnerDetailData(
    @SerialName("partner") val partner: PartnerDetail? = null,
    @SerialName("features") val features: List<PartnerFeaturePair> = emptyList()
)

@Serializable
data class PartnerStatusData(
    @SerialName("status") val status: Int
)

@Serializable
data class PartnerProfileImageData(
    @SerialName("img_profile") val imgProfile: String
)

@Serializable
data class FeatureRequestRequest(
    @SerialName("feature_id") val featureId: Int
)

/** Send only the field(s) you want to change -- leave the rest null and they're omitted server-side. */
@Serializable
data class UpdatePartnerProfileRequest(
    @SerialName("company_name") val companyName: String? = null,
    @SerialName("description") val description: String? = null,
    @SerialName("address") val address: String? = null,
    @SerialName("regencies_id") val regenciesId: Int? = null,
    @SerialName("latitude") val latitude: Double? = null,
    @SerialName("longitude") val longitude: Double? = null
)
