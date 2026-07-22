package com.nusatim.sapiriku.api.model

import kotlinx.serialization.InternalSerializationApi
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

/**
 * Every response from the backend REST API is wrapped in this shape:
 * {"status": bool, "message": string, "data": {...}|null, "meta": {...}|null}
 * `data`'s actual shape is endpoint-specific -- pass the matching data class as T.
 */
@Serializable
@OptIn(InternalSerializationApi::class)
data class ApiEnvelope<T>(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String,
    @SerialName("data") val data: T? = null,
    @SerialName("meta") val meta: ApiMeta? = null
)

/** Present only on list endpoints that paginate. */
@Serializable
@OptIn(InternalSerializationApi::class)
data class ApiMeta(
    @SerialName("page") val page: Int? = null,
    @SerialName("limit") val limit: Int? = null,
    @SerialName("total") val total: Int? = null,
    @SerialName("total_unfiltered") val totalUnfiltered: Int? = null
)
